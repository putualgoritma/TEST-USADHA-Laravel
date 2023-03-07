<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Career;
use App\Careertype;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CareertypesApiController extends Controller
{
    use TraitModel;

    public function lists(Request $request)
    {
        $members['member'] = $this->get_member($request->customer_id);
        //get last career
        $start_date = '';
        $career_selected_id = 0;
        $members['level_checked'] = 0;
        $members['level_name_checked'] = '-';
        $career = Career::select("*")
            ->where('customer_id', $request->customer_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($career) {
            $start_date = $career->created_at->format('Y-m-d');
            $career_selected_id = $career->careertype_id;
            $members['level_checked'] = $career->careertype_id;
            $careertype = Careertype::select("name")
                ->where('id', $career->careertype_id)
                ->first();
            $members['level_name_checked'] = $careertype->name;
        }
        $members['member_ro'] = $this->get_member_ro($request->customer_id, $start_date);
        $members['member_fee1'] = $this->get_member_fee($request->customer_id, 1);
        $members['member_fee2'] = $this->get_member_fee($request->customer_id, 2);
        $members['member_fee3'] = $this->get_member_fee($request->customer_id, 3);
        $members['get_member_down1'] = $this->get_member_down($request->customer_id, 1);
        $members['get_member_down2'] = $this->get_member_down($request->customer_id, 2);
        $members['get_member_down3'] = $this->get_member_down($request->customer_id, 3);
        $members['get_member_down4'] = $this->get_member_down($request->customer_id, 4);

        $careertypes = Careertype::with('careertypes')
            ->where('id', '>', $career_selected_id)
            ->with('activationtypes')
            ->with('activations')
            ->with('activationdownlines')
            ->get();
        foreach ($careertypes as $key => $value) {
            $status_total = 0;
            $inc = 0;
            //get member status
            if ($value->activation_type_id <= $members['member']->activation_type_id) {
                $member_activation_status = 1;
                $status_total += 1;
            } else {
                $member_activation_status = 0;
            }
            $careertypes[$key]->member_activation_status = $member_activation_status;
            $inc++;
            //get member ro status
            if ($value->ro_min_bv <= $members['member_ro']) {
                $member_ro_status = 1;
                $status_total += 1;
            } else {
                $member_ro_status = 0;
            }
            $careertypes[$key]->member_ro_status = $member_ro_status;
            $inc++;
            //get member fee status
            if ($value->fee_min <= $members['member_fee1'][0]->total && $value->fee_min <= $members['member_fee2'][0]->total && $value->fee_min <= $members['member_fee3'][0]->total) {
                $member_fee_status = 1;
                $status_total += 1;
            } else {
                $member_fee_status = 0;
            }
            $careertypes[$key]->member_fee_status = $member_fee_status;
            $inc++;
            //get member down
            $member_down = $this->get_member_down($request->customer_id, $value->ref_downline_id);
            if ($value->ref_downline_num <= $member_down[0]->total_downline) {
                $member_down_status = 1;
                $status_total += 1;
            } else {
                $member_down_status = 0;
            }
            $careertypes[$key]->member_down_status = $member_down_status;
            $inc++;
            $careertypes[$key]->member_down = $member_down[0]->total_downline;

            if ($value->team_level == 'career' && count($value->careertypes) > 0) {
                $get_member_level = $this->get_member_level($request->customer_id, $value->careertypes, 'career');
                $careertypes[$key]->team_levels = $get_member_level['levels'];
                $careertypes[$key]->team_level_status = $get_member_level['status'];
                if ($get_member_level['status'] == 1) {
                    $status_total += 1;
                }
            }
            if ($value->team_level == 'activation' && count($value->activationtypes) > 0) {
                $get_member_level = $this->get_member_level($request->customer_id, $value->activationtypes, 'activation');
                $careertypes[$key]->team_levels = $get_member_level['levels'];
                $careertypes[$key]->team_level_status = $get_member_level['status'];
                if ($get_member_level['status'] == 1) {
                    $status_total += 1;
                }
            }
            $inc++;
            if ($inc == $status_total) {
                $careertypes[$key]->level_status = 1;
                $members['level_checked'] = $value->id;
                $members['level_name_checked'] = $value->name;
            } else {
                $careertypes[$key]->level_status = 0;
            }

        }

        //check if level change
        if (!empty($career)) {
            if ($career->careertype_id < $members['level_checked']) {
                //close all related
                Career::where('customer_id', $request->customer_id)->update(['status' => 'close']);
                //update career
                $data = ['customer_id' => $request->customer_id, 'careertype_id' => $members['level_checked'], 'current_ro_amount' => $members['member_ro']];
                $career_upd = Career::create($data);
            }
        } else {
            if ($members['level_checked'] > 0) {
                //close all related
                Career::where('customer_id', $request->customer_id)->update(['status' => 'close']);
                //insert career
                $data = ['customer_id' => $request->customer_id, 'careertype_id' => $members['level_checked'], 'current_ro_amount' => $members['member_ro']];
                $career_crt = Career::create($data);
            }
        }

        //Check if history found or not.
        if (is_null($careertypes)) {
            $message = 'Jenjang Karir not found.';
            $status = false;
            return response()->json([
                'status' => $status,
                'message' => $message,
            ]);
        } else {
            $message = 'Jenjang Karir retrieved successfully.';
            $status = true;
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $careertypes,
                'data2' => $members,
            ]);
        }
    }
}
