<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyTopupRequest;
use App\Http\Requests\StoreTopupRequest;
use App\Http\Requests\UpdateTopupRequest;
use App\Ledger;
use App\LogNotif;
use App\OrderPoint;
use App\Point;
use App\Topup;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OneSignal;

class TopupsController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        //get env
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function transferIndex()
    {
        abort_if(Gate::denies('topup_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transfers = Topup::with('points')
            ->with('customers')
            ->where('type', 'transfer')
            ->orderBy("id", "DESC")
            ->get();
        //return $transfers;
        //find acc pay name
        $account = Account::find($transfers[0]->acc_pay);

        return view('admin.topups.transferindex', compact('transfers', 'account'));
    }

    public function transferApproved($id)
    {
        abort_if(Gate::denies('topup_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transfer = Topup::find($id);

        return view('admin.topups.transferapproved', compact('transfer'));
    }

    public function transferApprovedProcess(Request $request)
    {
        abort_unless(\Gate::allows('topup_show'), 403);
        if ($request->has('status')) {
            //get
            $transfer = Topup::find($request->input('id'));           

            //update
            $transfer->status = 'approved';
            $transfer->save();
            //get list order points
            $orderpoint_arr = OrderPoint::select('*')
                ->where('orders_id', $transfer->id)
                ->get();
            $user_dest_to=0;
            foreach ($orderpoint_arr as $key => $value) {
                if($value->type=='D'){
                    $user_dest_to=$value->customers_id;
                }
                $orderpoint = OrderPoint::find($value->id);
                $orderpoint->status = 'onhand';
                $orderpoint->save();
            }
            
            //push notif
            $user = Customer::find($user_dest_to);
            if(!empty($user)){
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Transfer sejumlah '.$transfer->total.' sudah disetujui.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $user->id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($id_onesignal!=""){
            if($user->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }}}
        }
        return redirect()->route('admin.transfers.index');

    }

    public function transferCancelled($id)
    {
        abort_if(Gate::denies('topup_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transfer = Topup::find($id);

        return view('admin.topups.transfercancelled', compact('transfer'));
    }

    public function transferCancelledProcess(Request $request)
    {
        abort_unless(\Gate::allows('topup_show'), 403);
        if ($request->has('status')) {
            //get
            $transfer = Topup::find($request->input('id'));

            //update
            $transfer->status = 'closed';
            $transfer->save();
            //get list order points
            $orderpoint_arr = OrderPoint::select('*')
                ->where('orders_id', $transfer->id)
                ->get();
            $user_dest_to=0;
            foreach ($orderpoint_arr as $key => $value) {
                if($value->type=='D'){
                    $user_dest_to=$value->customers_id;
                }
                $orderpoint = OrderPoint::find($value->id);
                $orderpoint->status = 'closed';
                $orderpoint->save();
            }
            
            //push notif
            $user = Customer::find($user_dest_to);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Transfer sejumlah '.$transfer->total.' sudah dibatalkan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $user->id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($user->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
        }
        return redirect()->route('admin.transfers.index');

    }

    public function index()
    {
        abort_if(Gate::denies('topup_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $topups = Topup::with('points')
            ->with('customers')
            ->where('type', 'topup')
            ->orderBy("id", "DESC")
            ->get();
        //return $topups;
        //find acc pay name
        $account = Account::find($topups[0]->acc_pay);

        return view('admin.topups.index', compact('topups', 'account'));
    }

    public function create()
    {
        abort_if(Gate::denies('topup_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();
        $customers = Customer::select('*')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        $last_code = $this->top_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.topups.create', compact('points', 'customers', 'accounts', 'code'));
    }

    public function store(StoreTopupRequest $request)
    {
        //get total
        $total = 0;
        $points = $request->input('points', []);
        $amounts = $request->input('amounts', []);
        for ($point = 0; $point < count($points); $point++) {
            $total += $amounts[$point];
        }
        //proceed ledger topup
        $last_code = $this->get_last_code('topup');
        $code = acc_code_generate($last_code, 8, 3);
        $member = Customer::find($request->input('customers_id'));
        $memo = "Topup Poin " . $member->code . "-" . $member->name;
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //set ledger entry arr
        $acc_points = '67';
        $accounts = array($acc_points, $request->input('accounts_id'));
        $amounts_acc = array($total, $total);
        $types = array('C', 'D');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts_acc[$account]]);
            }
        }

        //set def
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        //set topup
        $data = array_merge($request->all(), ['total' => $total, 'type' => 'topup', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash', 'memo' => $memo, 'code' => $code]);
        $topup = Topup::create($data);
        //set topup points
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $topup->points()->attach($points[$point], ['amount' => $amounts[$point], 'type' => 'D', 'status' => 'onhand', 'customers_id' => $customers_id, 'memo' => $memo]);
            }
        }

        return redirect()->route('admin.topups.index');
    }

    public function edit(Topup $topup)
    {
        abort_if(Gate::denies('topup_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();

        $topup->load('points');

        return view('admin.topups.edit', compact('points', 'topup'));
    }

    public function update(UpdateTopupRequest $request, Topup $topup)
    {
        $topup->update($request->all());

        $topup->points()->detach();
        $points = $request->input('points', []);
        $quantities = $request->input('quantities', []);
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $topup->points()->attach($points[$point], ['quantity' => $quantities[$point]]);
            }
        }

        return redirect()->route('admin.topups.index');
    }

    public function show(Topup $topup)
    {
        abort_if(Gate::denies('topup_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $topup->load('points');

        return view('admin.topups.show', compact('topup'));
    }

    public function destroy(Topup $topup)
    {
        abort_if(Gate::denies('topup_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $topup->delete();

        return back();
    }

    public function massDestroy(MassDestroyTopupRequest $request)
    {
        Topup::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function approved($id)
    {
        abort_if(Gate::denies('topup_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $topup = Topup::find($id);

        return view('admin.topups.approved', compact('topup'));
    }

    public function approvedprocess(Request $request)
    {
        abort_unless(\Gate::allows('topup_show'), 403);
        if ($request->has('status')) {
            //get
            $topup = Topup::find($request->input('id'));

            /* proceed ledger */
            $data = ['register' => $topup->register, 'title' => 'Topup Poin', 'memo' => 'Topup Poin'];
            //$data = array_merge($request->all(), ['total' => $total, 'type' => 'topup', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash']);
            //return $data;
            $ledger = Ledger::create($data);
            $ledgers_id = $ledger->id;
            //set ledger entry arr
            $acc_pay = $topup->acc_pay;
            $acc_points = '67'; //utang poin
            $total = $topup->total;
            $accounts = array($acc_points, $acc_pay);
            $amounts = array($total, $total);
            $types = array('C', 'D');
            //ledger entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }

            //update
            $topup->status = 'approved';
            $topup->ledgers_id = $ledgers_id;
            $topup->save();
            //get list order points
            $orderpoint_arr = OrderPoint::select('*')
                ->where('orders_id', $topup->id)
                ->get();
            foreach ($orderpoint_arr as $key => $value) {
                $orderpoint = OrderPoint::find($value->id);
                $orderpoint->status = 'onhand';
                $orderpoint->save();
            }
            
            //push notif
            $user = Customer::find($topup->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Topup sejumlah '.$topup->total.' sudah disetujui.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $topup->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($id_onesignal!=""){
            if($user->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }}
        }
        return redirect()->route('admin.topups.index');

    }

    public function cancelled($id)
    {
        abort_if(Gate::denies('topup_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $topup = Topup::find($id);

        return view('admin.topups.cancelled', compact('topup'));
    }

    public function cancelledProcess(Request $request)
    {
        abort_unless(\Gate::allows('topup_show'), 403);
        if ($request->has('status')) {
            //get
            $topup = Topup::find($request->input('id'));

            //update
            $topup->status = 'closed';
            $topup->save();
            //get list order points
            $orderpoint_arr = OrderPoint::select('*')
                ->where('orders_id', $topup->id)
                ->get();
            foreach ($orderpoint_arr as $key => $value) {
                $orderpoint = OrderPoint::find($value->id);
                $orderpoint->status = 'closed';
                $orderpoint->save();
            }
            
            //push notif
            $user = Customer::find($topup->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Topup sejumlah '.$topup->total.' sudah dibatalkan.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $topup->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($user->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
        }
        return redirect()->route('admin.topups.index');

    }
}
