<?php

namespace App\Traits;

use App\Account;
use App\AccountsGroup;
use App\Activation;
use App\Asset;
use App\Capital;
use App\Customer;
use App\NetworkFee;
use App\Order;
use App\OrderPoint;
use App\Pairing;
use App\Payreceivable;
use App\PayreceivableTrs;
use App\Production;
use Illuminate\Database\QueryException;
use App\Accountlock;

trait TraitModel
{
    private $fee_pairing_amount = 5;

    public function account_lock_get($code)
    {
        $accountlock = Accountlock::Where('code', '=', $code)
            ->first();
        if (!empty($accountlock)) {
            return $accountlock->account_id;
        } else {
            return 0;
        }
    }
    
    public function points_balance_selected($customer_id, $point_id)
    {
        $members = Customer::selectRaw("(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' THEN order_points.amount ELSE 0 END)) AS balance_point")
            ->leftjoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->Where('customers.id', '=', $customer_id)
            ->Where('order_points.points_id', '=', $point_id)
            ->groupBy('customers.id')
            ->first();
        if (!empty($members)) {
            return $members->balance_point;
        } else {
            return 0;
        }
    }

    public function points_balance($customer_id)
    {
        $members = Customer::selectRaw("(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS balance_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '2' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '2' THEN order_points.amount ELSE 0 END)) AS balance_upgrade_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '3' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '3' THEN order_points.amount ELSE 0 END)) AS balance_saving_points, (SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '4' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '4' THEN order_points.amount ELSE 0 END)) AS fee_points")
            ->leftjoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->Where('customers.id', '=', $customer_id)
            ->groupBy('customers.id')
            ->first();
        return $members;
    }

