<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $notification = new Notification();

        $orderCode = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type;
        $transactionId = $notification->transaction_id;
        $grossAmount = $notification->gross_amount;

        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            $order->update(['payment_status' => 'paid', 'status' => 'processing']);
            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $transactionId,
                'payment_type' => $paymentType,
                'status' => 'success',
                'amount' => $grossAmount,
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
        }
        return response()->json(['message' => 'Payment processed']);
    }
}
