<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Create a new order
     */
    public function createOrder(Request $request)
    {

        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Get authenticaded user
        $user = Auth::user();

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => 0, // Será calculado a continuación
            'status' => 'pending',
        ]);

        $totalAmount = 0;

        // Process products from the cart
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $totalAmount += $subtotal;

            // Create order details
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }

        // Update total amount from order
        $order->update(['total_amount' => $totalAmount]);

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load('orderItems'),
        ], 201);
    }

    /**
     * Show order details
     */
    public function getOrder($id)
    {
        $order = Order::with('orderItems.product')->findOrFail($id);

        if(!isset($order)){
            return response()->json([
                'message' => 'Order not found',
            ]);
        }

        // Verify if the user has access to order
        if (Auth::id() !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }
}
