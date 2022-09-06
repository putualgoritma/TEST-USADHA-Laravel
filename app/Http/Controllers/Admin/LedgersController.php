<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyLedgerRequest;
use App\Http\Requests\StoreLedgerRequest;
use App\Http\Requests\UpdateLedgerRequest;
use App\Ledger;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use DB;

class LedgersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('ledger_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');
        if ($request->ajax()) {

            $query = Ledger::with('accounts')
                ->where('status', '=', 'approved')
                ->whereBetween(DB::raw('DATE(register)'), [$from, $to])
                ->orderBy("id", "DESC");

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'ledger_show';
                $editGate = 'ledger_edit';
                $deleteGate = 'ledger_delete';
                $crudRoutePart = 'ledgers';

                return view('partials.datatablesOrders', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : "";
            });

            $table->editColumn('customers_id', function ($row) {
                return $row->customers_id ? $row->customers_id : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('account', function ($row) {
                $account_list = '<ul>';
                foreach ($row->accounts as $key => $item) {
                    $account_list .= '<li>' . $item->name . " Rp. (" . number_format($item->pivot->amount,2) . ")" . " (" . $item->pivot->entry_type . ")" .'</li>';
                }
                $account_list .= '</ul>';
                return $account_list;
            });

            $table->rawColumns(['actions', 'placeholder', 'account']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $ledgers = Ledger::with('accounts')
            ->where('status', '=', 'approved')
            ->whereBetween('register', [$from, $to])
            ->orderBy("id", "DESC");
        //return $ledgers;

        return view('admin.ledgers.index', compact('ledgers'));
    }

    public function create()
    {
        abort_if(Gate::denies('ledger_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $accounts = Account::orderBy("accounts_group_id", "ASC")->orderBy('code', 'ASC')->get();

        return view('admin.ledgers.create', compact('accounts'));
    }

    public function store(StoreLedgerRequest $request)
    {
        //check balance D C
        $debit_total = 0;
        $credit_total = 0;
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        $types = $request->input('types', []);
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                if ($types[$account] == "D") {
                    $debit_total += $amounts[$account];
                } else {
                    $credit_total += $amounts[$account];
                }
            }
        }

        if ($debit_total == $credit_total && $debit_total > 0) {
            $ledger = Ledger::create($request->all());
            //store to ledger_entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }
            return redirect()->route('admin.ledgers.index');
        } else {
            return back()->withError('Neraca Tidak Balance!')->withInput();
        }

    }

    public function edit(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $accounts = Account::all();

        $ledger->load('accounts');

        return view('admin.ledgers.edit', compact('accounts', 'ledger'));
    }

    public function update(UpdateLedgerRequest $request, Ledger $ledger)
    {
        $ledger->update($request->all());

        $ledger->accounts()->detach();
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['amount' => $amounts[$account]]);
            }
        }

        return redirect()->route('admin.ledgers.index');
    }

    public function show(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger->load('accounts');

        return view('admin.ledgers.show', compact('ledger'));
    }

    public function destroy(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger->accounts()->detach();
        $ledger->delete();

        return back();
    }

    public function massDestroy(MassDestroyLedgerRequest $request)
    {
        Ledger::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
