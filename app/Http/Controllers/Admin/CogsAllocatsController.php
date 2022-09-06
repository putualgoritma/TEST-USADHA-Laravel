<?php

namespace App\Http\Controllers\Admin;

use App\CogsAllocat;
use App\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyCogsAllocatRequest;
use Illuminate\Database\QueryException;

class CogsallocatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('cogsallocat_access'), 403);
        //$cogsallocats = CogsAllocat::all();
        $cogsallocats = CogsAllocat::selectRaw('cogs_allocats.*,accounts.code,accounts.name')
            ->join('accounts', 'accounts.id', '=', 'cogs_allocats.account_id')
            ->orderBy('cogs_allocats.id', 'ASC')
            ->get();
        return view('admin.cogsallocats.index', compact('cogsallocats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('cogsallocat_create'), 403);
        $accounts = Account::where('accounts_group_id', 12)
        ->pluck('name', 'id');
        //$products = Product::where('type', 'single')->get();
        return view('admin.cogsallocats.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('cogsallocat_create'), 403);
        //$cogsallocat = CogsAllocat::create($request->all());
        try {
            $cogsallocat = CogsAllocat::create($request->all());
        } catch (QueryException $exception) {
            return back()->withError('Duplicate Account ID ' . $request->input('account_id'))->withInput();
        }
        return redirect()->route('admin.cogsallocats.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CogsAllocat  $cogsallocat
     * @return \Illuminate\Http\Response
     */
    public function show(CogsAllocat $cogsallocat)
    {
        abort_unless(\Gate::allows('cogsallocat_show'), 403);
        $accounts = Account::pluck('name', 'id');
        return view('admin.cogsallocats.show', compact('cogsallocat','accounts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CogsAllocat  $cogsallocat
     * @return \Illuminate\Http\Response
     */
    public function edit(CogsAllocat $cogsallocat)
    {
        abort_unless(\Gate::allows('cogsallocat_edit'), 403);
        $accounts = Account::pluck('name', 'id');
        return view('admin.cogsallocats.edit', compact('cogsallocat','accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CogsAllocat  $cogsallocat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CogsAllocat $cogsallocat)
    {
        abort_unless(\Gate::allows('cogsallocat_edit'), 403);

        $cogsallocat->update($request->all());

        return redirect()->route('admin.cogsallocats.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CogsAllocat  $cogsallocat
     * @return \Illuminate\Http\Response
     */
    public function destroy(CogsAllocat $cogsallocat)
    {
        abort_unless(\Gate::allows('cogsallocat_delete'), 403);

        $cogsallocat->delete();

        return back();
    }

    public function massDestroy(MassDestroyCogsAllocatRequest $request)
    {
        CogsAllocat::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
