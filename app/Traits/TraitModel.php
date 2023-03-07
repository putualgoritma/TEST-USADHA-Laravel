<?php

namespace App\Traits;

use App\Account;
use App\Accountlock;
use App\AccountsGroup;
use App\Activation;
use App\ActivationType;
use App\Asset;
use App\BVPairingQueue;
use App\Capital;
use App\Career;
use App\Careertype;
use App\Customer;
use App\Ledger;
use App\NetworkFee;
use App\Order;
use App\OrderPoint;
use App\Pairing;
use App\Payreceivable;
use App\PayreceivableTrs;
use App\Production;
use DB;
use Illuminate\Database\QueryException;

trait TraitModel
{
    private $fee_pairing_amount = 5;
    private $id_order_priv = 0;

    public function career_type($customer_id)
    {
        //init
        $year = date('Y');
        $month = date('m');
        $auto_maintain_bv = 0;
        //get career def
        $career_def = Career::select("*")
            ->where('customer_id', $customer_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($career_def) {
            //get careertypes
            $careertype_def = Careertype::select("*")
                ->where('id', $career_def->careertype_id)
                ->first();
            //BVPO
            $bvpo_row = NetworkFee::select('*')
                ->Where('code', '=', 'BVPO')
                ->first();
            $bv_automaintain_amount = $careertype_def->auto_maintain_bv * $bvpo_row->amount;
            //get current auto maintain
            $order = Order::select('id')
                ->where('customers_id', $customer_id)
                ->where('status', '=', 'approved')
                ->where('status_delivery', '=', 'received')
                ->where('bv_automaintain_amount', '>=', $bv_automaintain_amount)
                ->whereDate('created_at', '=', date('Y-m-d'))
                // ->whereYear('created_at', '=', $year)
                // ->whereMonth('created_at', '=', $month)
                ->first();
                if (!$order) {
                    $auto_maintain_bv = $bv_automaintain_amount;
                }
        }
        return $auto_maintain_bv;
    }

    public function fee_auto_maintain($order_id, $ref_id, $bv_total, $bvcv_amount, $ref1_fee_point_sale, $ref1_fee_point_upgrade, $ref2_fee_point_sale, $ref2_fee_point_upgrade, $ref1_flush_out, $ledger_id, $cba2, $cbmart, $points_fee_id, $points_upg_id, $ref2_id, $memo, $member_get_flush_out, $package_type, $ref_fee_lev, $customer_id)
    {
        //PAIRING
        if ($package_type == 0) {
            $fee_auto_maintain = $this->auto_maintain($order_id, $customer_id, $bv_total, $points_fee_id, 0, 0);
        } else {
            $fee_auto_maintain = 0;
        }

        //get netfee_amount
        $bvcv = (($bvcv_amount) / 100) * $bv_total;
        $bv_nett = $bv_total - $bvcv;
        if ($package_type == 0) {
            $res_netfee_amount = $ref1_fee_point_sale + $ref1_fee_point_upgrade + $ref2_fee_point_sale + $ref2_fee_point_upgrade + $fee_auto_maintain + $ref1_flush_out;
        } else {
            $res_netfee_amount = $ref_fee_lev;
        }

        //set account
        $acc_points = $this->account_lock_get('acc_points'); //'67'
        $acc_res_netfee = $this->account_lock_get('acc_res_netfee'); //'70'
        $acc_res_cashback = $this->account_lock_get('acc_res_cashback');
        $points_amount = $res_netfee_amount + $cba2 + $cbmart;
        $accounts = array($acc_points, $acc_res_netfee, $acc_res_cashback);
        $amounts = array($points_amount, $res_netfee_amount, $cba2 + $cbmart);
        $types = array('C', 'D', 'D');
        //order & ledger
        $order = Order::find($order_id);
        $ledger = Ledger::find($ledger_id);
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        //set ref1 fee
        //point sale
        if ($ref1_fee_point_sale > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref1_fee_point_sale, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref_id]);
        }
        //point upgrade
        if ($ref1_fee_point_upgrade > 0) {
            $order->points()->attach($points_upg_id, ['amount' => $ref1_fee_point_upgrade, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin (Upgrade) Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref_id]);
        }

