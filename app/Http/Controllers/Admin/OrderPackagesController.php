<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderPackagesController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('product_access'), 403);

        //$from = !empty($request->from) ? $request->from : date('2019-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');
        if ($request->ajax()) {

            $query = Order::selectRaw("orders.register,products.name,orders.type,order_product.quantity")
                ->join('order_product', 'order_product.order_id', '=', 'orders.id')
                ->join('products', 'order_product.product_id', '=', 'products.id')
                ->where(function ($qry) {
                    $qry->where('orders.type', '=', 'sale')
                        ->orWhere('orders.type', '=', 'buy')
                        ->orWhere('orders.type', '=', 'sale_retur')
                        ->orWhere('orders.type', '=', 'buy_retur')
                        ->orWhere('orders.type', '=', 'activation_agent');
                })
                ->where('products.type', 'package')
                ->FilterPackageJoin()
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
                return ($row->type == 'buy' || $row->type == 'sale_retur') ? $row->quantity : 0;
            });

            $table->editColumn('credit', function ($row) {
                return ($row->type == 'sale' || $row->type == 'buy_retur' || $row->type == 'activation_agent') ? $row->quantity : 0;
            });

            $table->rawColumns(['placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $products = Product::where('type', 'package')
            ->get();
        $orderproduct = Order::selectRaw("orders.register,products.name,orders.type,order_product.quantity")
            ->join('order_product', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->where(function ($qry) {
                $qry->where('orders.type', '=', 'sale')
                    ->orWhere('orders.type', '=', 'buy')
                    ->orWhere('orders.type', '=', 'sale_retur')
                    ->orWhere('orders.type', '=', 'buy_retur')
                    ->orWhere('orders.type', '=', 'activation_agent');
            })
            ->where('products.type', 'package')
            ->FilterPackageJoin()
            ->whereBetween('orders.register', [$from, $to])
            ->orderBy("orders.register", "desc");

        return view('admin.order-packages.index', compact('orderproduct', 'products'));
    }

}
