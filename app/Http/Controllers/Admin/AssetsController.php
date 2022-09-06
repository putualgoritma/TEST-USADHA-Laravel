<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Asset;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\CogsAllocat;
use App\Customer;
use App\Account;
use App\Traits\TraitModel;
use App\Ledger;

class AssetsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_unless(\Gate::allows('asset_access'), 403);

        $assets = Asset::get();
        //return $assets;

        return view('admin.assets.index', compact('assets'));
    }

    public function create()
    {
        $last_code = $this->get_last_code('asset');
        $code = acc_code_generate($last_code, 8, 3);
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();
        return view('admin.assets.create', compact('code','accounts'));
    }

    public function store(StoreAssetRequest $request)
    {
        abort_unless(\Gate::allows('asset_create'), 403);

        $memo="Registrasi Inventaris";
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo, 'status' => 'approved'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        $acc_inv = '22';
        $accounts = array($acc_inv, $request->input('accounts_id'));
        $amounts = array($request->input('value'), $request->input('value'));
        $types = array('D', 'C');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        $data=array_merge($request->all(), ['ledger_id' => $ledger_id]);
        $asset=Asset::create($data);
        
        return redirect()->route('admin.assets.index');
    }

    public function edit(Asset $asset)
    {
        abort_unless(\Gate::allows('asset_edit'), 403);

        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        return view('admin.assets.edit', compact('asset','accounts'));
    }

    public function update(StoreAssetRequest $request, Asset $asset)
    {
        abort_unless(\Gate::allows('asset_edit'), 403);

        // $data = $request->all();
        
        // $asset->update($data);
        
        return redirect()->route('admin.assets.index');

    }

    public function show(Asset $asset)
    {
        abort_unless(\Gate::allows('asset_show'), 403);

        return view('admin.assets.show', compact('asset'));
    }

    public function destroy(Asset $asset)
    {
        abort_unless(\Gate::allows('asset_delete'), 403);

        if ($asset->ledger_id > 0) {
            $ledger = Ledger::find($asset->ledger_id);
            $ledger->accounts()->detach();
            $ledger->delete();
        }
        $asset->delete();

        return back();
    }

    public function massDestroy(MassDestroyAssetRequest $request)
    {
        //Asset::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
