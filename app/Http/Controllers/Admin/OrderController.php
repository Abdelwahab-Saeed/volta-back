<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Restore stock if cancelled (logic from OrderController API)
        if ($request->status === OrderStatus::CANCELLED->value && $oldStatus !== OrderStatus::CANCELLED->value) {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return redirect()->route('admin.orders.index')->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }
}
