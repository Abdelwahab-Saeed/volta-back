<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Get all orders for the authenticated user.
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user owns the order
        if ($order->user_id !== Auth::id()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items.product'));
    }

    /**
     * Update the order status.
     * Note: In a real app, this should be protected by Admin middleware.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', Rule::in(OrderStatus::values())],
        ]);

        // Optional: specific logic for users cancelling their own orders
        // if (Auth::id() === $order->user_id) {
        //     if ($request->status !== OrderStatus::CANCELLED->value) {
        //         return response()->json(['message' => 'Users can only cancel orders'], 403);
        //     }
        //     if ($order->status !== OrderStatus::PENDING->value) {
        //          return response()->json(['message' => 'Cannot cancel processed order'], 422);
        //     }
        // }

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Restore stock if cancelled
        if ($request->status === OrderStatus::CANCELLED->value && $oldStatus !== OrderStatus::CANCELLED->value) {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }

    /**
     * Get all orders (Admin).
     */
    public function all()
    {
        $orders = Order::with('items.product', 'user')->latest()->get();
        return response()->json($orders);
    }

    /**
     * Cancel the user's order.
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== OrderStatus::PENDING->value) {
            return response()->json(['message' => 'Cannot cancel order that is not pending'], 422);
        }

        $order->status = OrderStatus::CANCELLED->value;
        $order->save();

        // Restore Stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        return response()->json(['message' => 'Order cancelled successfully', 'order' => $order]);
    }
}
