<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Payreceivable;
use App\PayreceivableTrs;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyPayreceivableTrsRequest;
use App\Http\Requests\StorePayreceivableTrsRequest;
use App\Ledger;
use App\Traits\TraitModel;

class ReceivableTrsController extends Controller
{
    use TraitModel;

    public function indexTrs($id)
    {
        abort_unless(\Gate::allows('receivable_access'), 403);

        $payreceivable = Payreceivable::find($id);
        $customer = Customer::find($payreceivable->customer_id);

        $receivabletrs = PayreceivableTrs::where('payreceivable_id', $id)
        ->get();
        //return $receivabletrs;

        return view('admin.receivabletrs.index', compact('receivabletrs','customer','id'));
    }

    public function createTrs($id)
    {
        $last_code = $this->get_last_code('receivable_trsc');
        $code = acc_code_generate($last_code, 8, 3);
        $accounts = Account::select('*')
            ->where('accounts_group_id', 2)
            ->get();
        $accounts_pay = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        $customers = Customer::select('*')
            ->where('def', '=', '0')
            ->where(function ($query) {
                $query->where('type', 'general')
                    ->orWhere('type', 'capitalist');
            })
            ->get();
        return view('admin.receivabletrs.create', compact('code', 'accounts', 'customers', 'accounts_pay','id'));
    }

    public function storeTrs(StorePayreceivableTrsRequest $request)
    {
        abort_unless(\Gate::allows('receivable_create'), 403);

        //get hidden
        $payreceivable = Payreceivable::find($request->input('payreceivable_id'));

        $memo = "Registrasi Pembayaran Piutang";
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        $accounts = array($payreceivable->account_id, $request->input('account_id'));
        $amounts = array($request->input('amount'), $request->input('amount'));
        $types = array('C', 'D');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        $data = array_merge($request->all(), ['ledger_id' => $ledger_id, 'type' => 'C', 'status' => 'approved', 'code' => $request->input('code'), 'label' => $memo, 'memo' => $request->input('memo'), 'register' => $request->input('register'), 'amount' => $request->input('amount'), 'account_id' => $request->input('account_id')]);
        $payreceivable = PayreceivableTrs::create($data);

        return redirect()->route('admin.receivables.indexTrs',$request->input('payreceivable_id'));
    }

    public function editTrs($id)
    {
        abort_unless(\Gate::allows('receivable_edit'), 403);

        // $payreceivable->load('accounts');

        // return view('admin.receivabletrs.edit', compact('payreceivable'));
        $payreceivableTrs = PayreceivableTrs::find($id);
        return redirect()->route('admin.receivables.indexTrs',$payreceivableTrs->payreceivable_id);
    }

    public function updateTrs(StorePayreceivableTrsRequest $request, PayreceivableTrs $payreceivable)
    {
        abort_unless(\Gate::allows('receivable_edit'), 403);

        // $data = $request->all();

        // $payreceivable->update($data);

        return redirect()->route('admin.receivables.indexTrs');

    }

    public function showTrs($id)
    {
        abort_unless(\Gate::allows('receivable_show'), 403);        

        // return view('admin.receivabletrs.show', compact('payreceivable'));

        $payreceivableTrs = PayreceivableTrs::find($id);
        return redirect()->route('admin.receivables.indexTrs',$payreceivableTrs->payreceivable_id);
    }

    public function destroyTrs($id)
    {
        abort_unless(\Gate::allows('receivable_delete'), 403);

        $payreceivableTrs = PayreceivableTrs::find($id);
        if ($payreceivableTrs->ledger_id > 0 && $payreceivableTrs->type=='C') {
            $ledger = Ledger::find($payreceivableTrs->ledger_id);            
            if($payreceivableTrs->delete()){
            $ledger->accounts()->detach();
            $ledger->delete();
            $payreceivableTrs->delete();
            }
        }

        return back();
    }

    public function massDestroyTrs(MassDestroyPayreceivableTrsRequest $request)
    {
        //PayreceivableTrs::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
