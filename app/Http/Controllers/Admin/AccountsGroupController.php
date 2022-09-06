<?php

namespace App\Http\Controllers\Admin;

use App\AccountsGroup;
use App\AccountsType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\MassDestroyAccountsGroupRequest;

class AccountsGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('accountsgroup_access'), 403);
        $accountsgroups = AccountsGroup::selectRaw('accounts_group.*,accounts_type.id as accounts_type_id,accounts_type.name as accounts_type_name')
            ->join('accounts_type', 'accounts_type.id', '=', 'accounts_group.accounts_type_id')
            ->orderBy('accounts_group.id', 'ASC')
            ->get();
        return view('admin.accountsgroups.index', compact('accountsgroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('accountsgroup_create'), 403);
        $accountstype = AccountsType::pluck('name', 'id');
        return view('admin.accountsgroups.create', compact('accountstype'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('accountsgroup_create'), 403);
        try {
            $accountsgroups = AccountsGroup::create($request->all());
        } catch (QueryException $exception) {
            return back()->withError('Duplicate Account Type ID ' . $request->input('accounts_type_id'))->withInput();
        }
        return redirect()->route('admin.accountsgroups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AccountsGroup  $accountsGroup
     * @return \Illuminate\Http\Response
     */
    public function show(AccountsGroup $accountsgroup)
    {
        abort_unless(\Gate::allows('accountsgroup_show'), 403);
        $accountstype = AccountsType::pluck('name', 'id');
        return view('admin.accountsgroups.show', compact('accountsgroup','accountstype'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AccountsGroup  $accountsGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountsGroup $accountsgroup)
    {
        abort_unless(\Gate::allows('accountsgroup_edit'), 403);
        $accountstype = AccountsType::pluck('name', 'id');
        return view('admin.accountsgroups.edit', compact('accountsgroup','accountstype'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AccountsGroup  $accountsGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AccountsGroup $accountsgroup)
    {
        abort_unless(\Gate::allows('accountsgroup_edit'), 403);

        $accountsgroup->update($request->all());

        return redirect()->route('admin.accountsgroups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AccountsGroup  $accountsGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountsGroup $accountsgroup)
    {
        abort_unless(\Gate::allows('accountsgroup_delete'), 403);

        $accountsgroup->delete();

        return back();
    }

    public function massDestroy(MassDestroyAccountsGroupRequest $request)
    {
        AccountsGroup::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
