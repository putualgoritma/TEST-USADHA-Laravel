<?php

namespace App\Http\Controllers\Admin;

use App\CogProduct;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyProductionRequest;
use App\Http\Requests\StoreProductionRequest;
use App\Http\Requests\UpdateProductionRequest;
use App\Ledger;
use App\Product;
use App\Production;
use App\Traits\TraitModel;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductionsController extends Controller
{
    use TraitModel;

    public function index()
    {
        abort_if(Gate::denies('production_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $productions = Production::with('products')
            ->where('type', 'production')
            ->get();

        return view('admin.productions.index', compact('productions'));
    }

    public function create()
    {
        abort_if(Gate::denies('production_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //$products = Product::all();
        $products = Product::select('*')
            ->where('type', 'single')
            ->get();

        $last_code = $this->prd_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.productions.create', compact('products', 'code'));
    }

    public function store(StoreProductionRequest $request)
    {
        //get total
        $total = 0;
        $cogs_total = 0;
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        for ($product = 0; $product < count($products); $product++) {
            $total += $quantities[$product] * $prices[$product];
            $product_row = Product::find($products[$product]);
            $cogs_total += $quantities[$product] * $product_row->cogs;
        }
        //proceed ledger hpp
        $data = ['register' => $request->input('register'), 'title' => 'Penambahan Stok Barang Jadi', 'memo' => 'Penambahan Stok Barang Jadi'];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //cogs total acc
        $accounts = array('20');
        $amounts = array($cogs_total);
        $types = array('D');
        //loop products
        for ($product = 0; $product < count($products); $product++) {
            //get cogs allocate
            $cogsallocats = CogProduct::select('accounts_id', 'amount')
                ->where('products_id', $products[$product])
                ->get();
            //set ledger entry arr
            $cogsallocats_arr = json_decode($cogsallocats, true);
            foreach ($cogsallocats_arr as $key => $value) {
                array_push($accounts, $value['accounts_id']);
                $qty_amount = $quantities[$product] * $value['amount'];
                array_push($amounts, $qty_amount);
                array_push($types, 'C');
            }
        }
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        //set def
        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();
        $customers_id = $ref_def_id[0]->id;
        $warehouses_id = 1;
        //set order production
        $data = array_merge($request->all(), ['total' => $total, 'type' => 'production', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'none']);
        $production = Production::create($data);
        //set order production products
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $production->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product]]);
            }
        }
        //set order production details (inventory stock)
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $production->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => 85]);
            }
        }

        return redirect()->route('admin.productions.index');
    }

    public function edit(Production $production)
    {
        abort_if(Gate::denies('production_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $products = Product::select('*')
            ->where('type', 'single')
            ->get();

        $production->load('products');

        return view('admin.productions.edit', compact('products', 'production'));
    }

    public function update(UpdateProductionRequest $request, Production $production)
    {
        abort_if(Gate::denies('production_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        //get total
        $total = 0;
        $cogs_total = 0;
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        for ($product = 0; $product < count($products); $product++) {
            $total += $quantities[$product] * $prices[$product];
            $product_row = Product::find($products[$product]);
            $cogs_total += $quantities[$product] * $product_row->cogs;
        }
        //proceed ledger hpp
        $ledger = Ledger::find($production->ledgers_id);
        $ledger_id = $ledger->id;
        $ledger->accounts()->detach();
        //cogs total acc
        $accounts = array('20');
        $amounts = array($cogs_total);
        $types = array('D');
        //loop products
        for ($product = 0; $product < count($products); $product++) {
            //get cogs allocate
            $cogsallocats = CogProduct::select('accounts_id', 'amount')
                ->where('products_id', $products[$product])
                ->get();
            //set ledger entry arr
            $cogsallocats_arr = json_decode($cogsallocats, true);
            foreach ($cogsallocats_arr as $key => $value) {
                array_push($accounts, $value['accounts_id']);
                $qty_amount = $quantities[$product] * $value['amount'];
                array_push($amounts, $qty_amount);
                array_push($types, 'C');
            }
        }
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        //set def
        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();
        $customers_id = $ref_def_id[0]->id;
        $warehouses_id = 1;
        //set order production
        $production->products()->detach();
        $production->productdetails()->detach();
        $data = array_merge($request->all(), ['total' => $total, 'type' => 'production', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'none']);
        $production->update($data);
        //set order production products
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $production->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product]]);
            }
        }
        //set order production details (inventory stock)
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $production->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => 85]);
            }
        }

        return redirect()->route('admin.productions.index');
    }

    public function show(Production $production)
    {
        abort_if(Gate::denies('production_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $production->load('products');

        //return view('admin.productions.show', compact('production'));
        return Redirect()->Route('admin.productions.index');
    }

    public function destroy(Production $production)
    {
        abort_if(Gate::denies('production_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //get ledger
        $ledger = Ledger::find($production->ledgers_id);
        //ledger entries detach
        $ledger->accounts()->detach();
        //ledger delete   
        $ledger->delete();     
        //detach products & details
        $production->products()->detach();
        $production->productdetails()->detach();
        $production->delete();
        return back();
    }

    public function massDestroy(MassDestroyProductionRequest $request)
    {
        //Production::whereIn('id', request('ids'))->delete();

        //return response(null, Response::HTTP_NO_CONTENT);
        return Redirect()->Route('admin.productions.index');
    }
}
