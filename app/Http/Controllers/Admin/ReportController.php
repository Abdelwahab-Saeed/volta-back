<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function soldProducts()
    {
        $soldProducts = OrderItem::whereHas('order', function ($query) {
                $query->where('status', 'delivered');
            })
            ->select('product_id', 
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->with(['product' => function ($query) {
                $query->withTrashed();
            }])
            ->groupBy('product_id')
            ->get();

        return view('admin.reports.sold_products', compact('soldProducts'));
    }
}
