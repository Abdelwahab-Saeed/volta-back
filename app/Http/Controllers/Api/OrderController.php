<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Traits\ApiResponse;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Get all orders for the authenticated user.
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return $this->successResponse($orders, 'تم جلب طلباتك بنجاح');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user owns the order
        if ($order->user_id !== Auth::id()) {
             return $this->errorResponse('غير مصرح لك بالوصول لهذا الطلب', 403);
        }

        return $this->successResponse($order->load('items.product'), 'تم جلب بيانات الطلب بنجاح');
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

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Restore stock if cancelled
        if ($request->status === OrderStatus::CANCELLED->value && $oldStatus !== OrderStatus::CANCELLED->value) {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return $this->successResponse($order, 'تم تحديث حالة الطلب بنجاح');
    }

    /**
     * Get all orders (Admin).
     */
    public function all()
    {
        $orders = Order::with('items.product', 'user')->latest()->get();
        return $this->successResponse($orders, 'تم جلب جميع الطلبات بنجاح');
    }

    /**
     * Cancel the user's order.
     */
    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return $this->errorResponse('غير مصرح لك بإلغاء هذا الطلب', 403);
        }

        if ($order->status !== OrderStatus::PENDING->value) {
            return $this->errorResponse('لا يمكن إلغاء طلب غير معلق', 422);
        }

        $order->status = OrderStatus::CANCELLED->value;
        $order->save();

        // Restore Stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        return $this->successResponse($order, 'تم إلغاء الطلب بنجاح');
    }
}
