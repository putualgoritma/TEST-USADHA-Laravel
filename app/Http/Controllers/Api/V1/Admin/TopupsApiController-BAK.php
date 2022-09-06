<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\LogNotif;
use App\NetworkFee;
use App\Order;
use App\OrderPoint;
use App\Topup;
use App\Traits\TraitModel;
use App\Withdraw;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use OneSignal;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class TopupsApiController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function actvBalance($id)
    {
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->first();

        $balance = Order::where('customers_activation_id', '=', $id)
            ->where('type', '=', 'activation_member')
            ->where('status', '=', 'approved')
            ->sum('bv_activation_amount');

        //Check if balance found or not.
        if (is_null($balance)) {
            $message = 'Balance not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $balance = $balance / $bvpo_row->amount;
            $message = 'Balance retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $balance,
            ]);
        }
    }

    public function convert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register' => 'required',
            'amount' => 'required',
            'customers_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        //set member
        $member = Customer::find($request->customers_id);

        $points_type = array();
        $amount = $request->amount;

        //loop points
        foreach ($request->points as $point_key => $point_row) {
            $arrIndex = $point_row['id'];
            $points_type["'" . $arrIndex . "'"] = (int) $this->points_balance_selected($request->customers_id, $arrIndex);
        }

        asort($points_type, 1);
        $amount_res = $amount;
        foreach ($points_type as $point_key => $point_value) {
            // echo $point_key." - ".$point_value." <br>";
            $amount_res -= $point_value;
        }

        //memo
        $memo = "Convert Poin " . $member->code . "-" . $member->name;

        if ($amount_res <= 0) {

            //set def
            $customers_id = $request->customers_id;
            $warehouses_id = 1;
            $ref_def_id = Customer::select('id')
                ->Where('def', '=', '1')
                ->get();
            $referals_id = $ref_def_id[0]->id;
            $last_code = $this->get_last_code('convert');
            $code = acc_code_generate($last_code, 8, 3);
            //set convert
            $data = array_merge($request->all(), ['code' => $code, 'total' => $amount, 'type' => 'point_conversion', 'status' => 'approved', 'ledgers_id' => 0, 'customers_id' => $customers_id, 'payment_type' => 'point', 'memo' => $memo]);
            $convert = Withdraw::create($data);
            //set convert points
            $amount_residu = $amount;
            foreach ($points_type as $point_key => $point_value) {
                $point_id = (int) str_replace("'", "", $point_key);
                if ($amount_residu > 0) {
                    $amount_point_convert = $point_value;
                    if ($amount_residu <= $point_value) {
                        $amount_point_convert = $amount_residu;
                    }
                    $amount_residu -= $point_value;
                    $convert->points()->attach($point_id, ['amount' => $amount_point_convert, 'type' => 'C', 'status' => 'onhand', 'memo' => $memo, 'customers_id' => $customers_id]);
                    $convert->points()->attach(1, ['amount' => $amount_point_convert, 'type' => 'D', 'status' => 'onhand', 'memo' => $memo, 'customers_id' => $customers_id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Konversi Berhasil.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
                'data' => $points_balance,
            ], 401);
        }
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register' => 'required',
            'amount' => 'required',
            'customers_id' => 'required',
            'bank_name' => 'required',
            'bank_acc_no' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        //set member
        $member = Customer::find($request->customers_id);

        $points_type = array();
        $amount = $request->amount;

        if (!isset($request->points)) {
            $points_type['1'] = $this->points_balance_selected($request->customers_id, 1);
        } else {
            //loop points
            foreach ($request->points as $point_key => $point_row) {
                $arrIndex = $point_row['id'];
                $points_type["'" . $arrIndex . "'"] = (int) $this->points_balance_selected($request->customers_id, $arrIndex);
            }}

        asort($points_type, 1);
        $amount_res = $amount;
        foreach ($points_type as $point_key => $point_value) {
            // echo $point_key." - ".$point_value." <br>";
            $amount_res -= $point_value;
        }

        //memo
        $memo = "Withdraw Poin " . $member->code . "-" . $member->name;

        if ($amount_res <= 0) {

            //set def
            $customers_id = $request->customers_id;
            $warehouses_id = 1;
            $ref_def_id = Customer::select('id')
                ->Where('def', '=', '1')
                ->get();
            $referals_id = $ref_def_id[0]->id;
            $last_code = $this->get_last_code('withdraw');
            $code = acc_code_generate($last_code, 8, 3);
            //set withdraw
            $data = array_merge($request->all(), ['code' => $code, 'total' => $amount, 'type' => 'withdraw', 'status' => 'pending', 'ledgers_id' => 0, 'customers_id' => $customers_id, 'payment_type' => 'point', 'memo' => $memo]);
            $withdraw = Withdraw::create($data);
            //set withdraw points
            $amount_residu = $amount;
            foreach ($points_type as $point_key => $point_value) {
                $point_id = (int) str_replace("'", "", $point_key);
                if ($amount_residu > 0) {
                    $amount_point_withdraw = $point_value;
                    if ($amount_residu <= $point_value) {
                        $amount_point_withdraw = $amount_residu;
                    }
                    $amount_residu -= $point_value;
                    $withdraw->points()->attach($point_id, ['amount' => $amount_point_withdraw, 'type' => 'C', 'status' => 'onhold', 'memo' => $memo, 'customers_id' => $customers_id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdraw Berhasil.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
                'data' => $points_balance,
            ], 401);
        }
    }

    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register' => 'required',
            'amount' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        //set member
        $member = Customer::find($request->from);
        //get point member
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->from)
            ->where('type', '=', 'D')
            ->where('status', '=', 'onhand')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->from)
            ->where('type', '=', 'C')
            ->where('status', '=', 'onhand')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;
        //set member destination
        $member_to = Customer::find($request->to);
        $memo = "Transfer Poin dari " . $member->code . "-" . $member->name . " ke " . $member_to->code . "-" . $member_to->name;

        //get total
        $total = $request->input('amount');
        $points_id = 1;

        if ($points_balance >= $total) {

            //set def
            $customers_id = $request->from;
            $warehouses_id = 1;
            $last_code = $this->get_last_code('transfer');
            $code = acc_code_generate($last_code, 8, 3);
            //set topup
            $data = array_merge($request->all(), ['code' => $code, 'total' => $total, 'type' => 'transfer', 'status' => 'approved', 'ledgers_id' => 0, 'customers_id' => $customers_id, 'payment_type' => 'point', 'memo' => $memo]);
            $topup = Topup::create($data);
            //set topup points

            $topup->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'memo' => $memo, 'customers_id' => $customers_id]);
            $topup->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'memo' => $memo, 'customers_id' => $request->to]);

            //push notif
            $user_os = Customer::find($request->to);
            $id_onesignal = $user_os->id_onesignal;
            $memo = 'Hallo ' . $user_os->name . ', Anda mendapatkan Transfer sejumlah ' . $topup->total;
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $request->to, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if ($id_onesignal != "") {
                if ($user_os->type == 'agent') {
                    OneSignal::sendNotificationToUser(
                        $memo,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                } else {
                    $this->onesignal_client->sendNotificationToUser(
                        $memo,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                }}

            return response()->json([
                'success' => true,
                'message' => 'Transfer Berhasil.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Poin Member Tidak Mencukupi.',
            ], 401);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            // 'phone' => 'required',
            // 'email' => 'required|email',
            // 'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        //get total
        $total = $request->input('amount');
        $points_id = 1;

        //set def
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        $last_code = $this->get_last_code('topup');
        $code = acc_code_generate($last_code, 8, 3);
        $member = Customer::find($customers_id);
        $memo = "Topup Poin " . $member->code . "-" . $member->name;
        //set topup
        $register = date("Y-m-d");
        $data = array_merge($request->all(), ['code' => $code, 'total' => $total, 'type' => 'topup', 'status' => 'pending', 'ledgers_id' => 0, 'customers_id' => $customers_id, 'payment_type' => 'cash', 'register' => $register, 'memo' => $memo, 'acc_pay' => $request->input('accounts_id')]);
        $topup = Topup::create($data);
        //set topup points

        $topup->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhold', 'memo' => $memo, 'customers_id' => $customers_id]);

        //push notif
        $user_os = Customer::find($customers_id);
        $id_onesignal = $user_os->id_onesignal;
        $memo = 'Hallo ' . $user_os->name . ', Permohonan Topup sejumlah ' . $topup->total . ' sedang menunggu persetujuan. Silahkan konfirmasi pembayaran Topup ke Admin secepatnya.';
        $register = date("Y-m-d");
        //store to logs_notif
        $data = ['register' => $register, 'customers_id' => $customers_id, 'memo' => $memo];
        $logs = LogNotif::create($data);
        //push notif
        if ($id_onesignal != "") {
            if ($user_os->type == 'agent') {
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            } else {
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }}

        return response()->json([
            'success' => true,
            'message' => 'Topup Berhasil.',
        ]);
    }

    public function balance($id)
    {
        //$agent = CustomerApi::find($id);
        $members = Customer::selectRaw("(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS balance_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '2' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '2' THEN order_points.amount ELSE 0 END)) AS balance_upgrade_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '3' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '3' THEN order_points.amount ELSE 0 END)) AS balance_saving_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '4' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '4' THEN order_points.amount ELSE 0 END)) AS fee_points")
            ->leftjoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->Where('customers.id', '=', $id)
            ->groupBy('customers.id')
            ->get();

        //Check if balance found or not.
        if (is_null($members)) {
            $message = 'Balance not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Balance retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $members,
            ]);
        }
    }

    public function historyFee($id, Request $request)
    {
        //debit
        $points_debit = OrderPoint::where('customers_id', '=', $id)
            ->where('type', '=', 'D')
            ->where('status', '=', 'onhand')
            ->where('memo', 'LIKE', '%komisi%')
            ->sum('amount');
        //credit
        $points_credit = OrderPoint::where('customers_id', '=', $id)
            ->where('type', '=', 'C')
            ->where('status', '=', 'onhand')
            ->where('memo', 'LIKE', '%komisi%')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        if (isset($request->page)) {
            $orderpoint = OrderPoint::with('orders')
                ->where('customers_id', $id)
                ->where('status', 'onhand')
                ->where('memo', 'LIKE', '%komisi%')
                ->orderBy('id', 'DESC')
                ->paginate(10, ['*'], 'page', $request->page);
        } else {
            //list
            $orderpoint = OrderPoint::with('orders')
                ->where('customers_id', $id)
                ->where('status', 'onhand')
                ->where('memo', 'LIKE', '%komisi%')
                ->orderBy('id', 'DESC')
                ->get();
        }

        //Check if history found or not.
        if (is_null($orderpoint)) {
            $message = 'History not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'History retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $orderpoint,
                'total' => $points_balance,
            ]);
        }
    }

    public function history($id, Request $request)
    {
        if (isset($request->page)) {
            $orderpoint = OrderPoint::with('orders')
                ->where('customers_id', $id)
                ->where('status', 'onhand')
                ->orderBy('id', 'DESC')
                ->paginate(10, ['*'], 'page', $request->page);
        } else {
            $orderpoint = OrderPoint::with('orders')
                ->where('customers_id', $id)
                ->where('status', 'onhand')
                ->orderBy('id', 'DESC')
                ->get();
        }

        //Check if history found or not.
        if (is_null($orderpoint)) {
            $message = 'History not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'History retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $orderpoint,
            ]);
        }
    }
    public function topupMAP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => '401',
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }

        //get total
        $total = $request->input('amount');
        $points_id = 1;

        //set def
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        $last_code = $this->get_last_code('topup');
        $code = acc_code_generate($last_code, 8, 3);
        $member = Customer::find($customers_id);
        $memo = "Topup Poin " . $member->code . "-" . $member->name;

        //set acc pay
        $accounts = Account::select('id')
            ->where('code', '10006')
            ->first();

        //set topup
        $register = date("Y-m-d");
        $data = array_merge($request->all(), ['code' => $code, 'total' => $total, 'type' => 'topup', 'status' => 'pending', 'ledgers_id' => 0, 'customers_id' => $customers_id, 'payment_type' => 'cash', 'register' => $register, 'memo' => $memo, 'acc_pay' => $accounts->id]);
        $topup = Topup::create($data);
        //set topup points

        $topup->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhold', 'memo' => $memo, 'customers_id' => $customers_id]);

        //push notif
        $user_os = Customer::find($customers_id);
        $id_onesignal = $user_os->id_onesignal;
        $memo = 'Hallo ' . $user_os->name . ', Permohonan Topup sejumlah ' . $topup->total . ' sedang menunggu persetujuan. Silahkan konfirmasi pembayaran Topup ke Admin secepatnya.';
        $register = date("Y-m-d");
        //store to logs_notif
        $data = ['register' => $register, 'customers_id' => $customers_id, 'memo' => $memo];
        $logs = LogNotif::create($data);

        if ($topup) {
            Config::$serverKey = config('midtrans.serverKey');
            Config::$isProduction = config('midtrans.isProduction');
            Config::$isSanitized = config('midtrans.isSanitized');
            Config::$is3ds = config('midtrans.is3ds');

            $midtrans_params = [
                'transaction_details' => [
                    'order_id' => "$request->customer_name-$topup->id",
                    'gross_amount' => $request->amount, //harus int kalau string ikuti => (int) variabel harga
                ],
                'customer_details' => [
                    'first_name' => $request->customer_name,
                    'email' => "$request->customer_email",
                ],
                'enabled_payments' => [
                    "bca_va", "gopay", "indomaret", "alfamart",
                ], //ini kita kana menggunakan gopay saja
                'vtweb' => [], //ini intinya kita akan menggunakan snap redirect atau bisa di sebut vtweb juga
            ];

            try {
                // Ambil halaman payment gateway midtrans
                $paymentUrl = Snap::createTransaction($midtrans_params)->redirect_url;

                //push notif
                if ($id_onesignal != "") {
                    if ($user_os->type == 'agent') {
                        OneSignal::sendNotificationToUser(
                            $memo,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );
                    } else {
                        $this->onesignal_client->sendNotificationToUser(
                            $memo,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );
                    }}

                // redirectke halaman nmidtrans
                return response()->json([
                    'message' => 'succes',
                    'urimap' => $paymentUrl,
                ]);

            } catch (\Exception $e) {
                //    return  $e->getMessage();
                return response()->json([
                    'message' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'code' => '401',
                'success' => false,
                'message' => $validator->errors(),
            ], 401);
        }
    }
}
