<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMartregisterRequest;
use App\Http\Requests\StoreMartregisterRequest;
use App\Http\Requests\UpdateMartregisterRequest;
use App\Ledger;
use App\Martregister;
use App\Point;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MartregistersController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        //get env
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function index()
    {
        abort_if(Gate::denies('martregister_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $martregisters = Martregister::with('customers')->orderBy("id", "DESC")
            ->get();
        return view('admin.martregisters.index', compact('martregisters'));
    }

    public function create()
    {
        abort_if(Gate::denies('martregister_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();
        $customers = Customer::select('*')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        $last_code = $this->top_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.martregisters.create', compact('points', 'customers', 'accounts', 'code'));
    }

    public function store(StoreMartregisterRequest $request)
    {
        //get total
        $total = 0;
        $points = $request->input('points', []);
        $amounts = $request->input('amounts', []);
        for ($point = 0; $point < count($points); $point++) {
            $total += $amounts[$point];
        }
        //proceed ledger martregister
        $last_code = $this->get_last_code('martregister');
        $code = acc_code_generate($last_code, 8, 3);
        $member = Customer::find($request->input('customers_id'));
        $memo = "Martregister Poin " . $member->code . "-" . $member->name;
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //set ledger entry arr
        $acc_points = '67';
        $accounts = array($acc_points, $request->input('accounts_id'));
        $amounts_acc = array($total, $total);
        $types = array('C', 'D');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts_acc[$account]]);
            }
        }

        //set def
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        //set martregister
        $data = array_merge($request->all(), ['total' => $total, 'type' => 'martregister', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash', 'memo' => $memo, 'code' => $code]);
        $martregister = Martregister::create($data);
        //set martregister points
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $martregister->points()->attach($points[$point], ['amount' => $amounts[$point], 'type' => 'D', 'status' => 'onhand', 'customers_id' => $customers_id, 'memo' => $memo]);
            }
        }

        return redirect()->route('admin.martregisters.index');
    }

    public function edit(Martregister $martregister)
    {
        abort_if(Gate::denies('martregister_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();

        $martregister->load('points');

        return view('admin.martregisters.edit', compact('points', 'martregister'));
    }

    public function update(UpdateMartregisterRequest $request, Martregister $martregister)
    {
        $martregister->update($request->all());

        $martregister->points()->detach();
        $points = $request->input('points', []);
        $quantities = $request->input('quantities', []);
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $martregister->points()->attach($points[$point], ['quantity' => $quantities[$point]]);
            }
        }

        return redirect()->route('admin.martregisters.index');
    }

    public function show(Martregister $martregister)
    {
        abort_if(Gate::denies('martregister_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $martregister->load('customers');
        $referal = Customer::find($martregister->referal_id);

        return view('admin.martregisters.show', compact('martregister', 'referal'));
    }

    public function destroy(Martregister $martregister)
    {
        abort_if(Gate::denies('martregister_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $martregister->delete();

        return back();
    }

    public function massDestroy(MassDestroyMartregisterRequest $request)
    {
        Martregister::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function approved($id)
    {
        abort_if(Gate::denies('martregister_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $martregister = Martregister::find($id);

        return view('admin.martregisters.approved', compact('martregister'));
    }

    public function approvedprocess(Request $request)
    {
        abort_unless(\Gate::allows('martregister_show'), 403);
        if ($request->has('status')) {
            $martregister = Martregister::find($request->id);
            if ($martregister->type == 'join') {
                $customer = Customer::find($martregister->customer_id);
                $customer->agent_type = 'supermart';
                $customer->save();
            }
            if ($martregister->type == 'registerdownline') {
                $customer = Customer::find($martregister->customer_id);
                $last_code = $this->get_last_code('agent');
                $code = acc_code_generate($last_code, 8, 3);
                $password = bcrypt($martregister->password);
                $data['code'] = $code;
                $data['password'] = $password;
                $data['customer_agent_id'] = $martregister->customer_id;
                $data['ref_id'] = $martregister->referal_id;
                $data['ref_bin_id'] = $martregister->referal_id;
                $data['parent_id'] = $martregister->referal_id;
                $data['agent_type'] = 'minimart';
                $data['email'] = $martregister->email;
                $data['phone'] = $martregister->phone;
                $data['address'] = $martregister->address;
                $data['name'] = $martregister->name;
                $data['type'] = 'agent';
                $data['status'] = 'active';
                $data['register'] = date("Y-m-d");
                $agent = Customer::create($data);
            }
            if ($martregister->type == 'upgrade') {
                $customer = Customer::find($martregister->customer_id);
                if ($customer->agent_type == 'minimart') {
                    $customer->agent_type = 'mart';
                } else {
                    $customer->agent_type = 'supermart';
                }
                $customer->save();
            }
            $martregister->status = 'approved';
            $martregister->save();
        }
        return redirect()->route('admin.martregisters.index');

    }

    public function cancelled($id)
    {
        abort_if(Gate::denies('martregister_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $martregister = Martregister::find($id);

        return view('admin.martregisters.cancelled', compact('martregister'));
    }

    public function cancelledProcess(Request $request)
    {
        abort_unless(\Gate::allows('martregister_show'), 403);
        if ($request->has('status')) {
            //get
            $martregister = Martregister::find($request->input('id'));

            //update
            $martregister->status = 'closed';
            $martregister->save();
        }
        return redirect()->route('admin.martregisters.index');

    }
}