    public function downline_tree($ref_id, $down_arr)
    {
        $bv_activation_amount_total = 0;
        $customer = Customer::select('id', 'code', 'name')
            ->where('id', $ref_id)
            ->where('status', '=', 'active')
            ->first();
        $array_adj = array('id' => $customer->id, 'name' => $customer->name, 'code' => $customer->code);
        array_push($down_arr, $array_adj);
        $downref_list = Customer::select('id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->orderBy('activation_at', 'asc')
            ->get();
        foreach ($downref_list as $downline) {
            $down_arr = $this->downline_tree($downline->id, $down_arr);
        }
        return $down_arr;
    }

    public function get_ref_plat($id)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if ($ref_status == 'active' && $referal->activation_type_id == 4) {
                return $ref_id;
            } else {
                return $this->get_ref_plat($ref_id);
            }
        } else {
            return $id;
        }
    }

    public function get_fee_pairing_amount($id_order)
    {
        $fee_pairing_amount = OrderPoint::where('orders_id', '=', $id_order)
            ->where('status', '=', 'onhold')
            ->sum('amount');
        return $fee_pairing_amount;
    }

    public function pairing($id_order, $ref1_id, $test = 0)
    {
        $up_arr = array();
        $pairing_fee_total = $this->pairing_process($id_order, $ref1_id, $test);
        $up_list = $this->ref_up_list($ref1_id, $up_arr);
        foreach ($up_list as $upline) {
            $pairing_fee_total += $this->pairing_process($id_order, $upline, $test);
        }
        return $this->get_fee_pairing_amount($id_order);
    }

    public function ref_up_list($ref1_id, $up_arr)
    {
        $customer = Customer::find($ref1_id);
        $ref2_id = $customer->ref_id;
        if ($ref2_id > 0) {
            $referal = Customer::find($ref2_id);
            $ref2_status = $referal->status;
            if ($ref2_status == 'active') {
                array_push($up_arr, $ref2_id);
                return $this->ref_up_list($ref2_id, $up_arr);
            }
        }
        return $up_arr;
    }

    public function recursive_test($var)
    {
        $var_add = 5;
        if ($var > 0) {
            $var_nxt = $var - 1;
            return $var_add + $this->recursive_test($var_nxt);
        }
    }

    public function pairing_process($id_order, $ref1_id, $test = 0)
    {
        $ref1_row = Customer::find($ref1_id);
        //init
        $fee_out = 0;
        $ref2_row = Customer::find($ref1_row->ref_id);
        if (!empty($ref2_row) && $ref2_row->ref_id != 0 && $ref2_row->id > 0) {
            //get network fee pairing -> ref1 activation type
            $nf_rf2_pairing_row = NetworkFee::select('*')
                ->Where('type', '=', 'pairing')
                ->Where('activation_type_id', '=', $ref2_row->activation_type_id)
                ->get();
            $deep_level = $nf_rf2_pairing_row[0]->deep_level;
            if ($ref2_row->id > 0 && (!empty($ref2_row)) && ($ref2_row->ref_id > 0)) {
                // $dwn_arr = array();
                $dwn_arr = $this->downref_list($ref2_row->id, $deep_level);
                foreach ($dwn_arr as $downline) {
                    //get9 pairing_lev
                    //$fee_out .= "-".$id_order."-".$downline->id."-".$deep_level;
                    $fee_out += (float) $this->pairing_lev($id_order, $downline->id, $deep_level, $test);
                    $deep_level--;
                }}
        }
        //echo $fee_out."</br>";
        return $fee_out;
    }

    public function pairing_lev($id_order, $ref1_id, $deep_level, $test = 0)
    {
        //init
        $test_out = array();
        $fee_out = 0;
        if ($test == 0) {
            $order = Order::find($id_order);
        }
        $points_id = 1;
        $points_upg_id = 2;
        $points_fee_id = 4;
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->get();
        //get ref2 activation type
        $ref1_row = Customer::find($ref1_id);
        $ref1_activation_row = Activation::find($ref1_row->activation_type_id);
        if (!empty($ref1_row) && $ref1_row->ref_id > 0) {
            $memo = $ref1_row->code . " - " . $ref1_row->name;
            $deep_lev = $deep_level;
            //get pairing ref1 balance
            $pairing_ref1_balance = $this->pairing_ref1_balance($ref1_id, $deep_lev);
            //get network fee pairing -> ref1 activation type
            $nf_rf1_pairing_row = NetworkFee::select('*')
                ->Where('type', '=', 'pairing')
                ->Where('activation_type_id', '=', $ref1_row->activation_type_id)
                ->get();
            //get min bv pairing -> ref1 activation type
            $min_bv_pairing = $nf_rf1_pairing_row[0]->bv_min_pairing * $bvpo_row[0]->amount;
            //if pairing ref1 balance >= min bv pairing -> process
            if ($pairing_ref1_balance >= $min_bv_pairing) {
                //set fee to ref1
                $ref1_amount = 0;
                $ref2_amount = 0;
                if ($test == 0 && $ref1_row->status == 'active') {
                    $ref1_fee_pairing = (($nf_rf1_pairing_row[0]->sbv) / 100) * $pairing_ref1_balance;
                    $ref1_amount = $ref1_fee_pairing;
                    //hitung total bv_amount hari ini yang sudah di pairing di tbl pairing {bvarp_paired}
                    $reg_today = date('Y-m-d');
                    $daily_amount_paired = Pairing::where('ref1_id', '=', $ref1_id)
                        ->whereDate('register', '=', $reg_today)
                        ->sum('ref1_amount');
                    if ($daily_amount_paired <= $nf_rf1_pairing_row[0]->fee_day_max) {
                        //$fee_out += $ref1_fee_pairing;
                        //$order->points()->attach($points_fee_id, ['amount' => $ref1_fee_pairing, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $ref1_row->id]);
                    } else {
                        //$ref1_amount = 0;
                    }
                    //set fee to ref2
                    $ref2_id = 0;
                    $ref2_row = Customer::find($ref1_row->ref_id);
                    $ref2_activation_row = Activation::find($ref2_row->activation_type_id);
                    $ref2_fee_pairing = 0;
                    if (!empty($ref2_row) && $ref2_row->ref_id > 0 && $test == 0 && $ref2_row->status == 'active' && $ref2_activation_row->type != "user") {
                        //get network fee pairing -> ref1 activation type
                        $nf_rf2_pairing_row = NetworkFee::select('*')
                            ->Where('type', '=', 'pairing')
                            ->Where('activation_type_id', '=', $ref2_row->activation_type_id)
                            ->get();
                        $ref2_id = $ref2_row->id;
                        $ref2_fee_pairing = (($nf_rf2_pairing_row[0]->sbv) / 100) * $pairing_ref1_balance;
                        $ref2_amount = $ref2_fee_pairing;
                        //hitung total bv_amount hari ini yang sudah di pairing di tbl pairing {bvarp_paired}
                        $daily_amount_paired2 = Pairing::where('ref2_id', '=', $ref2_id)
                            ->whereDate('register', '=', $reg_today)
                            ->sum('ref2_amount');
                        if ($daily_amount_paired2 <= $nf_rf2_pairing_row[0]->fee_day_max) {
                            $fee_out += (float) $ref2_fee_pairing;
                            $this->fee_pairing_amount += (float) $ref2_fee_pairing;
                            //echo $fee_out."1. </br>";
                            $order->points()->attach($points_fee_id, ['amount' => $ref2_fee_pairing, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $ref2_row->id]);
                        } else {
                            //$ref2_amount = 0;
                        }
                    }
                    //set matching
                    //find ref3
                    $ref3_row = Customer::find($ref2_row->ref_id);
                    $ref3_activation_row = Activation::find($ref3_row->activation_type_id);
                    if (!empty($ref3_row) && $ref3_row->ref_id > 0 && !$test && $ref3_row->status == 'active' && $ref3_activation_row->type != "user") {
                        //get network fee pairing -> ref3 activation type
                        $nf_rf3_pairing_row = NetworkFee::select('*')
                            ->Where('type', '=', 'matching')
                            ->Where('activation_type_id', '=', $ref3_row->activation_type_id)
                            ->get();
                        $ref3_fee_pairing = (($nf_rf3_pairing_row[0]->amount) / 100) * $ref2_fee_pairing;
                        //set fee to ref3
                        $fee_out += (float) $ref3_fee_pairing;
                        $this->fee_pairing_amount += (float) $ref3_fee_pairing;
                        //echo $fee_out."2. </br>";
                        $order->points()->attach($points_fee_id, ['amount' => $ref3_fee_pairing, 'type' => 'D', 'status' => 'onhold', 'memo' => 'Poin Komisi (Matching) dari group ' . $memo, 'customers_id' => $ref3_row->id]);
                    }
                    //insert into tbl pairings
                    $register = date('Y-m-d H:i:s');
                    $data = ['register' => $register, 'ref1_id' => $ref1_row->id, 'ref2_id' => $ref2_id, 'bv_amount' => $pairing_ref1_balance, 'order_id' => $id_order, 'ref2_amount' => $ref2_amount, 'ref1_amount' => $ref1_amount];
                    $logs = Pairing::create($data);
                }
            }}
        //echo $fee_out."3. </br>";
        return $fee_out;
    }

    public function pairing_ref1_balance($ref1_id, $deep_lev)
    {
        $arr_out = array();
        $bv_pairing = 0;
        //hitung total bv_amount yang sudah di pairing di tbl pairing {bvarp_paired}
        $bvarp_paired = Pairing::where('ref1_id', '=', $ref1_id)
            ->sum('bv_amount');
        //hitung selisih bv_amount yang sudah di pairing dengan total bv_amount activasi {bvarp_paired_balance}
        $bvarp = $this->ref1_omzet($ref1_id, $deep_lev);
        $bvarp_paired_balance = $bvarp - $bvarp_paired;
        //hitung selisih bv_amount yang sudah di pairing dengan total bv_amount group {bvarp_g_paired_balance}
        $bvarp_g = $this->group_omzet($ref1_id, $deep_lev);
        $bvarp_g_paired_balance = $bvarp_g - $bvarp_paired;
        //compare {bvarp_paired_balance} dengan {bvarp_g_paired_balance},
        if ($bvarp_g_paired_balance >= $bvarp_paired_balance) {
            $bv_pairing = $bvarp_paired_balance;
        } else {
            $bv_pairing = $bvarp_g_paired_balance;
        }
        $arr_out['bvarp_paired'] = $bvarp_paired;
        $arr_out['bvarp'] = $bvarp;
        $arr_out['bvarp_g'] = $bvarp_g;
        $arr_out['bv_pairing'] = $bv_pairing;
        //return $arr_out;
        return $bv_pairing;
    }

    //omzet
    public function ref1_omzet($ref1_id, $deep_lev, $inc_ref1 = 0)
    {
        $bv_activation_amount_total = 0;
        $ref1_row = Customer::find($ref1_id);
        $activation_row = Activation::find($ref1_row->activation_type_id);
        if ($inc_ref1 == 1) {
            //get ref1 bv activation
            $balance = Order::where('customers_activation_id', '=', $ref1_id)
                ->where('type', '=', 'activation_member')
                ->where('status', '!=', 'closed')
                ->sum('bv_activation_amount');
            $bv_activation_amount_total = $balance;
        }
        $downref_list = $this->downref_list($ref1_id, $deep_lev);
        //loop downref_list
        foreach ($downref_list as $downline) {
            $downline_row = Customer::find($downline->id);
            $downline_activation_row = Activation::find($downline_row->activation_type_id);
            //get bv_activation_amount
            $balance = Order::where('customers_activation_id', '=', $downline->id)
                ->where('type', '=', 'activation_member')
                ->where('status', '!=', 'closed')
                ->where('created_at', '>=', $ref1_row->activation_at)
                ->sum('bv_activation_amount');
            $downref_omzet = $this->downref_omzet($downline->id, $ref1_row->activation_at, 1);
            // echo "</br>". $downline_row->code . "-" . $downline_row->name . "-" . $balance . "-" . $downref_omzet;
            $bv_activation_amount_total += $balance + $downref_omzet;
        }
        return $bv_activation_amount_total;
    }

    //omzet group
    public function group_omzet($parent_id, $deep_lev)
    {
        $bv_activation_amount_total = 0;
        $dwn_arr = array();
        $parent_row = Customer::find($parent_id);
        $ref_id = $parent_row->ref_id;
        $downref_lev_num = $this->downref_lev_num($ref_id, $ref_id, $parent_id, 0);
        $downline_lev = $deep_lev-$downref_lev_num;
        $dwn_arr = $this->downline_list($ref_id, $parent_id, $dwn_arr, 1, 0, $downline_lev);
        foreach ($dwn_arr as $downline) {
            //get bv_activation_amount
            $balance = $this->ref1_omzet($downline, $deep_lev);
            $bv_activation_amount_total += $balance;
        }
        return $bv_activation_amount_total;

    }

    public function downref_omzet($ref_id, $activation_at, $lev)
    {
        $bv_activation_amount_total = 0;
        $downref_list = Customer::select('id', 'code', 'name', 'activation_type_id', 'activation_at')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->orderBy('activation_at', 'asc')
            ->with('activations')
            ->get();
        foreach ($downref_list as $downline) {
            $balance = Order::where('customers_activation_id', '=', $downline->id)
                ->where('type', '=', 'activation_member')
                ->where('status', '!=', 'closed')
                ->where('created_at', '>=', $activation_at)
                ->sum('bv_activation_amount');
            $bv_activation_amount_total += $balance;
            $lev_nxt = $lev + 1;
            $bv_activation_amount_total += $this->downref_omzet($downline->id, $activation_at, $lev_nxt);
        }
        return $bv_activation_amount_total;
    }

    public function downref_omzet_view_test($member_code, $activation_at, $lev)
    {
        $bv_activation_amount_total = 0;
        $member_row = Customer::where('code', $member_code)->first();
        $ref_id = $member_row->id;
        $downref_list = Customer::select('id', 'code', 'name', 'activation_type_id', 'activation_at')
            ->where('ref_id', $ref_id)
            ->get();
        foreach ($downref_list as $downline) {
            echo "</br>" . $this->space_generate($lev) . $downline->code . "-" . $downline->name . "-" . $downline->activations->name;
            $lev_nxt = $lev + 1;
            $bv_activation_amount_total += $this->downref_omzet_view_test($downline->code, $activation_at, $lev_nxt);
        }
        return $bv_activation_amount_total;
    }

    public function downref_omzet_view($member_code, $activation_at, $lev)
    {
        $bv_activation_amount_total = 0;
        $member_row = Customer::where('code', $member_code)->first();
        $ref_id = $member_row->id;
        $downref_list = Customer::select('id', 'code', 'name', 'activation_type_id', 'activation_at')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->orderBy('activation_at', 'asc')
            ->with('activations')
            ->get();
        foreach ($downref_list as $downline) {
            $balance = Order::where('customers_activation_id', '=', $downline->id)
                ->where('type', '=', 'activation_member')
                ->where('status', '!=', 'closed')
                ->where('created_at', '>=', $activation_at)
                ->sum('bv_activation_amount');
            $bv_activation_amount_total += $balance;
            echo "</br>" . $this->space_generate($lev) . $downline->code . "-" . $downline->name . "-" . $downline->activations->name . "-" . $balance . "-" . $downline->activation_at;
            $lev_nxt = $lev + 1;
            $bv_activation_amount_total += $this->downref_omzet_view($downline->code, $activation_at, $lev_nxt);
        }
        return $bv_activation_amount_total;
    }

    public function space_generate($lev)
    {
        $space_str = "";
        for ($i = 0; $i < $lev; $i++) {

            $space_str .= '-';
        }
        return $space_str;
    }

    //semua downline yang di refrensikan langsung olehnya
    public function downref_list($ref_id, $deep_lev)
    {
        $downref_list = Customer::select('id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->orderBy('activation_at', 'asc')
            ->skip(0)
            ->take($deep_lev)
            ->get();
        return $downref_list;
    }

    //semua downline semu yang di refrensikan parentnya (for pairing group)
    public function downline_list($ref_id, $parent_id, $dwn_arr, $lev_max, $id_exc, $deep_lev = 5)
    {
        $downline_obj = Customer::where('parent_id', $parent_id)
            ->where('ref_id', '=', $ref_id)
            ->where('status', '=', 'active')
            ->first();
        if (!empty($downline_obj)) {
            $downline_id = $downline_obj->id;
            if ($lev_max <= $deep_lev) {
                $downline_row = Customer::find($downline_id);
                $downline_activation_row = Activation::find($downline_row->activation_type_id);
                if (($downline_id != $id_exc)) {
                    $lev_max++;
                    array_push($dwn_arr, $downline_id);
                }
                return $this->downline_list($ref_id, $downline_id, $dwn_arr, $lev_max, $id_exc, $deep_lev);
            } else {
                return $dwn_arr;
            }} else {
            return $dwn_arr;
        }
    }

    public function downref_lev_num($ref_id, $parent_id, $downline_id, $lev_num)
    {
        $downline_obj = Customer::select('id')->where('parent_id', $parent_id)
            ->where('ref_id', '=', $ref_id)
            ->where('status', '=', 'active')
            ->first();
        if (!empty($downline_obj)) {
            $lev_num++;
            if ($downline_obj->id != $downline_id) {
                return $this->downref_lev_num($ref_id, $downline_obj->id, $downline_id, $lev_num);
            } else {
                return $lev_num;
            }
        } else {
            return $lev_num;
        }
    }

    public function set_parent($ref_id)
    {
        $member_row = Customer::find($ref_id);
        $member_last_row = Customer::where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->orderBy("activation_at", "desc")
            ->first();
        if (empty($member_last_row) || $member_row->ref_id == 0) {
            $id = $ref_id;
        } else {
            $id = $member_last_row->id;
        }
        return $id;
    }

    public function get_level_num($id, $deep_level, $lev_num = 1)
    {
        $customer = Customer::find($id);
        $parent = Customer::find($customer->parent_id);
        if (!empty($parent) && $lev_num < $deep_level && $parent->status == 'active' && ($customer->parent_id != $customer->ref_id)) {
            $lev_num++;
            return $this->get_level_num($parent->id, $deep_level, $lev_num);
        } else {
            return $lev_num;
        }
    }

    public function get_last_code($type)
    {
        if ($type == "convert") {
            $account = Order::where('type', 'point_conversion')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('CNV', 8);
            }
        }

        if ($type == "receivable") {
            $account = Payreceivable::where('type', 'receivable')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('REC', 8);
            }
        }
        if ($type == "receivable_trsc") {
            $account = PayreceivableTrs::selectRaw("payreceivables_trs.*")
                ->leftJoin('payreceivables', 'payreceivables_trs.payreceivable_id', '=', 'payreceivables.id')
                ->where('payreceivables_trs.type', '=', 'C')
                ->where('payreceivables.type', '=', 'receivable')
                ->orderBy('payreceivables_trs.id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('RTC', 8);
            }
        }
        if ($type == "receivable_trs") {
            $account = PayreceivableTrs::selectRaw("payreceivables_trs.*")
                ->leftJoin('payreceivables', 'payreceivables_trs.payreceivable_id', '=', 'payreceivables.id')
                ->where('payreceivables_trs.type', '=', 'D')
                ->where('payreceivables.type', '=', 'receivable')
                ->orderBy('payreceivables_trs.id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('RTD', 8);
            }
        }
        if ($type == "payable") {
            $account = Payreceivable::where('type', 'payable')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('PAY', 8);
            }
        }
        if ($type == "payable_trsc") {
            $account = PayreceivableTrs::selectRaw("payreceivables_trs.*")
                ->leftJoin('payreceivables', 'payreceivables_trs.payreceivable_id', '=', 'payreceivables.id')
                ->where('payreceivables_trs.type', '=', 'C')
                ->where('payreceivables.type', '=', 'payable')
                ->orderBy('payreceivables_trs.id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('PTC', 8);
            }
        }
        if ($type == "payable_trs") {
            $account = PayreceivableTrs::selectRaw("payreceivables_trs.*")
                ->leftJoin('payreceivables', 'payreceivables_trs.payreceivable_id', '=', 'payreceivables.id')
                ->where('payreceivables_trs.type', '=', 'D')
                ->where('payreceivables.type', '=', 'payable')
                ->orderBy('payreceivables_trs.id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('PTD', 8);
            }
        }
        if ($type == "capitalist") {
            $account = Customer::where('type', 'capitalist')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('KOM', 8);
            }
        }
        if ($type == "customer") {
            $account = Customer::where('type', 'customer')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('CUS', 8);
            }
        }
        if ($type == "capital") {
            $account = Capital::orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('CAP', 8);
            }
        }

        if ($type == "asset") {
            $account = Asset::orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('INV', 8);
            }
        }

        if ($type == "sale_retur") {
            $account = Order::where('type', 'sale_retur')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('SRT', 8);
            }
        }

        if ($type == "topup") {
            $account = Order::where('type', 'topup')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('TOP', 8);
            }
        }

        if ($type == "transfer") {
            $account = Order::where('type', 'transfer')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('TRF', 8);
            }
        }

        if ($type == "order") {
            $account = Order::where('type', 'sale')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('ORD', 8);
            }
        }

        if ($type == "order-agent") {
            $account = Order::where('type', 'agent_sale')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('OAG', 8);
            }
        }

        if ($type == "member") {
            $account = Customer::where('type', 'member')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('MBR', 8);
            }
        }

        if ($type == "agent") {
            $account = Customer::where('type', 'agent')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('AGN', 8);
            }
        }

        if ($type == "withdraw") {
            $account = Order::where('type', 'withdraw')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('WDW', 8);
            }
        }

        return $code;
    }

    public function acc_get_last_code($accounts_group_id)
    {
        $account = Account::where('accounts_group_id', $accounts_group_id)
            ->orderBy('code', 'desc')
            ->first();
        if ($account) {
            $code = $account->code;
        } else {
            $accounts_group = AccountsGroup::select('code')->where('id', $accounts_group_id)->first();
            $accounts_group_code = $accounts_group->code;
            $code = acc_codedef_generate($accounts_group_code, 5);
        }

        return $code;
    }

    public function mbr_get_last_code()
    {
        $account = Customer::where('type', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('MBR', 8);
        }

        return $code;
    }

    public function cst_get_last_code()
    {
        $account = Customer::where('type', '!=', 'member')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('CST', 8);
        }

        return $code;
    }

    public function prd_get_last_code()
    {
        $account = Production::where('type', 'production')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('PRD', 8);
        }

        return $code;
    }

    public function ord_get_last_code()
    {
        $account = Production::where('type', 'sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('ORD', 8);
        }

        return $code;
    }

    public function oag_get_last_code()
    {
        $account = Production::where('type', 'agent_sale')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('OAG', 8);
        }

        return $code;
    }

    public function top_get_last_code()
    {
        $account = Production::where('type', 'topup')
            ->orderBy('id', 'desc')
            ->first();
        if ($account && (strlen($account->code) == 8)) {
            $code = $account->code;
        } else {
            $code = acc_codedef_generate('TOP', 8);
        }

        return $code;
    }

    public function get_ref_exc($id, $ref_arr, $lev_max, $id_exc, $deep_lev = 9)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= $deep_lev) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if (($ref_id != $id_exc) && ($ref_status == 'active')) {
                array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref_exc($ref_id, $ref_arr, $lev_max, $id_exc, $deep_lev);
        } else {
            return $ref_arr;
        }
    }

    public function get_ref($id, $ref_arr, $lev_max)
    {
        $customer = Customer::find($id);
        $ref_id = $customer->ref_id;
        if ($ref_id > 0 && $lev_max <= 9) {
            $referal = Customer::find($ref_id);
            $ref_status = $referal->status;
            if ($ref_status == 'active') {
                array_push($ref_arr, $ref_id);
            }
            $lev_max++;
            return $this->get_ref($ref_id, $ref_arr, $lev_max);
        } else {
            return $ref_arr;
        }
    }
}
