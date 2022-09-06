<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Ledger;
use App\Order;
use App\OrderDetails;
use App\Product;
use App\Traits\TraitModel;
use DB;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AgentsController extends Controller
{
    use TraitModel;

    public function stockRecap(Request $request)
    {
        abort_if(Gate::denies('agent_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        if ($request->ajax()) {

            $query = OrderDetails::selectRaw("products.*,product_order_details.owner as owner,(SUM(CASE WHEN product_order_details.type = 'D' THEN product_order_details.quantity ELSE 0 END) - SUM(CASE WHEN product_order_details.type = 'C' THEN product_order_details.quantity ELSE 0 END)) AS quantity_balance")
                ->join('orders', 'orders.id', '=', 'product_order_details.orders_id')
                ->join('products', 'products.id', '=', 'product_order_details.products_id')
                ->where('product_order_details.owner', $request->customer)
                ->whereBetween(DB::raw('DATE(orders.register)'), [$from, $to])
                ->where('orders.status', '=', 'approved')
                ->where('products.type', '=', 'single')
                ->FilterProductJoin()
                ->groupBy('products.id')
                ->orderBy("orders.register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'agent_show';
                return view('partials.datatablesAgentsStock', compact(
                    'viewGate',
                    'row'
                ));
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('description', function ($row) {
                return $row->description ? $row->description : "";
            });

            $table->editColumn('price', function ($row) {
                return $row->price ? $row->price : "";
            });

            $table->editColumn('quantity_balance', function ($row) {
                return $row->quantity_balance ? $row->quantity_balance : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orders = OrderDetails::selectRaw("products.*,(SUM(CASE WHEN product_order_details.type = 'D' THEN product_order_details.quantity ELSE 0 END) - SUM(CASE WHEN product_order_details.type = 'C' THEN product_order_details.quantity ELSE 0 END)) AS quantity_balance")
            ->join('orders', 'orders.id', '=', 'product_order_details.orders_id')
            ->join('products', 'products.id', '=', 'product_order_details.products_id')
            ->where('product_order_details.owner', $request->customer)
            ->whereBetween(DB::raw('DATE(orders.register)'), [$from, $to])
            ->where('orders.status', '=', 'approved')
            ->where('products.type', '=', 'single')
            ->FilterProductJoin()
            ->groupBy('products.id')
            ->orderBy("orders.register", "desc");

        $products = Product::select('*')
            ->where('type', 'single')
            ->orderBy("name", "asc")
            ->get();

        //return $orders;
        return view('admin.agents.stockrecap', compact('orders', 'products'));
    }

    public function stock(Request $request)
    {
        abort_if(Gate::denies('agent_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        if ($request->ajax()) {

            $query = OrderDetails::selectRaw("product_order_details.*")
                ->join('orders', 'orders.id', '=', 'product_order_details.orders_id')
                ->with('orders')
                ->with('products')
                ->where('product_order_details.owner', $request->customer)
                ->whereBetween(DB::raw('DATE(orders.register)'), [$from, $to])
                ->where('orders.status', '=', 'approved')
                ->FilterProduct()
                ->orderBy("orders.register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'order_show';
                $editGate = 'order_edit';
                $deleteGate = 'order_delete';
                $crudRoutePart = 'orders';

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('register', function ($row) {
                return $row->orders->register ? $row->orders->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->products->name ? $row->products->name : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->orders->memo ? $row->orders->memo : "";
            });

            $table->editColumn('debit', function ($row) {
                return $row->type == 'D' ? number_format($row->quantity, 2) : 0;
            });

            $table->editColumn('credit', function ($row) {
                return $row->type == 'C' ? number_format($row->quantity, 2) : 0;
            });

            $table->editColumn('total', function ($row) {
                return '';
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orders = OrderDetails::selectRaw("product_order_details.*")
            ->join('orders', 'orders.id', '=', 'product_order_details.orders_id')
            ->with('orders')
            ->with('products')
            ->where('product_order_details.owner', $request->customer)
            ->whereBetween(DB::raw('DATE(orders.register)'), [$from, $to])
            ->where('orders.status', '=', 'approved')
            ->FilterProduct()
            ->orderBy("orders.register", "desc");

        $products = Product::select('*')
            ->where('type', 'single')
            ->orderBy("name", "asc")
            ->get();

        //return $orders;
        return view('admin.agents.stock', compact('orders', 'products'));
    }

    public function saleDetails(Request $request)
    {
        abort_if(Gate::denies('agent_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        if ($request->ajax()) {

            $query = Order::with('products')
                ->with('customers')
                ->with('accounts')
                ->where('agents_id', $request->customer)
                ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
                ->where('status', '=', 'approved')
                ->orderBy("register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'order_show';
                $editGate = 'order_edit';
                $deleteGate = 'order_delete';
                $crudRoutePart = 'orders';

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                if (isset($row->customers->code)) {
                    return $row->customers->code ? $row->customers->code . " - " . $row->customers->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('status_delivery', function ($row) {
                return $row->status_delivery ? $row->status_delivery : "";
            });

            $table->editColumn('amount', function ($row) {
                return $row->total ? number_format($row->total, 2) : "";
            });

            $table->editColumn('accpay', function ($row) {
                if (isset($row->accounts->code)) {
                    return $row->accounts->name ? $row->accounts->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('product', function ($row) {
                $product_list = '<ul>';
                foreach ($row->products as $key => $item) {
                    $product_list .= '<li>' . $item->name . " (" . $item->pivot->quantity . " x " . number_format($item->price, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orders = Order::with('products')
            ->with('customers')
            ->with('accounts')
            ->where('agents_id', $request->customer)
            ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
            ->where('status', '=', 'approved')
            ->orderBy("register", "desc");

        $customers = Customer::select('*')
            ->where(function ($query) {
                $query->where('type', 'member')
                    ->orWhere('type', 'agent')
                    ->orWhere('def', '1');
            })
            ->orderBy("name", "asc")
            ->get();

        //return $orders;
        return view('admin.agents.saledetails', compact('orders', 'customers'));
    }

    public function buyDetails(Request $request)
    {
        abort_if(Gate::denies('agent_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');
        if ($request->ajax()) {

            $query = Order::with('products')
                ->with('customers')
                ->with('accounts')
                ->FilterCustomer()
                ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
                ->where(function ($query) {
                    $query->where('type', 'sale')
                        ->orWhere('type', 'activation_agent');
                })
                ->where('status', '=', 'approved')
                ->orderBy("register", "desc");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'order_show';
                $editGate = 'order_edit';
                $deleteGate = 'order_delete';
                $crudRoutePart = 'orders';

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('name', function ($row) {
                if (isset($row->customers->code)) {
                    return $row->customers->code ? $row->customers->code . " - " . $row->customers->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('status_delivery', function ($row) {
                return $row->status_delivery ? $row->status_delivery : "";
            });

            $table->editColumn('amount', function ($row) {
                return $row->total ? number_format($row->total, 2) : "";
            });

            $table->editColumn('accpay', function ($row) {
                if (isset($row->accounts->code)) {
                    return $row->accounts->name ? $row->accounts->name : "";
                } else {
                    return '';
                }
            });

            $table->editColumn('product', function ($row) {
                $product_list = '<ul>';
                foreach ($row->products as $key => $item) {
                    $product_list .= '<li>' . $item->name . " (" . $item->pivot->quantity . " x " . number_format($item->price, 2) . ")" . '</li>';
                }
                $product_list .= '</ul>';
                return $product_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'product']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orders = Order::with('products')
            ->with('customers')
            ->with('accounts')
            ->FilterCustomer()
            ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
            ->where(function ($query) {
                $query->where('type', 'sale')
                    ->orWhere('type', 'activation_agent');
            })
            ->where('status', '=', 'approved')
            ->orderBy("register", "desc");

        $customers = Customer::select('*')
            ->where(function ($query) {
                $query->where('type', 'member')
                    ->orWhere('type', 'agent')
                    ->orWhere('def', '1');
            })
            ->orderBy("name", "asc")
            ->get();

        //return $orders;
        return view('admin.agents.buydetails', compact('orders', 'customers'));
    }

    public function saleRecap(Request $request)
    {
        abort_unless(\Gate::allows('agent_access'), 403);

        if ($request->ajax()) {
            $query = Customer::selectRaw("customers.*,SUM(orders.total) AS amount_balance")
                ->leftJoin('orders', 'orders.agents_id', '=', 'customers.id')
                ->where('customers.type', '=', 'agent')
                ->where('customers.def', '=', '0')
                ->where('orders.status', '=', 'approved')
                ->groupBy('customers.id')
                ->orderBy("customers.register", "DESC")
                ->FilterInput()
                ->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'agent_show';
                $editGate = 'agent_edit';
                $deleteGate = 'agent_delete';
                $crudRoutePart = 'agents';

                return view('partials.datatablesSagents', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('address', function ($row) {
                return $row->address ? $row->address : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('saldo', function ($row) {
                return $row->amount_balance ? number_format($row->amount_balance, 2) : 0;
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        $agents = Customer::selectRaw("customers.*,SUM(orders.total) AS amount_balance")
            ->leftJoin('orders', 'orders.agents_id', '=', 'customers.id')
            ->where('customers.type', '=', 'agent')
            ->where('customers.def', '=', '0')
            ->where('orders.status', '=', 'approved')
            ->groupBy('customers.id')
            ->orderBy("customers.register", "DESC")
            ->FilterInput()
            ->get();

        return view('admin.agents.sale', compact('agents'));
    }

    public function buyRecap(Request $request)
    {
        abort_unless(\Gate::allows('agent_access'), 403);

        if ($request->ajax()) {
            $query = Customer::selectRaw("customers.*,SUM(orders.total) AS amount_balance")
                ->leftJoin('orders', 'orders.customers_id', '=', 'customers.id')
                ->where('customers.type', '=', 'agent')
                ->where('customers.def', '=', '0')
                ->where(function ($query) {
                    $query->where('orders.type', 'sale')
                        ->orWhere('orders.type', 'activation_agent');
                })
                ->where('orders.status', '=', 'approved')
                ->groupBy('customers.id')
                ->orderBy("customers.register", "DESC")
                ->FilterInput()
                ->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'agent_show';
                $editGate = 'agent_edit';
                $deleteGate = 'agent_delete';
                $crudRoutePart = 'agents';

                return view('partials.datatablesBagents', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('address', function ($row) {
                return $row->address ? $row->address : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('saldo', function ($row) {
                return $row->amount_balance ? number_format($row->amount_balance, 2) : 0;
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        $agents = Customer::selectRaw("customers.*,SUM(orders.total) AS amount_balance")
            ->leftJoin('orders', 'orders.customers_id', '=', 'customers.id')
            ->where('customers.type', '=', 'agent')
            ->where('customers.def', '=', '0')
            ->where(function ($query) {
                $query->where('orders.type', 'sale')
                    ->orWhere('orders.type', 'activation_agent');
            })
            ->where('orders.status', '=', 'approved')
            ->groupBy('customers.id')
            ->orderBy("customers.register", "DESC")
            ->FilterInput()
            ->get();

        return view('admin.agents.buy', compact('agents'));
    }

    public function unblock($id)
    {
        abort_if(\Gate::denies('agent_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $agent = Customer::find($id);

        return view('admin.agents.unblock', compact('agent'));
    }

    public function unblockProcess(Request $request)
    {
        abort_unless(\Gate::allows('agent_show'), 403);
        if ($request->has('status_block')) {
            //get
            $agent = Customer::find($request->input('id'));
            //update
            $agent->status_block = '0';
            $agent->save();
        }
        return redirect()->route('admin.agents.index');

    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('agent_access'), 403);

        if ($request->ajax()) {
            $query = Customer::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
                ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
                ->where('customers.type', '=', 'agent')
                ->where('customers.def', '=', '0')
                ->groupBy('customers.id')
                ->orderBy("customers.register", "DESC")
                ->FilterInput()
                ->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'agent_show';
                $editGate = 'agent_edit';
                $deleteGate = 'agent_delete';
                $crudRoutePart = 'agents';

                return view('partials.datatablesAgents', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('address', function ($row) {
                return $row->address ? $row->address : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('saldo', function ($row) {
                return $row->amount_balance ? number_format($row->amount_balance, 2) : 0;
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        // $agents = Customer::where('type', '!=', 'member')
        // ->where('def', '=', '0')
        // ->get();

        $agents = Customer::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
            ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->where('customers.type', '=', 'agent')
            ->where('customers.def', '=', '0')
            ->groupBy('customers.id')
            ->get();

        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('agent_create'), 403);

        $last_code = $this->cst_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.agents.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(\Gate::allows('agent_create'), 403);

        $password_def = bcrypt('2579');
        $data = array_merge($request->all(), ['status' => 'pending', 'password' => $password_def]);
        $agent = Customer::create($data);

        return redirect()->route('admin.agents.index');
    }

    public function edit(Customer $agent)
    {
        abort_unless(\Gate::allows('agent_edit'), 403);

        return view('admin.agents.edit', compact('agent'));
    }

    public function update(UpdateCustomerRequest $request, Customer $agent)
    {
        abort_unless(\Gate::allows('agent_edit'), 403);

        $agent->update($request->all());

        return redirect()->route('admin.agents.index');
    }

    public function show(Customer $agent)
    {
        abort_unless(\Gate::allows('agent_show'), 403);

        return view('admin.agents.show', compact('agent'));
    }

    public function destroy(Customer $agent)
    {
        abort_unless(\Gate::allows('agent_delete'), 403);

        //check if pending
        if ($agent->status == 'pending') {
            $orders = Order::where('customers_id', $agent->id)
                ->orWhere('customers_activation_id', $agent->id)
                ->get();
            foreach ($orders as $key => $order) {
                if ($order->ledgers_id > 0) {
                    $ledger = Ledger::find($order->ledgers_id);
                    $ledger->accounts()->detach();
                    $ledger->delete();
                }
                $order->products()->detach();
                $order->productdetails()->detach();
                $order->points()->detach();
                $order->delete();
            }
            // $agent->delete();
            $agent->status = 'closed';
            $agent->save();
        } else {
            return back()->withError('Gagal Delete, Member Active!');
        }

        return back();
    }

    public function massDestroy(MassDestroyCustomerRequest $request)
    {
        // Customer::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return back()->withError('Gagal Delete, Member Active!');
    }
}
