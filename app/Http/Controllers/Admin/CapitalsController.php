<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Capital;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCapitalRequest;
use App\Http\Requests\StoreCapitalRequest;
use App\Ledger;
use App\Traits\TraitModel;

class CapitalsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('capital_access'), 403);

        $capitals = Capital::with('customers')
            ->get();
        //return $capitals;

        return view('admin.capitals.index', compact('capitals'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('capital');
        $code = acc_code_generate($last_code, 8, 3);
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        $accounts_credit = Account::select('*')
            ->where('accounts_group_id', 13)
            ->get();
        $customers = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'capitalist')
            ->get();
        return view('admin.capitals.create', compact('code', 'accounts', 'customers', 'accounts_credit'));
    }

    public function store(StoreCapitalRequest $request)
    {
        abort_unless(\Gate::allows('capital_create'), 403);

        $memo = "Registrasi Modal";
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        $accounts = array($request->input('account_credit'), $request->input('account_debit'));
        $amounts = array($request->input('amount'), $request->input('amount'));
        $types = array('C', 'D');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        $data = array_merge($request->all(), ['ledger_id' => $ledger_id]);
        $capital = Capital::create($data);

        return redirect()->route('admin.capitals.index');
    }

    public function edit(Capital $capital)
    {
        abort_unless(\Gate::allows('capital_edit'), 403);

        // $capital->load('accounts');

        // return view('admin.capitals.edit', compact('capital'));
        return redirect()->route('admin.capitals.index');
    }

    public function update(StoreCapitalRequest $request, Capital $capital)
    {
        abort_unless(\Gate::allows('capital_edit'), 403);

        // $data = $request->all();

        // $capital->update($data);

        return redirect()->route('admin.capitals.index');

    }

    public function show(Capital $capital)
    {
        abort_unless(\Gate::allows('capital_show'), 403);

        // return view('admin.capitals.show', compact('capital'));
        return redirect()->route('admin.capitals.index');
    }

    public function destroy(Capital $capital)
    {
        abort_unless(\Gate::allows('capital_delete'), 403);

        if ($capital->ledger_id > 0) {
            $ledger = Ledger::find($capital->ledger_id);            
            if($capital->delete()){
            $ledger->accounts()->detach();
            $ledger->delete();
            }
        }else{
        $capital->delete();
        }

        return back();
    }

    public function massDestroy(MassDestroyCapitalRequest $request)
    {
        //Capital::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
