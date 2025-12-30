<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'success' => false,
            ], 422);
        }
        $user = auth('api')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $uniquecode = 'ORD-' . strtoupper(uniqid('', true));

        $total = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (! $product) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Product Not Found',
                ]);
            }
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock Not Enough',
                ], 400);
            }
            $total += $product->price * $item['quantity'];
        }
        $order = Order::create([
            'order_code' => $uniquecode,
            'user_id' => $user->id,
            'total_price' => $total,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $orderItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $orderItems[] = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
            ]);
            $product->stock -= $item['quantity'];
            $product->save();
        }

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $user->username,
                'email' => $user->email,
            ],
        ];
        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'success' => true,
            'message' => 'Transaction Success',
            'data' => [
                'order' => $order,
                'items' => $orderItems,
                'snap_token' => $snapToken,
            ],
        ], 200);
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'user')->find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'message' => 'Order details',
            'success' => true,
            'data' => $order,
        ], 200);
    }

    public function destroy($id)
    {
        $order = Order::with('items.product')->find($id);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false,
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Cannot delete a paid order',
                'success' => false,
            ], 400);
        }

        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $order->items()->delete();
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
            'success' => true,
        ], 200);
    }

    public function index()
    {
        $orderDetail = Order::with('items.product', 'user')->get();
        if ($orderDetail->isEmpty()) {
            return response()->json([
                'message' => 'data Not Found',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'data' => $orderDetail,
            'message' => 'Get All Resource',
            'success' => true,
        ]);
    }
}
