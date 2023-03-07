<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\NetworkFee;
use App\Career;

class TreeController extends Controller
{
    use TraitModel;

    public function treeModal(Request $request)
    {
        //get user info
        $user = Customer::select('*')
            ->Where('id', '=', $request->id)
            ->with('activations')
            ->with('refferal')
            ->first();
        //get pairing info
        //BVPO
        $bvpo_row = NetworkFee::select('*')
            ->Where('code', '=', 'BVPO')
            ->first();
        //set arr
        $pairing_info_arr=array();
        $bv_queue = $this->get_bv_queue($request->id);
        $pairing_info_arr['bv_pairing_r'] = ($bv_queue['r'] - $bv_queue['c']) / $bvpo_row->amount;
        $pairing_info_arr['bv_pairing_l'] = ($bv_queue['l'] - $bv_queue['c']) / $bvpo_row->amount;
        $pairing_info_arr['bv_queue_c'] = $bv_queue['c'] / $bvpo_row->amount;
        $pairing_info_arr['bv_queue_c_count'] = $bv_queue['c_count'];
        $reg_today = date('Y-m-d');
        $pairing_info_arr['get_bv_daily_queue'] = $this->get_bv_daily_queue($request->id, $reg_today) / $bvpo_row->amount;
        //get net info
        $member = Customer::find($request->id);
        $downline_ref = Customer::select('id')
            ->where('ref_bin_id', $request->id)
            ->where('type', '=', 'member')
            ->where('status', '=', 'active')
            ->get();
        //get total level
        $get_level_total = $this->get_level_total($member->slot_x, $member->slot_y, 1, 0);
        //get left and right child
        $slot_selected_x = $member->slot_x + 1;
        $slot_selected_y_left = ($member->slot_y * 2) - 1;
        $slot_selected_y_right = ($member->slot_y * 2);
        $downline_left = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->where('slot_y', $slot_selected_y_left)
            ->first();
        $downline_right = Customer::select('id')
            ->where('ref_bin_id', '>', 0)
            ->where('type', '=', 'member')
            ->where('slot_x', '=', $slot_selected_x)
            ->where('slot_y', $slot_selected_y_right)
            ->first();

        $downline_left_total = 0;
        if ($downline_left) {
            $downline_left_total = $this->get_downline_total($slot_selected_x, $slot_selected_y_left, 1, 0) + 1;
        }
        $downline_right_total = 0;
        if ($downline_right) {
            $downline_right_total = $this->get_downline_total($slot_selected_x, $slot_selected_y_right, 1, 0) + 1;
        }
        $net_info_arr=array();
        $net_info_arr['right_total']=$downline_right_total;
        $net_info_arr['left_total']=$downline_left_total;
        $net_info_arr['level_total']=$get_level_total;
        $net_info_arr['ref_total']=count($downline_ref);

        //career info
        $careertype_name='';
        $career = Career::select("*")
            ->where('customer_id', $request->id)
            ->with('careertypes')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($career) {
            $careertype_name=$career->careertypes->name;
        }

        return view('admin.tree.modal', compact('user','pairing_info_arr','net_info_arr','careertype_name'));
    }

