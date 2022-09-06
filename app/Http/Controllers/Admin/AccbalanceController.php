<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Ledger;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccbalanceController extends Controller
{
    public function accMutation(Request $request)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $id = !empty($request->account) ? $request->account : 0;
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');
        if ($request->ajax()) {

            if($id>0){
            $query = Ledger::selectRaw("ledgers.register,ledgers.memo,ledger_entries.entry_type,ledger_entries.amount,accounts.name")
                ->rightjoin('ledger_entries', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
                ->join('accounts', 'accounts.id', '=', 'ledger_entries.accounts_id')
                ->where('ledgers.status', '=', 'approved')
                ->where('ledger_entries.accounts_id', '=', $id)
                ->whereBetween('ledgers.register', [$from, $to])
                ->orderBy("ledgers.register", "asc");
            }else{
            $query = Ledger::selectRaw("ledgers.register,ledgers.memo,ledger_entries.entry_type,ledger_entries.amount,accounts.name")
                ->rightjoin('ledger_entries', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
                ->join('accounts', 'accounts.id', '=', 'ledger_entries.accounts_id')
                ->where('ledgers.status', '=', 'approved')
                ->whereBetween('ledgers.register', [$from, $to])
                ->orderBy("ledgers.register", "asc");
            }
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('register', function ($row) {
                return $row->register ? $row->register : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('debit', function ($row) {
                return $row->entry_type == 'D' ? number_format($row->amount, 2) : 0;
            });

            $table->editColumn('credit', function ($row) {
                return $row->entry_type == 'C' ? number_format($row->amount, 2) : 0;
            });

            $table->editColumn('balance', function ($row) {
                return '-';
            });

            $table->rawColumns(['placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $accounts = Account::orderBy("accounts_group_id", "ASC")->orderBy('code', 'ASC')->get();
        $ledger_entries = Ledger::selectRaw("ledgers.register,ledgers.memo,ledger_entries.entry_type,ledger_entries.amount,accounts.name")
            ->rightjoin('ledger_entries', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->join('accounts', 'accounts.id', '=', 'ledger_entries.accounts_id')
            ->where('ledgers.status', '=', 'approved')
            ->where('ledger_entries.accounts_id', '=', $id)
            ->whereBetween('ledgers.register', [$from, $to])
            ->orderBy("ledgers.register", "asc");

        return view('admin.accounts.mutation', compact('ledger_entries','accounts'));
    }

    public function mutation($id)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $ledger_entries = Ledger::selectRaw("ledgers.register,ledgers.memo,ledger_entries.entry_type,ledger_entries.amount,accounts.name")
            ->rightjoin('ledger_entries', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->join('accounts', 'accounts.id', '=', 'ledger_entries.accounts_id')
            ->where('ledgers.status', '=', 'approved')
            ->where('ledger_entries.accounts_id', '=', $id)
            ->orderBy("ledgers.register", "asc")
            ->get();
        //return $ledger_entries;

        return view('admin.accounts.mutation', compact('ledger_entries'));
    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        $accounts = Account::selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->leftJoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->where('ledgers.status', '=', 'approved')
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->orderBy("accounts.accounts_group_id", "ASC")
            ->orderBy('accounts.code', 'ASC')
            ->groupBy('accounts.id')
            ->get();
        //return $accounts;

        return view('admin.accounts.balance', compact('accounts'));
    }

    public function trial(Request $request)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        $accounts_assets = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 1)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        $accounts_liabilities = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 2)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        $accounts_equity = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 3)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        $accounts_revenues = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 4)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        $accounts_expenses = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 5)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        return view('admin.accounts.balancetrial', compact('accounts_assets', 'accounts_liabilities', 'accounts_equity', 'accounts_revenues', 'accounts_expenses'));
    }

    public function profitLoss(Request $request)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        $accounts_revenues = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 4)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        $accounts_expenses = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 5)
            ->whereBetween(DB::raw('DATE(ledgers.register)'), [$from, $to])
            ->groupBy('accounts.id')
            ->get();

        return view('admin.accounts.profitloss', compact('accounts_revenues', 'accounts_expenses'));
    }
}
