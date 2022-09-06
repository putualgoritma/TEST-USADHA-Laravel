<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Accountlock;
use App\AccountsGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAccountRequest;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TraitModel;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('account_access'), 403);

        if ($request->ajax()) {
            $query = Account::query()
                ->orderBy("code", "asc")    
                ->filterDates()
                ->select(sprintf('%s.*', 'accounts'));
            $table = Datatables::of($query);           

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'account_show';
                $editGate      = 'account_edit';
                $deleteGate    = 'account_delete';
                $crudRoutePart = 'accounts';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            return $table->make(true);
        }

        $accounts = Account::all();
        return view('admin.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('account_create'), 403);
        $accountsgroup = AccountsGroup::pluck('name', 'id');
        return view('admin.accounts.create', compact('accountsgroup'));        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('account_create'), 403);

        $accounts_group_id = $request->input('accounts_group_id');
        $last_code=$this->acc_get_last_code($accounts_group_id);
        $code=acc_code_generate($last_code,5,2);
        $data=array_merge($request->all(), ['code' => $code]);
        $account=Account::create($data);

        return redirect()->route('admin.accounts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        abort_unless(\Gate::allows('account_show'), 403);

        return view('admin.accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit(Account $account)
    {
        abort_unless(\Gate::allows('account_edit'), 403);
        $accountsgroup = AccountsGroup::pluck('name', 'id');
        return view('admin.accounts.edit', compact('account','accountsgroup'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        abort_unless(\Gate::allows('account_edit'), 403);

        //check if locked
        $accountlock = Accountlock::where('account_id', '=', $account->id)->get();
        if($account->accounts_group_id!=$request->input('accounts_group_id') && count($accountlock)==0){
            $accounts_group_id = $request->input('accounts_group_id');
            $last_code=$this->acc_get_last_code($accounts_group_id);
            $code=acc_code_generate($last_code,5,2);
            $data=array_merge($request->all(), ['code' => $code]);
        }else{
            $data=['name' => $request->input('name'),'accounts_group_id' => $account->accounts_group_id];
        }
        $account->update($data);

        return redirect()->route('admin.accounts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        abort_unless(\Gate::allows('account_delete'), 403);

        // $account->delete();

        // return back();
        return redirect()->route('admin.accounts.index');
    }

    public function massDestroy(MassDestroyAccountRequest $request)
    {
        // Account::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return redirect()->route('admin.accounts.index');
    }
}
