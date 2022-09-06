<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleReturRequest;
use App\Http\Requests\UpdateSaleReturRequest;
use App\Http\Requests\MassDestroySaleReturRequest;
use App\Ledger;
use App\NetworkFee;
use App\Order;
use App\Package;
use App\Product;
use App\Traits\TraitModel;
use Gate;
use Illuminate\Http\Request;
use OneSignal;
use Symfony\Component\HttpFoundation\Response;
use Berkayk\OneSignal\OneSignalClient;
use App\LogNotif;

class SaleReturController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function index()
    {
        abort_if(Gate::denies('saleretur_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salereturs = Order::where('type','sale_retur')
            ->with('products')
            ->with('customers')
            ->with('accounts')
            ->get();

        //return $salereturs;
        return view('admin.saleretur.index', compact('salereturs'));
    }

    public function create()
    {
        abort_if(Gate::denies('saleretur_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $products = Product::all();
        $customers = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->orwhere('id', 67)
            ->get();

        $last_code = $this->get_last_code('sale_retur');
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.saleretur.create', compact('products', 'customers', 'accounts', 'code'));
    }

    public function store(StoreSaleReturRequest $request)
    {
        //get total
        $total = 0;
        $cogs_total = 0;
        $products = $request->input('products', []);
        $quantities = $request->input('quantities', []);
        $prices = $request->input('prices', []);
        $cogs = $request->input('cogs', []);
        for ($product = 0; $product < count($products); $product++) {
            $total += $quantities[$product] * $prices[$product];
            $cogs_total += $quantities[$product] * $cogs[$product];
        }

        /* proceed ledger */
        $memo = 'Transaksi Retur Penjualan Barang ' . $request->input('code');
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //set ledger entry arr
        $acc_inv_stock = '20';//acc persediaan barang (D)
        $acc_sale_retur = '69';//acc retur penjualan (D)
        $acc_exp_cogs = '45';//acc hpp (C)
        $total_pay = $total;
        $accounts = array($acc_inv_stock, $acc_exp_cogs, $acc_sale_retur);
        $amounts = array($cogs_total, $cogs_total, $total);
        $types = array('D', 'C', 'D');
        //if agent get cashback
        $customer_row = Customer::select('*')
            ->Where('id', '=', $request->input('customers_id'))
            ->get();
        if ($customer_row[0]->type == 'agent') {
            //get cashback 01
            $acc_disc = 68;
            $acc_res_cashback = 70;
            $networkfee_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA01')
                ->get();
            $networkfee2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBA02')
                ->get();
            //LEV 1
            $lev_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'LEV')
                ->get();
            //set ref fee
            $ref_fee_row = NetworkFee::select('*')
                ->Where('code', '=', 'REF')
                ->get();
            //set cashback member 1
            $cashback_mbr_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM01')
                ->get();
            //set cashback member 2
            $cashback_mbr2_row = NetworkFee::select('*')
                ->Where('code', '=', 'CBM02')
                ->get();
            $cba1 = (($networkfee_row[0]->amount) / 100) * $total;
            $cba2 = (($networkfee2_row[0]->amount) / 100) * $total;
            $total_disc = $networkfee_row[0]->amount + $networkfee2_row[0]->amount + $lev_fee_row[0]->amount + $ref_fee_row[0]->amount + $cashback_mbr_row[0]->amount + $cashback_mbr2_row[0]->amount;
            $amount_disc = (($total_disc) / 100) * $total;
            $amount_res_cashback = $amount_disc - $cba1;
            $total_pay = $total - $cba1;
            //$acc_points = '67';
            //push array jurnal
            array_push($accounts, $acc_disc, $acc_res_cashback);
            array_push($amounts, $amount_disc, $amount_res_cashback);
            array_push($types, "C", "D");
        }
        //push array jurnal
        array_push($accounts, $request->input('accounts_id'));
        array_push($amounts, $total_pay);
        array_push($types, "C");
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        /* proceed order, order products, order details (inventory stock) */
        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();
        $owner_def = $ref_def_id[0]->id;
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        //set order
        $payment_type='cash';
        if($request->input('accounts_id')=='67'){
            $payment_type='point';
        }        
        $data = array_merge($request->all(), ['memo' => $memo, 'total' => $total, 'type' => 'sale_retur', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => $payment_type]);
        $order = Order::create($data);
        //set points
        if($request->input('accounts_id')=='67'){
            //debit point customer
            $points_id = 1;
            $order->points()->attach($points_id, ['amount' => $total_pay, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Penambahan Poin dari ' . $memo, 'customers_id' => $customers_id]);
        }
        //set order products
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                $order->products()->attach($products[$product], ['quantity' => $quantities[$product], 'price' => $prices[$product], 'cogs' => $cogs[$product]]);
            }
        }
        //set order order details (inventory stock)
        for ($product = 0; $product < count($products); $product++) {
            if ($products[$product] != '') {
                //check if package
                $products_type = Product::select('type')
                    ->where('id', $products[$product])
                    ->get();
                $products_type = json_decode($products_type, false);
                if ($products_type[0]->type == 'package') {
                    $package_items = Package::with('products')
                        ->where('id', $products[$product])
                        ->get();
                    $package_items = json_decode($package_items, false);
                    $package_items = $package_items[0]->products;
                    //loop items
                    foreach ($package_items as $key => $value) {
                        $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                        $order->productdetails()->attach($value->id, ['quantity' => $quantities[$product] * $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                    }
                } else {
                    $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $owner_def]);
                    $order->productdetails()->attach($products[$product], ['quantity' => $quantities[$product], 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $customers_id]);
                }
            }
        }
        return redirect()->route('admin.salereturs.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('saleretur_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        $order = Order::find($id);
        $products = Product::all();

        $order->load('products');

        //return $order;
        return view('admin.saleretur.edit', compact('products', 'order'));
        //return Redirect()->Route('admin.saleretur.index');
    }

    public function update(UpdateSaleReturRequest $request, Order $order)
    {
        // $order->update($request->all());

        // $order->products()->detach();
        // $products = $request->input('products', []);
        // $quantities = $request->input('quantities', []);
        // for ($product = 0; $product < count($products); $product++) {
        //     if ($products[$product] != '') {
        //         $order->products()->attach($products[$product], ['quantity' => $quantities[$product]]);
        //     }
        // }

        return redirect()->route('admin.salereturs.index');
    }

    public function show(Order $order)
    {
        abort_if(Gate::denies('saleretur_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $order->load('products');

        //return view('admin.saleretur.show', compact('order'));
        return Redirect()->Route('admin.salereturs.index');
    }

    public function destroy(Order $order)
    {
        abort_if(Gate::denies('saleretur_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // $order = Order::find($order->ledgers_id);
        // return $order;
        if ($order->ledgers_id > 0) {
            $ledger = Ledger::find($order->ledgers_id);
            $ledger->accounts()->detach();
            $ledger->delete();
        }

        $order->products()->detach();
        $order->productdetails()->detach();
        $order->points()->detach();
        $order->delete();
        //return back();
        //return Redirect()->Route('admin.saleretur.index');
    }

    public function massDestroy(MassDestroySaleReturRequest $request)
    {
        Order::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
        //return Redirect()->Route('admin.saleretur.index');
    }
}