        //set ref2 fee
        //point sale
        if ($ref2_fee_point_sale > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref2_fee_point_sale, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref2_id]);
        }
        //point upgrade
        if ($ref2_fee_point_upgrade > 0) {
            $order->points()->attach($points_upg_id, ['amount' => $ref2_fee_point_upgrade, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin (Upgrade) Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref2_id]);
        }
        //point flush out
        if ($ref1_flush_out > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref1_flush_out, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Flush Out) dari ' . $memo, 'customers_id' => $member_get_flush_out]);
        }
    }

    public function auto_maintain($order_id, $customer_id, $bv_amount, $points_fee_id, $total, $level = 0)
    {
        //init
        $year = date('Y');
        $month = date('m');
        //get order def detail
        $order_def = Order::find($order_id);
        $customer = Customer::find($order_def->customers_id);
        $memo = $customer->code . " - " . $customer->name;
        //get career def
        $career_def = Career::select("*")
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->first();
        //get careertypes
        $careertype_def = Careertype::select("*")
            ->where('id', $career_def->careertype_id)
            ->first();
        //check level
        if ($level < 10) {
            //get referal ref_bin_id
            $referral = Customer::select('*')
                ->where('id', '=', $customer_id)
                ->first();
            //print_r($referral);
            //echo 'total: '.$total . '<br>';
            //if ref_bin_id > 1
            if ($referral->ref_bin_id > 1) {
                //echo $referral->ref_bin_id . '<br>';
                //get jenjang karir
                $career = Career::select("*")
                    ->where('customer_id', $referral->ref_bin_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                //if jenjang karir get current auto maintain
                if ($career) {
                    //echo 'career: '.$career->careertype_id . '<br>';
                    //get current auto maintain
                    $order = Order::select('id')
                        ->where('customers_id', $referral->ref_bin_id)
                        ->where('status', '=', 'approved')
                        ->where('status_delivery', '=', 'received')
                        ->where('bv_automaintain_amount', '>', 0)
                        ->whereDate('created_at', '=', date('Y-m-d'))
                        // ->whereYear('created_at', '=', $year)
                        // ->whereMonth('created_at', '=', $month)
                        ->first();
                    //if qualified
                    if ($order) {
                        //set fee
                        $fee_am = ($careertype_def->fee_am / 100) * $bv_amount;
                        //echo 'amount' . '-' . $fee_am . '::' . 'type' . '-' . 'D' . '::' . 'status' . '-' . 'onhand' . '::' . 'memo' . '-' . 'Poin Auto Maintain dari Transaksi Member ' . $memo . '::' . 'customers_id' . '-' . $referral->ref_bin_id. '<br>';
                        $order_def->points()->attach($points_fee_id, ['amount' => $fee_am, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Auto Maintain dari Transaksi Member ' . $memo, 'customers_id' => $referral->ref_bin_id]);
                        //incr level
                        $level++;
                        $total += $fee_am;
                    }
                }
                //if level < 10 recursive
                if ($level < 10) {
                    return $this->auto_maintain($order_id, $referral->ref_bin_id, $bv_amount, $points_fee_id, $total, $level);
                } else {
                    return $total;
                }
            } else {
                return $total;
            }
        } else {
            return $total;
        }
    }

    public function test_pairing_bin($order_id, $customer_id, $bv_amount_inc, $points_fee_id)
    {
        //init
        $fee_out = 0;
        //get order detail
        $order = Order::find($order_id);
        //get member detail
        $member = Customer::select('activation_type_id', 'slot_x', 'slot_y')
            ->where('id', $customer_id)
            ->first();
        $lev = $member->slot_x - 0;
        $slot_prev_x = $member->slot_x;
        $slot_prev_y = $member->slot_y;
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->first();
        //get max level
        $member_pairing_row = NetworkFee::select('*')
            ->Where('type', '=', 'pairing')
            ->Where('activation_type_id', '=', $member->activation_type_id)
            ->first();
        $pairing_lev_max = $member_pairing_row->deep_level;
        $lev_count = 1;
        //loop upline
        for ($i = 0; $i < $lev; $i++) {
            $slot_x = $slot_prev_x - 1;
            $slot_y = ceil($slot_prev_y / 2);
            //echo $slot_x." - ".$slot_y;
            //get upline detail
            $upline = Customer::select('*')
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->where('status', 'active')
                ->where('activation_type_id', '>', 1)
                ->first();
            //print_r($upline);

            if ($upline) {
                //get upline queue
                $bv_queue = $this->get_bv_queue($upline->id);
                $bv_pairing_r = $bv_queue['r'];
                $bv_pairing_l = $bv_queue['l'];

                //get prev position
                if ($slot_prev_y % 2 == 0) {
                    $bv_pairing_r += $bv_amount_inc;
                    $position = 'R';
                } else {
                    $bv_pairing_l += $bv_amount_inc;
                    $position = 'L';
                }
                $bv_pairing = $bv_pairing_r;
                if ($bv_pairing_l < $bv_pairing_r) {
                    $bv_pairing = $bv_pairing_l;
                }
                $bv_pairing = ($bv_pairing - $bv_queue['c']);
                //compare min pairing
                //get network fee pairing -> upline activation type
                $nf_upline_pairing_row = NetworkFee::select('*')
                    ->Where('type', '=', 'pairing')
                    ->Where('activation_type_id', '=', $upline->activation_type_id)
                    ->first();
                //memo
                $memo = $upline->code . " - " . $upline->name;
                //get min bv pairing -> upline activation type
                $min_bv_pairing = $nf_upline_pairing_row->bv_min_pairing * $bvpo_row->amount;
                echo $bvpo_row->amount . '-' . $bv_amount_inc . '-' . $upline->code . '-' . $bv_pairing . '-' . $min_bv_pairing . '-' . $upline->status . '-' . $upline->type;
                // echo '</br>';
                //check if reach lev max
                if ($lev_count <= $pairing_lev_max) {
                    $pairing_sbv = $member_pairing_row->sbv;
                } else {
                    $pairing_sbv = $member_pairing_row->sbv2;
                }
                //mod bv pairing
                $bv_pairing_index = floor($bv_pairing / $min_bv_pairing);
                $bv_pairing = $min_bv_pairing * $bv_pairing_index;
                if (($bv_pairing >= $min_bv_pairing) && $pairing_sbv > 0) {
                    if ($upline->status == 'active' && $upline->type != "user") {
                        $upline_fee_pairing = (($pairing_sbv) / 100) * $bv_pairing;
                        $upline_amount = $upline_fee_pairing;
                        //hitung total bv_amount hari ini yang sudah di pairing di tbl pairing {bvarp_paired}
                        $reg_today = date('Y-m-d');
                        $daily_amount = $this->get_bv_daily_queue($upline->id, $reg_today);
                        $daily_amount_paired = $daily_amount + $upline_fee_pairing;
                        if ($daily_amount_paired <= $nf_upline_pairing_row->fee_day_max) {
                            $fee_out += (float) $upline_fee_pairing;
                            $this->fee_pairing_amount += (float) $upline_fee_pairing;
                            //$order->points()->attach($points_fee_id, ['amount' => $upline_fee_pairing, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $upline->id]);
                        } else {
                            $upline_fee_pairing = $nf_upline_pairing_row->fee_day_max - $daily_amount;
                            if ($upline_fee_pairing > 0) {
                                $fee_out += (float) $upline_fee_pairing;
                                $this->fee_pairing_amount += (float) $upline_fee_pairing;
                                //$order->points()->attach($points_fee_id, ['amount' => $upline_fee_pairing, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $upline->id]);
                            }
                        }
                        //insert into tbl queue C
                        $data = ['order_id' => $order_id, 'customer_id' => $upline->id, 'bv_amount' => $bv_pairing, 'position' => 'N', 'status' => 'active', 'type' => 'C', 'pairing_amount' => $upline_fee_pairing];
                        //$queue_crt = BVPairingQueue::create($data);
                    }
                }
                //insert into tbl queue D
                $data = ['order_id' => $order_id, 'customer_id' => $upline->id, 'bv_amount' => $bv_amount_inc, 'position' => $position, 'status' => 'active', 'type' => 'D'];
                //$queue_crt = BVPairingQueue::create($data);
            }
            $lev_count++;
            //set prev
            $slot_prev_x = $slot_x;
            $slot_prev_y = $slot_y;
        }
        return $fee_out;
    }

    public function bugPairing($customer_id, $bv_amount_inc, $position)
    {
        $upline = Customer::select('*')
            ->where('id', $customer_id)
            ->first();

        if ($upline) {
            //BVPO
            $bvpo_row = NetworkFee::select('*')
                ->Where('code', '=', 'BVPO')
                ->first();

            //get upline queue
            $bv_queue = $this->get_bv_queue($upline->id);
            $bv_pairing_r = $bv_queue['r'];
            $bv_pairing_l = $bv_queue['l'];

            //get prev position
            if ($position == 'R') {
                $bv_pairing_r += $bv_amount_inc;
            } else {
                $bv_pairing_l += $bv_amount_inc;
            }
            $bv_pairing = $bv_pairing_r;
            if ($bv_pairing_l < $bv_pairing_r) {
                $bv_pairing = $bv_pairing_l;
            }
            $bv_pairing = ($bv_pairing - $bv_queue['c']);
            //compare min pairing
            //get network fee pairing -> upline activation type
            $nf_upline_pairing_row = NetworkFee::select('*')
                ->Where('type', '=', 'pairing')
                ->Where('activation_type_id', '=', $upline->activation_type_id)
                ->first();
            //memo
            $memo = $upline->code . " - " . $upline->name;
            //get min bv pairing -> upline activation type
            $min_bv_pairing = $nf_upline_pairing_row->bv_min_pairing * $bvpo_row->amount;
            //check if reach lev max
            $pairing_sbv = $nf_upline_pairing_row->sbv;

            if (($bv_pairing >= $min_bv_pairing) && $pairing_sbv > 0) {
                if ($upline->status == 'active' && $upline->type != "user") {
                    $upline_fee_pairing = (($pairing_sbv) / 100) * $bv_pairing;
                    $upline_amount = $upline_fee_pairing;
                    $reg_today = date('Y-m-d');
                    $daily_amount = $this->get_bv_daily_queue($upline->id, $reg_today);
                    $daily_amount_paired = $daily_amount + $upline_fee_pairing;
                    if ($daily_amount_paired <= $nf_upline_pairing_row->fee_day_max) {
                        echo '<=' . ' : ' . $daily_amount . ' : ' . $daily_amount_paired . ' : ' . $nf_upline_pairing_row->fee_day_max . ' : ' . $upline_fee_pairing;
                    } else {
                        $upline_fee_pairing = $nf_upline_pairing_row->fee_day_max - $daily_amount;
                        echo '>' . ' : ' . $daily_amount . ' : ' . $daily_amount_paired . ' : ' . $nf_upline_pairing_row->fee_day_max . ' : ' . $upline_fee_pairing;
                    }
                }
            }
        }
    }

    public function status_list_upline($slot_x, $slot_y)
    {
        $upline_arr['status'] = 0;
        $upline_arr['x'] = 0;
        $upline_arr['y'] = 0;
        $upline_arr['code'] = 0;
        $upline_arr['name'] = 0;
        $lev = $slot_x - 0;
        if ($slot_x == 0 && $slot_y == 1) {
            $upline_arr['status'] = 1;
        }
        for ($i = 0; $i < $lev; $i++) {
            $slot_x = $slot_x - 1;
            $slot_y = ceil($slot_y / 2);
            //get upline detail
            //check if active & min silver
            $hu_status = 1;
            $upline_def = Customer::select('*')
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->first();
            $upline = Customer::select('*')
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->where('status', 'active')
                ->where('activation_type_id', '>', 1)
                ->first();
            //check if 3 HU
            if ($upline) {
                $users_hu = Customer::select('*')->where('owner_id', $upline->id)->where('ref_bin_id', '>', 0)->get();
                $hu = count($users_hu);
                if ($hu >= 3 && $upline->activation_type_id < 3) {
                    $hu_status = 0;
                }
            }
            $upline_arr['x'] = $slot_x;
            $upline_arr['y'] = $slot_y;
            $upline_arr['code'] = 0;
            $upline_arr['name'] = 0;
            if ($upline_def) {
                $upline_arr['code'] = $upline_def->code;
                $upline_arr['name'] = $upline_def->name;
            }
            if (!$upline || $hu_status == 0) {
                $upline_arr['status'] = 0;
                break;
            } else {
                $upline_arr['status'] = 1;
            }
        }
        return $upline_arr;
    }

    public function get_downline_total($slot_x, $slot_y, $level_selected, $downline_total)
    {
        $slot_selected_x = $slot_x + 1;
        $slot_selected_y1 = ($slot_y * 2) - 1;
        $slot_selected_y2 = (($slot_y * 2) - 1) + (pow(2, $level_selected) - 1);
        //return $slot_selected_x.":".$slot_selected_y1.":".$slot_selected_y2;
        $customer = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->whereBetween('slot_y', [$slot_selected_y1, $slot_selected_y2])
            ->get();
        if (count($customer) > 0) {
            $level_selected += 1;
            $downline_total += count($customer);
            //recursive
            //echo $downline_total."<br>";
            return $this->get_downline_total($slot_selected_x, $slot_selected_y1, $level_selected, $downline_total);
        } else {
            //echo $downline_total." h <br>";
            return $downline_total;
        }
    }

    public function pairing_bin($order_id, $customer_id, $bv_amount_inc, $points_fee_id)
    {
        //init
        $fee_out = 0;
        //get order detail
        $order = Order::find($order_id);
        //get member detail
        $member = Customer::select('activation_type_id', 'slot_x', 'slot_y')
            ->where('id', $customer_id)
            ->first();
        $lev = $member->slot_x - 0;
        $slot_prev_x = $member->slot_x;
        $slot_prev_y = $member->slot_y;
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->first();
        //get max level
        $member_pairing_row = NetworkFee::select('*')
            ->Where('type', '=', 'pairing')
            ->Where('activation_type_id', '=', $member->activation_type_id)
            ->first();
        $pairing_lev_max = $member_pairing_row->deep_level;
        $lev_count = 1;
        //loop upline
        for ($i = 0; $i < $lev; $i++) {
            $slot_x = $slot_prev_x - 1;
            $slot_y = ceil($slot_prev_y / 2);
            //echo $slot_x." - ".$slot_y;
            //get upline detail
            $upline = Customer::select('*')
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->where('status', 'active')
                ->where('activation_type_id', '>', 1)
                ->first();
            //print_r($upline);

            if ($upline) {
                //get upline queue
                $bv_queue = $this->get_bv_queue($upline->id);
                $bv_pairing_r = $bv_queue['r'];
                $bv_pairing_l = $bv_queue['l'];

                //get prev position
                if ($slot_prev_y % 2 == 0) {
                    $bv_pairing_r += $bv_amount_inc;
                    $position = 'R';
                } else {
                    $bv_pairing_l += $bv_amount_inc;
                    $position = 'L';
                }
                $bv_pairing = $bv_pairing_r;
                if ($bv_pairing_l < $bv_pairing_r) {
                    $bv_pairing = $bv_pairing_l;
                }
                $bv_pairing = ($bv_pairing - $bv_queue['c']);
                //compare min pairing
                //get network fee pairing -> upline activation type
                $nf_upline_pairing_row = NetworkFee::select('*')
                    ->Where('type', '=', 'pairing')
                    ->Where('activation_type_id', '=', $upline->activation_type_id)
                    ->first();
                //memo
                $memo = $upline->code . " - " . $upline->name;
                //get min bv pairing -> upline activation type
                $min_bv_pairing = $nf_upline_pairing_row->bv_min_pairing * $bvpo_row->amount;
                // echo $bvpo_row->amount . '-' . $bv_amount_inc . '-' . $upline->code . '-' . $bv_pairing . '-' . $min_bv_pairing . '-' . $upline->status . '-' . $upline->type;
                // echo '</br>';
                //check if reach lev max
                if ($lev_count <= $pairing_lev_max) {
                    $pairing_sbv = $member_pairing_row->sbv;
                } else {
                    $pairing_sbv = $member_pairing_row->sbv2;
                }
                //mod bv pairing
                $bv_pairing_index = floor($bv_pairing / $min_bv_pairing);
                $bv_pairing = $min_bv_pairing * $bv_pairing_index;
                if (($bv_pairing >= $min_bv_pairing) && $pairing_sbv > 0) {
                    if ($upline->status == 'active' && $upline->type != "user") {
                        $upline_fee_pairing = (($pairing_sbv) / 100) * $bv_pairing;
                        $upline_amount = $upline_fee_pairing;
                        //hitung total bv_amount hari ini yang sudah di pairing di tbl pairing {bvarp_paired}
                        $reg_today = date('Y-m-d');
                        $daily_amount = $this->get_bv_daily_queue($upline->id, $reg_today);
                        $daily_amount_paired = $daily_amount + $upline_fee_pairing;
                        if ($daily_amount_paired <= $nf_upline_pairing_row->fee_day_max) {
                            $fee_out += (float) $upline_fee_pairing;
                            $this->fee_pairing_amount += (float) $upline_fee_pairing;
                            $order->points()->attach($points_fee_id, ['amount' => $upline_fee_pairing, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $upline->id]);
                        } else {
                            $upline_fee_pairing = $nf_upline_pairing_row->fee_day_max - $daily_amount;
                            if ($upline_fee_pairing > 0) {
                                $fee_out += (float) $upline_fee_pairing;
                                $this->fee_pairing_amount += (float) $upline_fee_pairing;
                                $order->points()->attach($points_fee_id, ['amount' => $upline_fee_pairing, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Pairing) dari group ' . $memo, 'customers_id' => $upline->id]);
                            }
                        }
                        //insert into tbl queue C
                        $data = ['order_id' => $order_id, 'customer_id' => $upline->id, 'bv_amount' => $bv_pairing, 'position' => 'N', 'status' => 'active', 'type' => 'C', 'pairing_amount' => $upline_fee_pairing];
                        $queue_crt = BVPairingQueue::create($data);
                    }
                }
                //insert into tbl queue D
                $data = ['order_id' => $order_id, 'customer_id' => $upline->id, 'bv_amount' => $bv_amount_inc, 'position' => $position, 'status' => 'active', 'type' => 'D'];
                $queue_crt = BVPairingQueue::create($data);
            }
            $lev_count++;
            //set prev
            $slot_prev_x = $slot_x;
            $slot_prev_y = $slot_y;
        }
        return $fee_out;
    }

    public function get_bv_daily_queue($customer_id, $reg_today)
    {
        $queue_c = BVPairingQueue::selectRaw("sum(pairing_amount) as total")
            ->where('customer_id', $customer_id)
            ->where('status', 'active')
            ->where('type', 'C')
            ->whereDate('created_at', '=', $reg_today)
            ->groupBy('customer_id')
            ->first();
        if ($queue_c) {
            return $queue_c->total;
        } else {
            return 0;
        }
    }

    public function get_bv_queue($customer_id)
    {
        //get upline detail
        $queue_l = BVPairingQueue::selectRaw("sum(bv_amount) as total")
            ->where('customer_id', $customer_id)
            ->where('position', 'L')
            ->where('status', 'active')
            ->where('type', 'D')
            ->groupBy('customer_id')
            ->first();
        $queue_r = BVPairingQueue::selectRaw("sum(bv_amount) as total")
            ->where('customer_id', $customer_id)
            ->where('position', 'R')
            ->where('status', 'active')
            ->where('type', 'D')
            ->groupBy('customer_id')
            ->first();
        $queue_c = BVPairingQueue::selectRaw("sum(bv_amount) as total")
            ->where('customer_id', $customer_id)
            ->where('status', 'active')
            ->where('type', 'C')
            ->groupBy('customer_id')
            ->first();
        $queue_c_count = BVPairingQueue::where('customer_id', $customer_id)
            ->where('status', 'active')
            ->where('type', 'C')
            ->count();
        if ($queue_l) {
            $queue_arr['l'] = $queue_l->total;
        } else {
            $queue_arr['l'] = 0;
        }
        if ($queue_r) {
            $queue_arr['r'] = $queue_r->total;
        } else {
            $queue_arr['r'] = 0;
        }
        if ($queue_c) {
            $queue_arr['c'] = $queue_c->total;
        } else {
            $queue_arr['c'] = 0;
        }
        $queue_arr['c_count'] = $queue_c_count;

        return $queue_arr;
    }

    public function ins_bv_upline($order_id, $customer_id, $bv_amount, $type, $position_init = 1)
    {
        //get member detail
        $member = Customer::select('slot_x', 'slot_y')
            ->where('id', $customer_id)
            ->first();
        $lev = $member->slot_x - 0;
        $slot_prev_y = $member->slot_y;
        //loop upline
        for ($i = 0; $i < $lev; $i++) {
            $slot_x = $member->slot_x - 1;
            $slot_y = ceil($member->slot_y / 2);
            //get position
            if ($position_init == 1) {
                $position = 'L';
                if ($slot_prev_y % 2 == 0) {
                    $position = 'R';
                }} else {
                $position = 'N';
            }
            $slot_prev_y = $slot_y;
            //get upline detail
            $upline = Customer::select("id")
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->where('status', 'active')
                ->first();
            if ($upline) {
                //insert
                $data = ['order_id' => $order_id, 'customer_id' => $upline->id, 'bv_amount' => $bv_amount, 'position' => $position, 'status' => 'active', 'type' => $type];
                $queue_crt = BVPairingQueue::create($data);
            }
        }
    }

    public function group_bv_amount($slot_x, $slot_y, $level_selected, $id_order_inc, $balance, $balance_ro)
    {
        if ($level_selected == 1) {
            //get top member
            $top_line = Customer::select('id')
                ->where('ref_bin_id', '>', 0)
                ->where('type', '=', 'member')
                ->where('slot_x', '=', $slot_x)
                ->where('slot_y', '=', $slot_y)
                ->first();
            $date_start_bin = "2022-11-10" . " 00:00:01";
            $balance += Order::where('customers_activation_id', '=', $top_line->id)
                ->where('type', '=', 'activation_member')
                ->where('created_at', '>=', $date_start_bin)
                ->where(function ($query) use ($id_order_inc) {
                    $query->where('status', '=', 'approved')
                        ->orWhere('id', '=', $id_order_inc);
                })
                ->sum('bv_activation_amount');
            $balance_ro += Order::where('customers_id', '=', $top_line->id)
                ->where('type', '=', 'agent_sale')
                ->where('created_at', '>=', $date_start_bin)
                ->where(function ($query) use ($id_order_inc) {
                    $query->where('status', '=', 'approved')
                        ->orWhere('id', '=', $id_order_inc);
                })
                ->sum('bv_ro_amount');
        }
        //get list on selected level
        $slot_selected_x = $slot_x + 1;
        $slot_selected_y1 = ($slot_y * 2) - 1;
        $slot_selected_y2 = (($slot_y * 2) - 1) + (pow(2, $level_selected) - 1);
        $customer_list = Customer::selectRaw('customers.id')
            ->join('orders', function ($join) use ($id_order_inc) {
                $join->on('orders.customers_activation_id', '=', 'customers.id')
                    ->where('orders.type', '=', 'activation_member')
                    ->where(function ($query) use ($id_order_inc) {
                        $query->where('orders.status', '=', 'approved')
                            ->orWhere('orders.id', '=', $id_order_inc);
                    });
            })
            ->where('customers.ref_bin_id', '>', 0)
            ->where('customers.type', '=', 'member')
            ->where('customers.slot_x', '=', $slot_selected_x)
            ->whereBetween('customers.slot_y', [$slot_selected_y1, $slot_selected_y2])
            ->groupBy('customers.id')
            ->get();
        if ($customer_list) {
            $level_selected += 1;
            // foreach ($customer_list as $customer) {
            //     //get bv_activation_bin_amount
            //     $balance += Order::where('customers_activation_id', '=', $customer->id)
            //         ->where('type', '=', 'activation_member')
            //         ->where(function ($query) use ($id_order_inc) {
            //             $query->where('status', '=', 'approved')
            //                 ->orWhere('id', '=', $id_order_inc);
            //         })
            //         ->sum('bv_activation_bin_amount');
            //     $balance_ro += Order::where('customers_id', '=', $customer->id)
            //         ->where('type', '=', 'agent_sale')
            //         ->where(function ($query) use ($id_order_inc) {
            //             $query->where('status', '=', 'approved')
            //                 ->orWhere('id', '=', $id_order_inc);
            //         })
            //         ->sum('bv_ro_bin_amount');
            // }
            return $this->group_bv_amount($slot_selected_x, $slot_selected_y1, $level_selected, $id_order_inc, $balance, $balance_ro);
        } else {
            // $balance_arr[0]['balance'] = $balance;
            // $balance_arr[0]['balance_ro'] = $balance_ro;
            // return $balance_arr;
            return $level_selected;
        }

    }

    public function get_list_upline($slot_x, $slot_y)
    {
        $upline_arr = array();
        $lev = $slot_x - 0;
        for ($i = 0; $i < $lev; $i++) {
            $slot_x = $slot_x - 1;
            $slot_y = ceil($slot_y / 2);
            //get upline detail
            $upline = Customer::select('code')
                ->where('slot_x', $slot_x)
                ->where('slot_y', $slot_y)
                ->first();
            $upline_arr[$i]['x'] = $slot_x;
            $upline_arr[$i]['y'] = $slot_y;
            if ($upline) {
                $upline_arr[$i]['code'] = $upline->code;
            } else {
                $upline_arr[$i]['code'] = 0;
            }
        }
        return $upline_arr;
    }

    public function group_if_lr($group_slot_x, $group_slot_y, $slot_x, $slot_y)
    {
        $lev = $slot_x - $group_slot_x;
        $slot_lr_x = $slot_x - $lev + 1;
        $slot_lr_y = $slot_y;
        for ($i = 1; $i < $lev; $i++) {
            $slot_lr_y = ceil($slot_lr_y / 2);
        }
        $slot_lr_arr['x'] = $slot_lr_x;
        $slot_lr_arr['y'] = $slot_lr_y;
        return $slot_lr_arr;
    }

    public function get_slot_empty_3hu($slot_x, $slot_y, $level_selected, $slot_arr)
    {
        $slot_selected_x = $slot_x + 1;
        $slot_selected_y1 = ($slot_y * 2) - 1;
        $slot_selected_y2 = (($slot_y * 2) - 1) + (pow(2, $level_selected) - 1);
        //return $slot_selected_x.":".$slot_selected_y1.":".$slot_selected_y2;
        $customer = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->whereBetween('slot_y', [$slot_selected_y1, $slot_selected_y2])
            ->first();
        if ($customer) {
            //loop to get empty on active slot
            $slot_empty_status = 0;
            $slot_empty_x = $slot_selected_x;
            $slot_empty_y = 0;
            for ($i = $slot_selected_y1; $i <= $slot_selected_y2; $i++) {
                $slot_exist = Customer::select('id')
                    ->where('ref_bin_id', '>', 0)
                    ->where('type', '=', 'member')
                    ->where('slot_x', '=', $slot_selected_x)
                    ->where('slot_y', '=', $i)
                    ->first();
                if (!$slot_exist) {
                    //check left and right
                    $slot_left_x = $slot_selected_x + 1;
                    $slot_right_x = $slot_selected_x + 1;
                    $slot_left_y = ($i * 2) - 1;
                    $slot_right_y = $i * 2;
                    $slot_left_exist = Customer::select('id')
                        ->where('ref_bin_id', '>', 0)
                        ->where('type', '=', 'member')
                        ->where('slot_x', '=', $slot_left_x)
                        ->where('slot_y', '=', $slot_left_y)
                        ->first();
                    $slot_right_exist = Customer::select('id')
                        ->where('ref_bin_id', '>', 0)
                        ->where('type', '=', 'member')
                        ->where('slot_x', '=', $slot_right_x)
                        ->where('slot_y', '=', $slot_right_y)
                        ->first();
                    //if left right empty
                    if (!$slot_left_exist && !$slot_right_exist) {
                        $slot_empty_status = 1;
                        $slot_empty_y = $i;
                        break;
                    }
                }
            }
            if ($slot_empty_status == 0) {
                //looking for next level
                $level_selected += 1;
                return $this->get_slot_empty_3hu($slot_selected_x, $slot_selected_y1, $level_selected, $slot_arr);
            } else {
                $slot_prev_x = $slot_empty_x - 1;
                $slot_prev_y = ceil($slot_empty_y / 2);
                $slot_arr['x'] = $slot_prev_x;
                $slot_arr['y'] = $slot_prev_y;
                return $slot_arr;
            }

        } else {
            $slot_prev_x = $slot_selected_x - 1;
            $slot_prev_y = ceil($slot_selected_y1 / 2);
            $slot_arr['x'] = $slot_prev_x;
            $slot_arr['y'] = $slot_prev_y;
            return $slot_arr;
        }
    }

    public function get_slot_empty($slot_x, $slot_y, $level_selected, $slot_arr)
    {
        $slot_selected_x = $slot_x + 1;
        $slot_selected_y1 = ($slot_y * 2) - 1;
        $slot_selected_y2 = (($slot_y * 2) - 1) + (pow(2, $level_selected) - 1);
        //return $slot_selected_x.":".$slot_selected_y1.":".$slot_selected_y2;
        $customer = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->whereBetween('slot_y', [$slot_selected_y1, $slot_selected_y2])
            ->first();
        if ($customer) {
            //loop to get empty on active slot
            $slot_empty_status = 0;
            $slot_empty_x = $slot_selected_x;
            $slot_empty_y = 0;
            for ($i = $slot_selected_y1; $i <= $slot_selected_y2; $i++) {
                $slot_exist = Customer::select('id')
                    ->where('ref_bin_id', '>', 0)
                    ->where('type', '=', 'member')
                    ->where('slot_x', '=', $slot_selected_x)
                    ->where('slot_y', '=', $i)
                    ->first();
                if (!$slot_exist) {
                    $slot_empty_status = 1;
                    $slot_empty_y = $i;
                    break;
                }
            }
            if ($slot_empty_status == 0) {
                //looking for next level
                $level_selected += 1;
                return $this->get_slot_empty($slot_selected_x, $slot_selected_y1, $level_selected, $slot_arr);
            } else {
                $slot_prev_x = $slot_empty_x - 1;
                $slot_prev_y = ceil($slot_empty_y / 2);
                $slot_arr['x'] = $slot_prev_x;
                $slot_arr['y'] = $slot_prev_y;
                $slot_arr['ex'] = $slot_empty_x;
                $slot_arr['ey'] = $slot_empty_y;
                return $slot_arr;
            }

        } else {
            $slot_prev_x = $slot_selected_x - 1;
            $slot_prev_y = ceil($slot_selected_y1 / 2);
            $slot_arr['x'] = $slot_prev_x;
            $slot_arr['y'] = $slot_prev_y;
            $slot_arr['ex'] = $slot_selected_x;
            $slot_arr['ey'] = $slot_selected_y1;
            return $slot_arr;
        }
    }

    public function get_level_total($slot_x, $slot_y, $level_selected, $level_total)
    {
        $slot_selected_x = $slot_x + 1;
        $slot_selected_y1 = ($slot_y * 2) - 1;
        $slot_selected_y2 = (($slot_y * 2) - 1) + (pow(2, $level_selected) - 1);
        //return $slot_selected_x.":".$slot_selected_y1.":".$slot_selected_y2;
        $customer = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->whereBetween('slot_y', [$slot_selected_y1, $slot_selected_y2])
            ->first();
        if ($customer) {
            $level_selected += 1;
            $level_total += 1;
            //recursive
            //echo $level_total."<br>";
            return $this->get_level_total($slot_selected_x, $slot_selected_y1, $level_selected, $level_total);
        } else {
            //echo $level_total." h <br>";
            return $level_total;
        }
    }

    public function downline_tree_bin($ref_id, $down_arr, $status, $slot_x, $slot_y, $top_id)
    {
        if ($status == "yes") {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->where('slot_x', '>', 0)
                ->with('activations')
                ->first();
        } else if ($status == "no") {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->where('slot_x', '=', null)
                ->with('activations')
                ->first();
        } else {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->with('activations')
                ->first();
        }
        if ($customer) {
            $array_adj = array('id' => $customer->id, 'name' => $customer->name, 'code' => $customer->code, 'type' => $customer->activations->name, 'slot_x' => $customer->slot_x, 'slot_y' => $customer->slot_y, 'slot_set_x' => $slot_x, 'slot_set_y' => $slot_y, 'top_id' => $top_id, 'address' => $customer->address);
            array_push($down_arr, $array_adj);
        }
        $downref_list = Customer::select('id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->where('type', '=', 'member')
            ->orderBy('activation_at', 'asc')
            ->get();
        foreach ($downref_list as $downline) {
            $down_arr = $this->downline_tree($downline->id, $down_arr, $status, $slot_x, $slot_y, $top_id);
        }
        return $down_arr;
    }

    public function fee_pairing($order_id, $ref_id, $bv_total, $bvcv_amount, $ref1_fee_point_sale, $ref1_fee_point_upgrade, $ref2_fee_point_sale, $ref2_fee_point_upgrade, $ref1_flush_out, $ledger_id, $cba2, $cbmart, $points_fee_id, $points_upg_id, $ref2_id, $memo, $member_get_flush_out, $package_type, $ref_fee_lev, $customer_id)
    {
        //PAIRING
        if ($package_type == 0) {
            //$fee_pairing = $this->pairing($order_id, $ref_id);
            $fee_pairing = $this->pairing_bin($order_id, $customer_id, $bv_total, $points_fee_id);
        } else {
            $fee_pairing = 0;
        }

        //get netfee_amount
        $bvcv = (($bvcv_amount) / 100) * $bv_total;
        $bv_nett = $bv_total - $bvcv;
        if ($package_type == 0) {
            $res_netfee_amount = $ref1_fee_point_sale + $ref1_fee_point_upgrade + $ref2_fee_point_sale + $ref2_fee_point_upgrade + $fee_pairing + $ref1_flush_out;
        } else {
            $res_netfee_amount = $ref_fee_lev;
        }

        //set account
        $acc_points = $this->account_lock_get('acc_points'); //'67'
        $acc_res_netfee = $this->account_lock_get('acc_res_netfee'); //'70'
        $acc_res_cashback = $this->account_lock_get('acc_res_cashback');
        $points_amount = $res_netfee_amount + $cba2 + $cbmart;
        $accounts = array($acc_points, $acc_res_netfee, $acc_res_cashback);
        $amounts = array($points_amount, $res_netfee_amount, $cba2 + $cbmart);
        $types = array('C', 'D', 'D');
        //order & ledger
        $order = Order::find($order_id);
        $ledger = Ledger::find($ledger_id);
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
            }
        }

        //set ref1 fee
        //point sale
        if ($ref1_fee_point_sale > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref1_fee_point_sale, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref_id]);
        }
        //point upgrade
        if ($ref1_fee_point_upgrade > 0) {
            $order->points()->attach($points_upg_id, ['amount' => $ref1_fee_point_upgrade, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin (Upgrade) Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref_id]);
        }

        //set ref2 fee
        //point sale
        if ($ref2_fee_point_sale > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref2_fee_point_sale, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref2_id]);
        }
        //point upgrade
        if ($ref2_fee_point_upgrade > 0) {
            $order->points()->attach($points_upg_id, ['amount' => $ref2_fee_point_upgrade, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin (Upgrade) Komisi (Refferal) dari ' . $memo, 'customers_id' => $ref2_id]);
        }
        //point flush out
        if ($ref1_flush_out > 0) {
            $order->points()->attach($points_fee_id, ['amount' => $ref1_flush_out, 'type' => 'D', 'status' => 'onhand', 'memo' => 'Poin Komisi (Flush Out) dari ' . $memo, 'customers_id' => $member_get_flush_out]);
        }
    }

    public function test_post($account_id, $code)
    {
        $data = ['account_id' => $account_id, 'code' => $code];
        $account = Accountlock::create($data);
    }

    public function get_member($id)
    {
        $member = Customer::with('activations')->find($id);
        return $member;
    }

    public function get_member_ro($id, $start_date)
    {
        $member_ro_bv = 0;
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->get();
        $from = !empty($start_date) ? $start_date : '';
        $to = date('Y-m-d');
        $order = Order::selectRaw("SUM(bv_ro_amount) AS member_ro_bv")
            ->where('customers_id', $id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('customers_id')
            ->get();
        if (count($order) > 0) {
            $member_ro_bv = $order[0]->member_ro_bv;
        }
        if ($member_ro_bv > 0) {
            $member_ro_bv = $member_ro_bv / $bvpo_row[0]->amount;
        }
        return $member_ro_bv;
    }

    public function get_member_fee($id, $prev)
    {
        $date_filter = date('Y-m', strtotime('-' . $prev . ' months'));
        $orderpoint = OrderPoint::selectRaw("SUM(order_points.amount) AS total")
            ->join('orders', 'orders.id', '=', 'order_points.orders_id')
            ->where('order_points.customers_id', '=', $id)
            ->where('order_points.type', '=', 'D')
            ->where('order_points.status', '=', 'onhand')
            ->where('order_points.memo', 'LIKE', '%komisi%')
            ->where('orders.created_at', 'LIKE', '%' . $date_filter . '%')->get();
        return $orderpoint;
    }

    public function get_member_down($id, $activation_type_id)
    {
        //find downline ref where activation id = $activation_type_id
        $member_ref_downline = Customer::selectRaw("count('id') AS total_downline")
            ->where('ref_id', $id)
            ->where('activation_type_id', $activation_type_id)
            ->where('status', '=', 'active')
            ->get();
        return $member_ref_downline;
    }

    public function get_group_count($ref_id, $activation_type_id)
    {
        $account_total = 0;
        $downref_list = Customer::select('id', 'activation_type_id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->get();
        foreach ($downref_list as $downline) {
            if ($downline->activation_type_id == $activation_type_id) {
                $account_total++;
            }
            $account_total += $this->get_group_count($downline->id, $activation_type_id);
        }
        return $account_total;
    }

    public function get_group_career_count($ref_id, $careertype_level_id)
    {
        $account_total = 0;
        $downref_list = Customer::select('id', 'activation_type_id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->get();
        foreach ($downref_list as $downline) {
            $career = Career::selectRaw("count('id') AS total")
                ->where('customer_id', $downline->id)
                ->where('careertype_id', '=', $careertype_level_id)
                ->first();
            $account_total += $career->total;
            $account_total += $this->get_group_career_count($downline->id, $careertype_level_id);
        }
        return $account_total;
    }

    public function get_member_level($id, $level_arr, $type)
    {
        $return_arr = array();
        $status = 0;
        $status_total = 0;
        $inc = 0;
        if ($type == 'career') {
            foreach ($level_arr as $key => $careertype) {
                $level_arr[$key]['name'] = $careertype->name;
                $level_arr[$key]['careertype_level_id'] = $careertype->pivot->careertype_level_id;
                $level_arr[$key]['careertype_amount'] = $careertype->pivot->amount;
                $amount = $this->get_group_career_count($id, $careertype->pivot->careertype_level_id);
                $level_arr[$key]['amount'] = $amount;
                if ($careertype->pivot->amount <= $amount) {
                    $level_arr[$key]['status'] = 1;
                    $status_total += 1;
                } else {
                    $level_arr[$key]['status'] = 0;
                    $status_total += 0;
                }
                $inc++;
            }
        }
        if ($type == 'activation') {
            foreach ($level_arr as $key => $activationtype) {
                $level_arr[$key]['name'] = $activationtype->name;
                $level_arr[$key]['careertype_level_id'] = $activationtype->pivot->activation_id;
                $level_arr[$key]['careertype_amount'] = $activationtype->pivot->amount;
                $amount = $this->get_group_count($id, $activationtype->pivot->activation_id);
                $level_arr[$key]['amount'] = $amount;
                if ($activationtype->pivot->amount <= $amount) {
                    $level_arr[$key]['status'] = 1;
                    $status_total += 1;
                } else {
                    $level_arr[$key]['status'] = 0;
                    $status_total += 0;
                }
                $inc++;
            }
        }
        if ($inc > 0 && $inc == $status_total) {
            $status = 1;
        }
        $return_arr['levels'] = $level_arr;
        $return_arr['status'] = $status;
        return $return_arr;
    }

    public function get_act_type_bv($bv)
    {
        $act_type_id = 1;
        $activation_list = ActivationType::get();
        foreach ($activation_list as $activation) {
            if ($bv >= $activation->bv_min && $bv < $activation->bv_max) {
                $act_type_id = $activation->id;
            }
        }
        return $act_type_id;
    }

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

    public function downline_tree($ref_bin_id, $down_arr)
    {
        $bv_activation_amount_total = 0;
        $customer = Customer::select('id', 'code', 'name')
            ->where('id', $ref_bin_id)
            ->where('status', '=', 'active')
            ->first();
        if ($customer) {
            $array_adj = array('id' => $customer->id, 'name' => $customer->name, 'code' => $customer->code);
            array_push($down_arr, $array_adj);
        }
        $downref_list = Customer::select('id')
            ->where('ref_bin_id', $ref_bin_id)
            ->where('status', '=', 'active')
            ->orderBy('activation_at', 'asc')
            ->get();
        foreach ($downref_list as $downline) {
            $down_arr = $this->downline_tree($downline->id, $down_arr);
        }
        return $down_arr;
    }

    public function downline_tree2($ref_id, $down_arr, $status, $slot_x, $slot_y, $top_id)
    {
        if ($status == "yes") {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->where('slot_x', '>', 0)
                ->with('activations')
                ->first();
        } else if ($status == "no") {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->where('slot_x', '=', null)
                ->with('activations')
                ->first();
        } else {
            $customer = Customer::select('activation_type_id', 'id', 'code', 'name', 'address', 'slot_x', 'slot_y')
                ->where('id', $ref_id)
                ->where('status', '=', 'active')
                ->where('type', '=', 'member')
                ->with('activations')
                ->first();
        }
        if ($customer) {
            $array_adj = array('id' => $customer->id, 'name' => $customer->name, 'code' => $customer->code, 'type' => $customer->activations->name, 'slot_x' => $customer->slot_x, 'slot_y' => $customer->slot_y, 'slot_set_x' => $slot_x, 'slot_set_y' => $slot_y, 'top_id' => $top_id, 'address' => $customer->address);
            array_push($down_arr, $array_adj);
        }
        $downref_list = Customer::select('id')
            ->where('ref_id', $ref_id)
            ->where('status', '=', 'active')
            ->where('type', '=', 'member')
            ->orderBy('activation_at', 'asc')
            ->get();
        foreach ($downref_list as $downline) {
            $down_arr = $this->downline_tree2($downline->id, $down_arr, $status, $slot_x, $slot_y, $top_id);
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
        $this->id_order_priv = $id_order;
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
            //ref 2
            $ref2_row = Customer::find($ref1_row->ref_id);
            $ref2_activation_row = Activation::find($ref2_row->activation_type_id);
            //get network fee pairing -> ref1 activation type
            $nf_rf2_pairing_row = NetworkFee::select('*')
                ->Where('type', '=', 'pairing')
                ->Where('activation_type_id', '=', $ref2_row->activation_type_id)
                ->get();
            //get min bv pairing -> ref1 activation type
            $min_bv_pairing = $nf_rf2_pairing_row[0]->bv_min_pairing * $bvpo_row[0]->amount;
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
                    //set fee to ref2
                    $ref2_id = 0;

                    $ref2_fee_pairing = 0;
                    if (!empty($ref2_row) && $ref2_row->ref_id > 0 && $test == 0 && $ref2_row->status == 'active' && $ref2_activation_row->type != "user") {
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
            $balance_ro = Order::where('customers_id', '=', $ref1_id)
                ->where('type', '=', 'agent_sale')
                ->where(function ($query) {
                    $query->where('status', '=', 'approved')
                        ->orWhere('id', '=', $this->id_order_priv);
                })
                ->sum('bv_ro_amount');
            $bv_activation_amount_total = $balance + $balance_ro;
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
            $balance_ro = Order::where('customers_id', '=', $downline->id)
                ->where('type', '=', 'agent_sale')
                ->where(function ($query) {
                    $query->where('status', '=', 'approved')
                        ->orWhere('id', '=', $this->id_order_priv);
                })
                ->sum('bv_ro_amount');
            $downref_omzet = $this->downref_omzet($downline->id, $ref1_row->activation_at, 1);
            // echo "</br>". $downline_row->code . "-" . $downline_row->name . "-" . $balance . "-" . $downref_omzet;
            $bv_activation_amount_total += $balance + $balance_ro + $downref_omzet;
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
        $downline_lev = $deep_lev - $downref_lev_num;
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
            $balance_ro = Order::where('customers_id', '=', $downline->id)
                ->where('type', '=', 'agent_sale')
                ->where(function ($query) {
                    $query->where('status', '=', 'approved')
                        ->orWhere('id', '=', $this->id_order_priv);
                })
                ->sum('bv_ro_amount');
            $bv_activation_amount_total += $balance + $balance_ro;
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
        if (!empty($member_row) && (empty($member_last_row) || $member_row->ref_id == 0)) {
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
        if ($type == "stock_trsf") {
            $account = Order::where('type', 'stock_trsf')
                ->orderBy('id', 'desc')
                ->first();
            if ($account && (strlen($account->code) == 8)) {
                $code = $account->code;
            } else {
                $code = acc_codedef_generate('TSA', 8);
            }
        }

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