    public function tree(Request $request)
    {
        $slot_prev_x = -1;
        $slot_prev_y = -1;
        $user = Customer::select('id','slot_x','slot_y')
            ->Where('def', '=', '1')
            ->first();
        //return $user;
        if ($request->input('id')) {
            $user_selected = Customer::select('id','slot_x','slot_y')
            ->Where('id', '=', $request->input('id'))
            ->first();
            $slot_init_x = $user_selected->slot_x;
            $slot_init_y = $user_selected->slot_y;
            if ($slot_init_x > $user->slot_x) {
                $slot_prev_x = $slot_init_x - 3;
                $slot_prev_y = ceil($slot_init_y / 2);
                $slot_prev_y = ceil($slot_prev_y / 2);
                $slot_prev_y = ceil($slot_prev_y / 2);
            }
        }else if ($request->input('slot_x')) {
            $slot_init_x = $request->input('slot_x');
            $slot_init_y = $request->input('slot_y');
            if ($slot_init_x > $user->slot_x) {
                $slot_prev_x = $slot_init_x - 3;
                $slot_prev_y = ceil($slot_init_y / 2);
                $slot_prev_y = ceil($slot_prev_y / 2);
                $slot_prev_y = ceil($slot_prev_y / 2);
            }
        }  else {
            $slot_init_x = $user->slot_x;
            $slot_init_y = $user->slot_y;
        }
        $slot_arr = array();
        $slot_arr[0][0]['x'] = $slot_init_x;
        $slot_arr[0][0]['y'] = $slot_init_y;
        $slot_customer = Customer::select('id', 'activation_type_id', 'code', 'name', 'status')->where("slot_x", $slot_init_x)->where("slot_y", $slot_init_y)->with('activations')->first();
        if ($slot_customer) {
            $slot_arr[0][0]['data'] = $slot_customer;
            $top_id = $slot_customer->id;
        } else {
            $slot_arr[0][0]['data'] = '';
            $top_id = 0;
        }
        for ($i = 1; $i <= 3; $i++) {
            $slot_arr[$i][0]['x'] = $slot_arr[$i - 1][0]['x'] + 1;
            $slot_arr[$i][0]['y'] = ($slot_arr[$i - 1][0]['y'] * 2) - 1;
            $slot_customer = Customer::select('id', 'activation_type_id', 'code', 'name', 'status')->where("slot_x", $slot_arr[$i][0]['x'])->where("slot_y", $slot_arr[$i][0]['y'])->with('activations')->first();
            if ($slot_customer) {
                $slot_arr[$i][0]['data'] = $slot_customer;
            } else {
                $slot_arr[$i][0]['data'] = '';
            }
            for ($j = 1; $j < pow(2, $i); $j++) {
                $slot_arr[$i][$j]['x'] = $slot_arr[$i][$j - 1]['x'];
                $slot_arr[$i][$j]['y'] = ($slot_arr[$i][$j - 1]['y']) + 1;
                $slot_customer = Customer::select('id', 'activation_type_id', 'code', 'name', 'status')->where("slot_x", $slot_arr[$i][$j]['x'])->where("slot_y", $slot_arr[$i][$j]['y'])->with('activations')->first();
                if ($slot_customer) {
                    $slot_arr[$i][$j]['data'] = $slot_customer;
                } else {
                    $slot_arr[$i][$j]['data'] = '';
                }
            }
        }
        //return $slot_arr;
        return view('admin.tree.index', compact('slot_arr', 'top_id', 'slot_prev_x', 'slot_prev_y'));
    }  
    
    public function index(Request $request)
    {

        $user = Customer::select('id','slot_x','slot_y','ref_id')
            ->Where('def', '=', '1')
            ->first();
        $top_id = $user->id;
        $slot_x = $user->slot_x;
        $slot_y = $user->slot_y;
        $selected_ref_id = 0;
        $status = "all";
        if ($request->status) {
            $status = $request->status;
        }
        $ref_id = $user->ref_id;
        $down_arr = array();
        $downline_tree = $this->downline_tree2($ref_id, $down_arr, $status, $slot_x, $slot_y, $top_id);

        //return $downline_tree;

        // ajax
        if ($request->ajax()) {

            $query = $downline_tree;

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) use ($selected_ref_id) {
                $selected_ref_id = $selected_ref_id;
                return view('partials.datatablesReposisi', compact(
                    'row',
                    'selected_ref_id',
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row['code'] ? $row['code'] : "";
            });

            $table->editColumn('name', function ($row) {
                return $row['name'] ? $row['name'] : "";
            });

            $table->editColumn('address', function ($row) {
                return $row['address'] ? $row['address'] : "";
            });

            $table->editColumn('type', function ($row) {
                return $row['type'] ? $row['type'] : "";
            });

            $table->editColumn('status', function ($row) {
                return ($row['slot_x'] > 0) ? 1 : 0;
            });

            $table->rawColumns(['actions', 'placeholder']);

            // $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        //$downline_tree=$this->downline_tree($ref_id,$down_arr);

        return view('admin.tree.listMember', compact('top_id', 'slot_x', 'slot_y', 'selected_ref_id'));
    }
}
