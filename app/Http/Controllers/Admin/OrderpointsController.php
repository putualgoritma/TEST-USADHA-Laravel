<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Point;
use App\Http\Controllers\Controller;
use App\OrderPoint;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderpointsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('orderpoint_access'), 403);
        //$from = !empty($request->from) ? $request->from : date('Y-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        // dd('halo', $from .' '. $to);
        if ($request->ajax()) {
            $query = OrderPoint::selectRaw('order_points.*')->join('orders', 'order_points.orders_id', '=', 'orders.id')
                ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
            // ->with('orders')
                ->with('customers')
                ->where('order_points.status', 'onhand')
                ->FilterInputJoin()
                ->FilterPointJoin()
                ->orderBy('order_points.id', 'ASC');

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');

            $table->editColumn('register', function ($row) {
                return $row->orders->register ? $row->orders->register : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->customers->name ? $row->customers->code . " - " . $row->customers->name : "";
            });

            $table->editColumn('debit', function ($row) {
                if ($row->type === 'D') {
                    return $row->amount ? number_format($row->amount, 2) : "";
                }
            });

            $table->editColumn('credit', function ($row) {
                if ($row->type === 'C') {
                    return $row->amount ? number_format($row->amount, 2) : "";
                }
            });

            $table->editColumn('balance', function ($row) {
                return;
            });

            $table->rawColumns(['placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        // //def view
        $orderpoints = OrderPoint::selectRaw('order_points.*')->join('orders', 'order_points.orders_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
        // ->with('orders')
            ->with('customers')
            ->where('order_points.status', 'onhand')
            ->FilterInputJoin()
            ->FilterPointJoin()
            ->orderBy('order_points.id', 'ASC');

        $customers = Customer::select('*')
            ->where(function ($query) {
                $query->where('type', 'member')
                    ->orWhere('type', 'agent')
                    ->orWhere('def', '1');
            })
            ->orderBy('code', 'ASC')
            ->get();
        $points = Point::get();

        return view('admin.orderpoints.index', compact('orderpoints', 'customers','points'));
    }
}
