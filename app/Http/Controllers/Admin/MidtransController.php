<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Topup;
use Symfony\Component\HttpFoundation\Response;
use OneSignal;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use Gate;
use App\Ledger;
use App\LogNotif;
use App\OrderPoint;
use App\Point;
use App\Account;
use App\Customer;

class MidtransController extends Controller
{
    public function approvedprocess($topup, $notification)
    {
        try {
            
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
            // $topup->midtran = $notification->payment_type;
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

            return 'sukses';
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function notificationHandlerTopup(Request $request)
    {
        try {
            Config::$serverKey = config('midtrans.serverKey');
            Config::$isProduction = config('midtrans.isProduction');
            Config::$isSanitized = config('midtrans.isSanitized');
            Config::$is3ds = config('midtrans.is3ds');

            // buat instance midtrans notification
            $notification = new Notification();
            $order = explode('-', $notification->order_id);
            // assign variabel untuk memudahkan coding 

            $status = $notification->transaction_status; 
            $type = $notification->payment_type;
            $fraud = $notification->fraud_status;
            // $order_id = 'Midtrans tes-' . $notification->order_id;
            
            $order_id  =  $order[1];

            // cari transaski berdarkan id
            $topup = Topup::findOrFail($order_id);
            
            // return response( $notification);






        
            // handle notication handler
            // return view('admin.notifmidtrans.failed', compact('topup'));



            if($status == 'capture'){
                if($type='credit_card'){
                    if($fraud = 'challenge'){
                        return view('admin.notifmidtrans.unfinish');
                    }else {
                        // return view('admin.notifmidtrans.success');
                        $approved = $this->approvedprocess($topup, $notification);
                    }
                }
            }
            else if ($status == 'settlement'){
                $approved = $this->approvedprocess($topup, $notification);
            }
            else if ($status == 'pending'){
                return view('admin.notifmidtrans.unfinish');
            }
            else if ($status == 'deny'){
                return view('admin.notifmidtrans.failed');
            }
            else if ($status == 'expire'){
                return view('admin.notifmidtrans.failed');
            }
            else if ($status == 'cancel'){
                return view('admin.notifmidtrans.unfinish');
            }

            return $approved;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th,
                'funaproved' =>  $approved 
            ]);
        }

        // dd($notification);
        // return response()->json([
        //     'meta' => [
        //         'code' => '200',
        //         'message' => 'notificati'
        //     ]
        // ]);
    }

    public function finishRedirect(Request $request)
    {
        return view('admin.notifmidtrans.success');
    }

    public function unfinishRedirect(Request $request)
    {
        return view('admin.notifmidtrans.unfinish');
    }

    public function errorRedirect(Request $request)
    {
        return view('admin.notifmidtrans.failed');
    }
}