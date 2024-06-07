<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::where("user_id", auth()->id())->count();
        $products = Product::where("user_id", auth()->id())->count();
        $purchases = Purchase::where("user_id", auth()->id())->count();
        $todayPurchases = Purchase::whereDate('date', today()->format('Y-m-d'))->count();
        $todayProducts = Product::whereDate('created_at', today()->format('Y-m-d'))->count();
        $todayQuotations = Quotation::whereDate('created_at', today()->format('Y-m-d'))->count();
        $todayOrders = Order::whereDate('created_at', today()->format('Y-m-d'))->count();
        $categories = Category::where("user_id", auth()->id())->count();
        $quotations = Quotation::where("user_id", auth()->id())->count();

        // Query untuk data chart
        $barChartData = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(DB::raw('categories.name as category, SUM(products.quantity) as total_stock'))
            ->groupBy('categories.name')
            ->get();

        $pieChartData = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(DB::raw('categories.name as category, COUNT(*) as product_count'))
            ->groupBy('categories.name')
            ->get();

        // Query untuk data stok per produk
        $stockPerProductData = DB::table('products')
            ->select('name as product_name', 'quantity as stock')
            ->get();

        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'todayProducts' => $todayProducts,
            'todayQuotations' => $todayQuotations,
            'todayOrders' => $todayOrders,
            'categories' => $categories,
            'quotations' => $quotations,
            'barChartData' => $barChartData,
            'pieChartData' => $pieChartData,
            'stockPerProductData' => $stockPerProductData // Menambahkan data stok per produk
        ]);
    }
}
