<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class CapitalistsController extends Controller
{
    use TraitModel;

    public function unblock($id)
    {
        abort_if(\Gate::denies('capitalist_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $capitalist = Customer::find($id);

        return view('admin.capitalists.unblock', compact('capitalist'));
    }

    public function unblockProcess(Request $request)
    {
        abort_unless(\Gate::allows('capitalist_show'), 403);
        if ($request->has('status_block')) {
            //get
            $capitalist = Customer::find($request->input('id'));
            //update
            $capitalist->status_block = '0';
            $capitalist->save();
        }
        return redirect()->route('admin.capitalists.index');

    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('capitalist_access'), 403);

        if ($request->ajax()) {
            $query = Customer::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
                ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
                ->where('customers.type', '=', 'capitalist')
                ->where('customers.def', '=', '0')
                ->groupBy('customers.id')
                ->orderBy("customers.code", "DESC")
                ->FilterInput()
                ->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'capitalist_show';
                $editGate = 'capitalist_edit';
                $deleteGate = 'capitalist_delete';
                $crudRoutePart = 'capitalists';

                return view('partials.datatablesActions', compact(
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
                return number_format($row->amount_balance, 2) ? $row->amount_balance : 0;
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        // $capitalists = Customer::where('type', '!=', 'member')
        // ->where('def', '=', '0')
        // ->get();

        $capitalists = Customer::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
            ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->where('customers.type', '=', 'customer')
            ->where('customers.def', '=', '0')
            ->groupBy('customers.id')            
            ->get();

        return view('admin.capitalists.index', compact('capitalists'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('capitalist_create'), 403);

        $last_code = $this->get_last_code('capitalist');
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.capitalists.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        abort_unless(\Gate::allows('capitalist_create'), 403);

        $password_def = bcrypt('2579');
        $data = array_merge($request->all(), ['status' => 'active', 'password' => $password_def]);
        $capitalist = Customer::create($data);

        return redirect()->route('admin.capitalists.index');
    }

    public function edit(Customer $capitalist)
    {
        abort_unless(\Gate::allows('capitalist_edit'), 403);

        return view('admin.capitalists.edit', compact('capitalist'));
    }

    public function update(UpdateCustomerRequest $request, Customer $capitalist)
    {
        abort_unless(\Gate::allows('capitalist_edit'), 403);

        $capitalist->update($request->all());

        return redirect()->route('admin.capitalists.index');
    }

    public function show(Customer $capitalist)
    {
        abort_unless(\Gate::allows('capitalist_show'), 403);

        return view('admin.capitalists.show', compact('capitalist'));
    }

    public function destroy(Customer $capitalist)
    {
        abort_unless(\Gate::allows('capitalist_delete'), 403);

        //check if pending
        if ($capitalist->status == 'pending') {
            $capitalist->delete();
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
