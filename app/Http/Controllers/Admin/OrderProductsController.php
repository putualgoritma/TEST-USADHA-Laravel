<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Order;
use App\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderProductsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('product_access'), 403);

        //$from = !empty($request->from) ? $request->from : date('2019-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');
        if ($request->ajax()) {

            $query = Order::selectRaw("product_order_details.orders_id,orders.register,products.name,product_order_details.type,product_order_details.quantity")
                ->join('product_order_details', 'product_order_details.orders_id', '=', 'orders.id')
                ->join('products', 'product_order_details.products_id', '=', 'products.id')
                ->where('product_order_details.owner', '85')
                ->where('products.type', 'single')
                ->FilterProductJoin()
                ->whereBetween('orders.register', [$from, $to])
                ->orderBy("orders.register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('debit', function ($row) {
                return $row->type == 'D' ? $row->quantity : 0;
            });

            $table->editColumn('credit', function ($row) {
                return $row->type == 'C' ? $row->quantity : 0;
            });

            $table->rawColumns(['placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $products = Product::where('type', 'single')
            ->get();
        $orderproduct = Order::selectRaw("orders.register,products.name,product_order_details.type,product_order_details.quantity")
            ->join('product_order_details', 'product_order_details.orders_id', '=', 'orders.id')
            ->join('products', 'product_order_details.products_id', '=', 'products.id')
            ->where('product_order_details.owner', '85')
            ->where('products.type', 'single')
            ->FilterProductJoin()
            ->whereBetween('orders.register', [$from, $to])
            ->orderBy("orders.register", "desc");

        return view('admin.order-products.index', compact('orderproduct', 'products'));
    }
    
}
