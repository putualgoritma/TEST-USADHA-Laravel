<?php

namespace App\Http\Controllers\Admin;

use App\Accountlock;
use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAccountlockRequest;
use App\Http\Requests\StoreAccountlockRequest;
use App\Http\Requests\UpdateAccountlockRequest;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class AccountlockController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('accountlock_access'), 403);

        if ($request->ajax()) {
            $query = Accountlock::with('accounts')->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'accountlock_show';
                $editGate = 'accountlock_edit';
                $deleteGate = 'accountlock_delete';
                $crudRoutePart = 'accountlocks';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('account_id', function ($row) {
                return $row->accounts->name ? $row->accounts->name : "";
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        // $accountlock = Accountlock::where('type', '!=', 'member')
        // ->where('def', '=', '0')
        // ->get();

        $accountlock = Accountlock::with('accounts')->get();
        $accounts = Account::orderBy("code", "asc")
            ->get();

        return view('admin.accountlocks.index', compact('accountlock','accounts'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('accountlock_create'), 403);

        $accounts = Account::orderBy("code", "asc")
            ->get();

        return view('admin.accountlocks.create', compact('accounts'));
    }

    public function store(StoreAccountlockRequest $request)
    {
        abort_unless(\Gate::allows('accountlock_create'), 403);

        $accountlock = Accountlock::create($request->all());

        return redirect()->route('admin.accountlocks.index');
    }

    public function edit(Accountlock $accountlock)
    {
        abort_unless(\Gate::allows('accountlock_edit'), 403);

        $accounts = Account::orderBy("code", "asc")
            ->get();

        return view('admin.accountlocks.edit', compact('accountlock','accounts'));
    }

    public function update(UpdateAccountlockRequest $request, Accountlock $accountlock)
    {
        abort_unless(\Gate::allows('accountlock_edit'), 403);

        $accountlock->update($request->all());
        return redirect()->route('admin.accountlocks.index');
    }

    public function show(Accountlock $accountlock)
    {
        abort_unless(\Gate::allows('accountlock_show'), 403);

        return view('admin.accountlocks.show', compact('accountlock'));
    }

    public function destroy(Accountlock $accountlock)
    {
        abort_unless(\Gate::allows('accountlock_delete'), 403);

        return back()->withError('Gagal Delete, Member Active!');
    }

    public function massDestroy(MassDestroyAccountlockRequest $request)
    {
        // Accountlock::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return back()->withError('Gagal Delete, Member Active!');
    }
}
