<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notification = new Notification();

            $orderCode = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $transactionId = $notification->transaction_id;
            $grossAmount = $notification->gross_amount;
            $fraudStatus = $notification->fraud_status ?? null;

            Log::info('Midtrans Callback Received', [
                'order_code' => $orderCode,
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'transaction_id' => $transactionId,
                'fraud_status' => $fraudStatus,
            ]);

            $order = Order::where('order_code', $orderCode)->first();
            if (!$order) {
                Log::error('Order not found for callback', ['order_code' => $orderCode]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment_status === 'paid') {
                Log::info('Order already paid, skipping', ['order_code' => $orderCode]);
                return response()->json(['message' => 'Order already paid']);
            }

            DB::beginTransaction();

            try {
                $paymentStatus = $this->determinePaymentStatus($transactionStatus, $fraudStatus);

                if ($paymentStatus === 'success') {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing'
                    ]);

                    Payment::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'transaction_id' => $transactionId,
                            'payment_type' => $paymentType,
                            'payment_gateway' => 'midtrans',
                            'status' => 'success',
                            'amount' => $grossAmount,
                        ]
                    );

                    Log::info('Payment success, order updated to paid', ['order_code' => $orderCode]);

                } elseif ($paymentStatus === 'pending') {
                    $order->update(['payment_status' => 'pending']);

                    Payment::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'transaction_id' => $transactionId,
                            'payment_type' => $paymentType,
                            'payment_gateway' => 'midtrans',
                            'status' => 'pending',
                            'amount' => $grossAmount,
                        ]
                    );

                    Log::info('Payment pending', ['order_code' => $orderCode]);

                } elseif ($paymentStatus === 'failed') {
                    $order->update([
                        'payment_status' => 'failed',
                        'status' => 'cancelled'
                    ]);

                    Payment::updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'transaction_id' => $transactionId,
                            'payment_type' => $paymentType,
                            'payment_gateway' => 'midtrans',
                            'status' => 'failed',
                            'amount' => $grossAmount,
                        ]
                    );

                    foreach ($order->items as $item) {
                        $item->product->increment('stock', $item->quantity);
                    }

                    Log::info('Payment failed, stock restored', ['order_code' => $orderCode]);
                }

                DB::commit();

                return response()->json(['message' => 'Payment processed successfully']);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing payment callback', [
                    'order_code' => $orderCode,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Midtrans callback error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error processing callback'], 500);
        }
    }

    /**
     * Check payment status dari Midtrans dan update order
     * Gunakan ini jika callback tidak berjalan (misal: localhost development)
     */
    public function checkStatus($orderCode)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Jika sudah paid, tidak perlu cek lagi
        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Order already paid',
                'data' => $order
            ]);
        }

        try {
            // Ambil status transaksi dari Midtrans
            $status = Transaction::status($orderCode);

            $transactionStatus = $status->transaction_status;
            $fraudStatus = $status->fraud_status ?? null;
            $paymentType = $status->payment_type ?? null;
            $transactionId = $status->transaction_id ?? null;
            $grossAmount = $status->gross_amount ?? $order->total_price;

            Log::info('Midtrans Status Check', [
                'order_code' => $orderCode,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]);

            DB::beginTransaction();

            $paymentStatus = $this->determinePaymentStatus($transactionStatus, $fraudStatus);

            if ($paymentStatus === 'success') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing'
                ]);

                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'transaction_id' => $transactionId,
                        'payment_type' => $paymentType,
                        'payment_gateway' => 'midtrans',
                        'status' => 'success',
                        'amount' => $grossAmount,
                    ]
                );
            } elseif ($paymentStatus === 'failed') {
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);

                // Kembalikan stok
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment status updated',
                'transaction_status' => $transactionStatus,
                'payment_status' => $paymentStatus,
                'data' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking payment status', [
                'order_code' => $orderCode,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error checking payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function determinePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                return 'success';
            } elseif ($fraudStatus === 'challenge') {
                return 'pending';
            }
            return 'failed';
        }

        if ($transactionStatus === 'settlement') {
            return 'success';
        }

        if (in_array($transactionStatus, ['pending'])) {
            return 'pending';
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
            return 'failed';
        }

        return 'pending';
    }
}
