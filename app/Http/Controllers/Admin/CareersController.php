<?php

namespace App\Http\Controllers\Admin;

use App\Career;
use App\Careertype;
use App\Http\Controllers\Controller;
use App\Member;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CareersController extends Controller
{
    use TraitModel;

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('career_access'), 403);

        if ($request->ajax()) {
            if (isset($request->status) && $request->status == "active") {
                $query = Member::selectRaw("customers.*,careers.customer_id,careers.careertype_id,careertypes.name as careertype_name")
                    ->leftJoin('careers', 'careers.customer_id', '=', 'customers.id')
                    ->leftJoin('careertypes', 'careertypes.id', '=', 'careers.careertype_id')
                    ->where(function ($qry) {
                        $qry->where('customers.type', '=', 'member')
                            ->orWhere('customers.def', '=', '1');
                    })
                    ->where('careers.status', 'active')
                //->where('careers.careertype_id', null)
                    ->where('customers.status', 'active')
                    ->orderBy("customers.activation_at", "DESC")
                    ->get();
            } else if (isset($request->status) && $request->status == "pending") {
                $query = Member::selectRaw("customers.*,careers.customer_id,careers.careertype_id,careertypes.name as careertype_name")
                    ->leftJoin('careers', 'careers.customer_id', '=', 'customers.id')
                    ->leftJoin('careertypes', 'careertypes.id', '=', 'careers.careertype_id')
                    ->where(function ($qry) {
                        $qry->where('customers.type', '=', 'member')
                            ->orWhere('customers.def', '=', '1');
                    })
                    ->where('careers.careertype_id', null)
                    ->where('customers.status', 'active')
                    ->orderBy("customers.activation_at", "DESC")
                    ->get();
            } else {
                $query = Member::selectRaw("customers.*,careers.customer_id,careers.careertype_id,careertypes.name as careertype_name")
                    ->leftJoin('careers', 'careers.customer_id', '=', 'customers.id')
                    ->leftJoin('careertypes', 'careertypes.id', '=', 'careers.careertype_id')
                    ->where(function ($qry) {
                        $qry->where('customers.type', '=', 'member')
                            ->orWhere('customers.def', '=', '1');
                    })
                    ->where('customers.status', 'active')
                    ->orderBy("customers.activation_at", "DESC")
                    ->get();
            }
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'career_show';
                $editGate = 'career_edit';
                $deleteGate = 'career_delete';
                $crudRoutePart = 'careers';

                return view('partials.datatablesCareers', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->activation_at ? $row->activation_at : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('careertype_name', function ($row) {
                return $row->careertype_name ? $row->careertype_name : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            return $table->make(true);
        }

        $careers = Member::selectRaw("customers.*,careers.customer_id,careers.careertype_id,careertypes.name as careertype_name")
            ->leftJoin('careers', 'careers.customer_id', '=', 'customers.id')
            ->leftJoin('careertypes', 'careertypes.id', '=', 'careers.careertype_id')
            ->where(function ($qry) {
                $qry->where('customers.type', '=', 'member')
                    ->orWhere('customers.def', '=', '1');
            })
            ->where('customers.status', 'active')
            ->orderBy("customers.activation_at", "DESC")
            ->get();

        $out_val = array();
        return view('admin.careers.index', compact('careers'));
    }

    public function create(Request $request)
    {
        abort_unless(\Gate::allows('career_create'), 403);
        $customer_selected = new \stdClass();
        $customer_selected->id = null;
        $customer_selected->code = null;
        $customer_selected->name = null;
        if ($request->id) {
            $customer_selected = Member::find($request->id);
        }
        $careertypes = Careertype::get();

        return view('admin.careers.create', compact('customer_selected', 'careertypes'));
    }

    public function store(Request $request)
    {
        abort_unless(\Gate::allows('career_create'), 403);

        //get ro amount
        $start_date = '';
        $career = Career::select("*")
            ->where('customer_id', $request->customer_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($career) {
            $start_date = $career->created_at->format('Y-m-d');
        }
        $member_ro = $this->get_member_ro($request->customer_id, $start_date);
        //return $request->customer_id.'::'.$request->careertype_id.'::'.$member_ro;
        //init
        $data = ['customer_id' => $request->customer_id, 'careertype_id' => $request->careertype_id, 'current_ro_amount' => $member_ro];
        $career_crt = Career::create($data);

        return redirect()->route('admin.careers.index');
    }

    public function showMember(Request $request)
    {
        abort_unless(\Gate::allows('career_show'), 403);

        // get member
        $member = Member::find($request->id);

        $members['member'] = $this->get_member($member->id);
        //get last career
        $start_date = '';
        $career_selected_id = 0;
        $members['level_checked'] = 0;
        $members['level_name_checked'] = '-';
        $career = Career::select("*")
            ->where('customer_id', $member->id)
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
        $members['member_ro'] = $this->get_member_ro($member->id, $start_date);
        $members['member_fee1'] = $this->get_member_fee($member->id, 1);
        $members['member_fee2'] = $this->get_member_fee($member->id, 2);
        $members['member_fee3'] = $this->get_member_fee($member->id, 3);
        $members['get_member_down1'] = $this->get_member_down($member->id, 1);
        $members['get_member_down2'] = $this->get_member_down($member->id, 2);
        $members['get_member_down3'] = $this->get_member_down($member->id, 3);
        $members['get_member_down4'] = $this->get_member_down($member->id, 4);

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
            $member_down = $this->get_member_down($member->id, $value->ref_downline_id);
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
                $get_member_level = $this->get_member_level($member->id, $value->careertypes, 'career');
                $careertypes[$key]->team_levels = $get_member_level['levels'];
                $careertypes[$key]->team_level_status = $get_member_level['status'];
                if ($get_member_level['status'] == 1) {
                    $status_total += 1;
                }
            }
            if ($value->team_level == 'activation' && count($value->activationtypes) > 0) {
                $get_member_level = $this->get_member_level($member->id, $value->activationtypes, 'activation');
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
                $data = ['customer_id' => $member->id, 'careertype_id' => $members['level_checked'], 'current_ro_amount' => $members['member_ro']];
                $career_upd = Career::create($data);
            }
        } else {
            if ($members['level_checked'] > 0) {
                //close all related
                Career::where('customer_id', $request->customer_id)->update(['status' => 'close']);
                //insert career
                $data = ['customer_id' => $member->id, 'careertype_id' => $members['level_checked'], 'current_ro_amount' => $members['member_ro']];
                $career_crt = Career::create($data);
            }
        }

        return view('admin.careers.show', compact('member', 'careertypes', 'members'));
    }

    public function listMember(Request $request)
    {

        $members = Member::selectRaw("customers.*,careers.customer_id,careers.careertype_id,careertypes.name as careertype_name")
            ->leftJoin('careers', 'careers.customer_id', '=', 'customers.id')
            ->leftJoin('careertypes', 'careertypes.id', '=', 'careers.careertype_id')
            ->where(function ($qry) {
                $qry->where('customers.type', '=', 'member')
                    ->orWhere('customers.def', '=', '1');
            })
            ->where('customers.status', 'active')
            ->orderBy("customers.activation_at", "DESC")
            ->get();

        // ajax
        if ($request->ajax()) {

            $query = $members;

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                return view('partials.datatablesMembersList', compact(
                    'row',
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
                return $row['careertype_name'];
            });

            $table->rawColumns(['actions', 'placeholder']);

            // $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        //$downline_tree=$this->downline_tree($ref_id,$down_arr);

        return view('admin.careers.listMember');
    }
}
