<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends \App\Http\Controllers\Controller
{
    public function getSalesData()
    {
        try {
            $salesData = DB::table('orders')
                ->select(DB::raw('DATE(order_date) as sale_date'), DB::raw('SUM(total) as total_sales'))
                ->where('order_status', 1)
                ->groupBy('sale_date')
                ->orderBy('sale_date')
                ->get();

            return response()->json($salesData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
