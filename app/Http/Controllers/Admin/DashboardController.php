<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /** Days shown in the daily orders chart (today included). */
    private const CHART_DAYS = 14;

    /** Products at or below this stock show in "running low". Same threshold the products list highlights. */
    private const LOW_STOCK = 5;

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
            'total_expenses' => Product::sum(\DB::raw('cost_price * stock')),
            'recent_orders' => Order::with('user')->latest()->take(6)->get(),
        ];

        // Read-only figures for the dashboard's charts and "needs attention" lists.
        $statusCounts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $insights = [
            'status_counts' => collect(OrderStatus::values())->mapWithKeys(fn ($status) => [$status => (int) ($statusCounts[$status] ?? 0)]),
            'daily_orders' => $this->dailyOrders(),
            'low_stock' => Product::where('stock', '<=', self::LOW_STOCK)->orderBy('stock')->take(6)->get(),
            'low_stock_count' => Product::where('stock', '<=', self::LOW_STOCK)->count(),
        ];

        return view('admin.dashboard', compact('stats', 'insights'));
    }

    /**
     * Orders per day (Cairo time) for the last CHART_DAYS days, cancelled orders left out.
     * Grouped in PHP so it works the same on MySQL and SQLite; the window is small.
     *
     * @return array<int, array{date: Carbon, count: int, total: int}> total in piasters
     */
    private function dailyOrders(): array
    {
        $tz = OfferController::ADMIN_TIMEZONE;
        $start = now($tz)->startOfDay()->subDays(self::CHART_DAYS - 1);

        $orders = Order::query()
            ->where('created_at', '>=', $start->copy()->utc())
            ->where('status', '!=', OrderStatus::CANCELLED->value)
            ->get(['created_at', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->created_at->timezone($tz)->toDateString());

        return collect(range(0, self::CHART_DAYS - 1))->map(function (int $offset) use ($start, $orders) {
            $date = $start->copy()->addDays($offset);
            $day = $orders->get($date->toDateString(), collect());

            return ['date' => $date, 'count' => $day->count(), 'total' => (int) $day->sum('total_amount')];
        })->all();
    }
}
