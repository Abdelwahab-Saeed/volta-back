<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ReadsListFilters;

    public function index(Request $request)
    {
        $filters = $this->listFilters($request, [
            'q' => 'search',
            'status' => array_column(OrderStatus::cases(), 'value'),
            'discount' => ['offer', 'coupon', 'none'],
            'from' => 'date',
            'to' => 'date',
        ]);

        $orders = Order::with(['user', 'offer'])
            // Order number (#12 or 12), customer name, either phone, or the account's name/email
            ->when($filters['q'] ?? null, function ($query, $term) {
                $number = ltrim($term, '#');
                $query->where(function ($query) use ($term, $number) {
                    if (ctype_digit($number)) {
                        $query->where('id', (int) $number);
                    }
                    foreach (['full_name', 'phone_number', 'phone_number_backup'] as $column) {
                        $query->orWhere($column, 'like', "%{$term}%");
                    }
                    $query->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['discount'] ?? null, fn ($query, $discount) => match ($discount) {
                // offer_id is cleared if the offer is deleted; the snapshot stays
                'offer' => $query->where(fn ($q) => $q->whereNotNull('offer_id')->orWhereNotNull('offer_snapshot')),
                'coupon' => $query->whereNotNull('coupon_code'),
                'none' => $query->whereNull('offer_id')->whereNull('offer_snapshot')->whereNull('coupon_code'),
            })
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', $this->dayBoundary($date)))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->where('created_at', '<=', $this->dayBoundary($date, end: true)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'offer']);
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

        // Back to the same page, so a filtered or paged list stays as it was
        return back()->with('success', 'تم تحديث حالة الطلب بنجاح.');
    }
}
