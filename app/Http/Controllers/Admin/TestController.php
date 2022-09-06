<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use App\Withdraw;
use App\Careertype;
use App\Jobs\ProcessPairing;

class TestController extends Controller
{
    use TraitModel;

    public function test(Request $request)
    {
        return env('ADMIN_ACC_TRSF');
        // ProcessPairing::dispatch($request->account_id,$request->code);
        /*
        $careertypes = Careertype::with('careertypes')->with('activationtypes')->find($request->careertype_level_id);
        if($careertypes->team_level=='career' && count($careertypes->careertypes)>0){
            $this->get_member_level($request->customer_id,$careertypes->careertypes,'career');            
        }
        if($careertypes->team_level=='activation' && count($careertypes->activationtypes)>0){
            $this->get_member_level($request->customer_id,$careertypes->activationtypes,'activation');
        }
        
        //return $careertypes->careertypes;
        //echo $this->get_member_level(819,3);
        // $this->pairing(9627, 7828);
        //echo $this->get_act_type_bv($request->bv);
        //echo $this->account_lock_get($request->code);
        /*
        $downref_list = $this->downref_list($request->ref1_id, $request->level);
        echo "downref_list: ".$downref_list;
        echo "<br>";
        $downref_lev_num = $this->downref_lev_num($request->ref1_id, $request->ref1_id, $request->parent_id, 0);
        echo "downref_lev_num: ".$downref_lev_num;
        echo "<br>";
        $downline_lev = $request->level-($downref_lev_num);
        $dwn_arr = array();
        $downline_list = $this->downline_list($request->ref1_id, $request->parent_id, $dwn_arr, 1, 0, $downline_lev);
        echo "downline_list: ";
        print_r($downline_list);
        
        
        //print_r($downref_list);
        /*
        $points_balance=$this->points_balance(2088);
        $withdraw = Withdraw::with('points')->find(5836);
        $amount_res = $withdraw->total;
        $points_type = array();
        foreach ($withdraw->points as $point_key => $point_value) {
            if($point_value->id==1){
                $point_balance=$points_balance->balance_points;
            }
            if($point_value->id==3){
                $point_balance=$points_balance->balance_saving_points;
            }
            if($point_value->id==4){
                $point_balance=$points_balance->fee_points;
            }
            $points_type[$point_value->id] = $point_balance;
            // echo $point_value->id." - ".$point_value->pivot->amount." - ".$point_balance." <br>";
        }
        asort($points_type);
        foreach ($points_type as $point_key => $point_value) {
            // echo $point_key." - ".$point_value." <br>";
            $amount_res -= $point_value;
        }
        return $withdraw->customers->code;
        /*
        // $dwn_arr = array();
        // return $this->get_ref_plat($request->id);
        $points_type = array();
        $amount = $request->amount;
        if (!isset($request->selectedBalance)) {
            $points_type['1'] = 400;
        } else {
            if ($request->selectedBalance == true) {
                $points_type['1'] = 400;
            }
            if ($request->selectedFee == true) {
                $points_type['4'] = 30;
            }
            if ($request->selectedSaving == true) {
                $points_type['3'] = 60;
            }}
        // $points_type=array("1"=>"400","2"=>"10","3"=>"15","4"=>"2");
        asort($points_type);
        $amount_res = $amount;
        foreach ($points_type as $point_key => $point_value) {
            // echo $point_key." - ".$point_value." <br>";
            $amount_res -= $point_value;
        }
        if($amount_res<=0){
            return 'saldo cukup';
        }else{
            return 'saldo kurang';
        }
        return $amount_res;
        // return $points_type;
        */
    }

    public function tree(Request $request)
    {
        return $this->downref_omzet_view($request->input('member_code'), $request->input('activation_at'), 1);
        // $up_arr = array();
        // return $this->ref_up_list($request->input('ref_id'), $up_arr);
    }
}
