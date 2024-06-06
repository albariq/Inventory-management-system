<?php

namespace App\Http\Controllers\Order;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Mail\StockAlert;
use App\Models\Customer;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Requests\Order\OrderStoreRequest;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'details.product'])->get();
        $sortField = 'invoice_no'; // Misalnya, nilai awal sort field
        $sortAsc = true; // Misalnya, nilai awal sort ascending

        return view('livewire.tables.order-table', compact('orders', 'sortField', 'sortAsc'));
    }

    public function create()
    {
        $products = Product::where('user_id', auth()->id())->with(['category', 'unit'])->get();

        $customers = Customer::where('user_id', auth()->id())->get(['id', 'name']);

        $carts = Cart::content();

        return view('orders.create', [
            'products' => $products,
            'customers' => $customers,
            'carts' => $carts,
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'payment_type' => $request->payment_type,
            'pay' => $request->pay,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'order_status' => OrderStatus::PENDING->value,
            'total_products' => Cart::count(),
            'sub_total' => Cart::subtotal(),
            'vat' => Cart::tax(),
            'total' => Cart::total(),
            'invoice_no' => IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'ORD-'
            ]),
            'user_id' => auth()->id(),
            'uuid' => (string) Str::uuid(),
            'due' => Cart::total() - $request->pay // Tambahkan nilai untuk field 'due'
        ]);
    
        $products = Cart::content();
    
        foreach ($products as $product) {
            OrderDetails::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $product->qty,
                'price' => $product->price,
                'unitcost' => $product->options->unitcost ?? 0, // Tambahkan nilai untuk field 'unitcost'
                'total' => $product->qty * $product->price // Tambahkan nilai untuk field 'total'
            ]);
        }
    
        $products = OrderDetails::where('order_id', $order->id)->get();
    
        $stockAlertProducts = [];
    
        foreach ($products as $product) {
            $productEntity = Product::where('id', $product->product_id)->first();
            $newQty = $productEntity->quantity - $product->quantity;
            if ($newQty < $productEntity->quantity_alert) {
                $stockAlertProducts[] = $productEntity;
            }
            $productEntity->update(['quantity' => $newQty]);
        }
    
        if (count($stockAlertProducts) > 0) {
            $listAdmin = [];
            foreach (User::all('email') as $admin) {
                $listAdmin [] = $admin->email;
            }
            Mail::to($listAdmin)->send(new StockAlert($stockAlertProducts));
        }
        $order->update([
            'order_status' => OrderStatus::COMPLETE,
            'due' => '0',
            'pay' => $order->total
        ]);
    
        return redirect()
            ->route('orders.complete')
            ->with('success', 'Order has been completed!');
    }
    
    
    

    public function destroy($uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $order->delete();
    }

    public function downloadInvoice($uuid)
    {
        $order = Order::with(['customer', 'details'])->where('uuid', $uuid)->firstOrFail();

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order)
    {
        $order->update([
            'order_status' => 2
        ]);
        $orders = Order::where('user_id',auth()->id())->count();

        return redirect()
            ->route('orders.index', [
                'orders' => $orders
            ])
            ->with('success', 'Order has been canceled!');
    }
}
