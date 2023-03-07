<?php

namespace App\Http\Controllers\Admin;

use App\ActivationType;
use App\BVPairingQueue;
use App\Career;
use App\Careertype;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMemberRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Ledger;
use App\LogNotif;
use App\Member;
use App\Order;
use App\OrderDetails;
use App\OrderPoint;
use App\Package;
use App\Pairing;
use App\Product;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use DB;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use OneSignal;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class MembersController extends Controller
{

    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function upgrade($id)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        $member = Member::find($id);
        $activationtypes = ActivationType::where('id', '>', $member->activation_type_id)
            ->get();

        return view('admin.members.upgrade', compact('member', 'activationtypes'));
    }

    public function upgradeProcess(Request $request)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        if ($request->input('activation_type_id') > 0) {
            $member = Member::find($request->input('id_hidden'));
            //update
            $member->activation_type_id = $request->input('activation_type_id');
            $member->save();
            return redirect()->route('admin.members.index');
        } else {
            //response
            $message = 'Tipe belum dipilih!';
            return back()->withError($message)->withInput();
        }

    }

    public function activationCancell($id)
    {
        abort_if(Gate::denies('member_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $member = Member::find($id);

        return view('admin.members.cancell', compact('member'));
    }

    public function activationCancellProcess(Request $request)
    {
        abort_unless(\Gate::allows('member_show'), 403);
        if ($request->has('status')) {
            //get order relate to activation
            $order_activation = Order::selectRaw("id,count(id) as num_rows")
                ->where('customers_activation_id', '=', $request->id)
                ->where('type', '=', 'activation_member')
                ->get();
            $order_non_activation = Order::selectRaw("id,count(id) as num_rows")
                ->where('customers_id', '=', $request->id)
                ->where('type', '!=', 'activation_member')
                ->where('status', '!=', 'closed')
                ->get();
            // return $order_non_activation;
            if ($order_activation[0]->num_rows == 1 && $order_non_activation[0]->num_rows == 0) {
                $order = Order::find($order_activation[0]->id);
                $member = Customer::find($order->customers_activation_id);
                $member_3hus = Customer::where('owner_id', '=', $member->id)
                    ->where('id', '!=', $member->id)
                    ->where('status', '!=', 'closed')
                    ->where('ref_bin_id', '>', 0)
                    ->get();
                if (count($member_3hus)==0) {
                    $order->status = 'closed';
                    $order->status_delivery = 'pending';
                    $order->save();
                    //if order type activation_member close member status
                    if ($order->type == 'activation_member' && $order->activation_type_id_old == 0) {
                        $member->phone = $member->phone . "xxx";
                        $member->email = $member->email . "xxx";
                        $member->status = 'closed';
                        $member->slot_x = null;
                        $member->slot_y = null;
                        $member->ref_bin_id = null;
                        $member->save();
                    }

                    //reset points
                    $orderpoints = OrderPoint::where('orders_id', $order->id)->get();
                    foreach ($orderpoints as $key => $orderpoint) {
                        $orderpoint_upd = OrderPoint::find($orderpoint->id);
                        $orderpoint_upd->status = 'onhold';
                        $orderpoint_upd->save();
                    }
                    //reset pivot products details
                    $ids = $order->productdetails()->allRelatedIds();
                    foreach ($ids as $products_id) {
                        $order->productdetails()->updateExistingPivot($products_id, ['status' => 'onhold']);
                    }
                    //reset pairing
                    $pairingqueues = BVPairingQueue::where('order_id', $order->id)->get();
                    foreach ($pairingqueues as $key => $pairingqueue) {
                        $pairingqueue_upd = BVPairingQueue::find($pairingqueue->id);
                        $pairingqueue_upd->status = 'close';
                        $pairingqueue_upd->save();
                    }
                    //reset ledger
                    $ledger = Ledger::find($order->ledgers_id);
                    $ledger->status = 'closed';
                    $ledger->save();

                    //push notif to member
                    $user = Customer::find($order->customers_id);
                    //onesignal
                    $id_onesignal = $user->id_onesignal;
                    $memo = 'Hallo ' . $user->name . ', Order ' . $order->code . ' telah dibatalkan.';
                    $register = date("Y-m-d");
                    //store to logs_notif
                    $data = ['register' => $register, 'customers_id' => $order->customers_id, 'memo' => $memo];
                    $logs = LogNotif::create($data);
                    //push notif
                    if (!empty($id_onesignal)) {
                        $this->onesignal_client->sendNotificationToUser(
                            $memo,
                            $id_onesignal,
                            $url = null,
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );}
                    //push notif to agent
                    $user_os = Customer::find($order->agents_id);
                    $id_onesignal = $user_os->id_onesignal;
                    $memo = 'Hallo ' . $user_os->name . ', Order ' . $order->code . ' telah dibatalkan.';
                    $register = date("Y-m-d");
                    //store to logs_notif
                    $data = ['register' => $register, 'customers_id' => $order->agents_id, 'memo' => $memo];
                    $logs = LogNotif::create($data);
                    //push notif
                    OneSignal::sendNotificationToUser(
                        $memo,
                        $id_onesignal,
                        $url = null,
                        $data = null,
                        $buttons = null,
                        $schedule = null
                    );
                    //response
                    $message = 'Aktivasi Member: ' . $member->code . ' - ' . $member->name . ' Sudah Dibatalkan.';
                    return back()->withError($message)->withInput();
                } else {
                    $member = Customer::find($request->id);
                    $message = 'Pembatalan Aktivasi Member: ' . $member->code." - ".count($member_3hus) ." - ".$order_activation[0]->num_rows. " - ".$order_non_activation[0]->num_rows.  ' - ' . $member->name . ' Gagal Dibatalkan. Member Terkait Member Lain.';
                    return redirect()->route('admin.members.index')->withError($message);
                    // return back()->withError($message)->withInput();
                }} else {
                $member = Customer::find($request->id);
                $message = 'Pembatalan Aktivasi Member: ' . $member->code . ' - ' . $member->name . ' Gagal Dibatalkan.';
                return redirect()->route('admin.members.index')->withError($message);
                // return back()->withError($message)->withInput();
            }
        }
    }

    public function unblock($id)
    {
        abort_if(\Gate::denies('member_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $member = Member::find($id);

        return view('admin.members.unblock', compact('member'));
    }

    public function unblockProcess(Request $request)
    {
        abort_unless(\Gate::allows('member_show'), 403);
        if ($request->has('status_block')) {
            //get
            $member = Member::find($request->input('id'));
            //update
            $member->status_block = '0';
            $member->save();
        }
        return redirect()->route('admin.members.index');

    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('member_access'), 403);

        //$from = !empty($request->from) ? $request->from : date('Y-m-01');
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to : date('Y-m-d');

        if ($request->ajax()) {
            if (isset($request->status) && $request->status != "active") {
                $query = Member::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
                    ->whereBetween(DB::raw('DATE(customers.created_at)'), [$from, $to])
                    ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
                    ->where(function ($qry) {
                        $qry->where('customers.type', '=', 'member')
                            ->orWhere('customers.def', '=', '1');
                    })
                    ->orderBy("customers.activation_at", "DESC")
                    ->groupBy('customers.id')
                    ->FilterInput()
                    ->get();
            } else {
                $query = Member::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
                    ->whereBetween(DB::raw('DATE(customers.activation_at)'), [$from, $to])
                    ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
                    ->where(function ($qry) {
                        $qry->where('customers.type', '=', 'member')
                            ->orWhere('customers.def', '=', '1');
                    })
                    ->orderBy("customers.activation_at", "DESC")
                    ->groupBy('customers.id')
                    ->FilterInput()
                    ->get();
            }
            foreach ($query as $key => $value) {
                $query[$key]->ref_link = env('APP_URL') . "/member?ref=" . Hashids::encode($value->id);
            }
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'member_show';
                $editGate = 'member_edit';
                $deleteGate = 'member_delete';
                $crudRoutePart = 'members';

                return view('partials.datatablesMembers', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            // $table->editColumn('no', function ($row) {
            //     return $row->index ? $row->index : "";
            // });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->activation_at ? $row->activation_at : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('email', function ($row) {
                return $row->email ? $row->email : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('saldo', function ($row) {
                return $row->amount_balance ? number_format($row->amount_balance, 2) : 0;
            });

            $table->editColumn('ref_link', function ($row) {
                return $row->ref_link ? $row->ref_link : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            //     $table->filter(function ($instance) use ($request) {
            //        if ($request->get('status') == '0' || $request->get('status') == '1') {
            //            $instance->where('status', $request->get('status'));
            //        }
            //        if (!empty($request->get('search'))) {
            //             $instance->where(function($w) use($request){
            //                $search = $request->get('search');
            //                $w->orWhere('name', 'LIKE', "%$search%")
            //                ->orWhere('email', 'LIKE', "%$search%");
            //            });
            //        }
            //    });

            return $table->make(true);
        }

        // $orders_id=104;
        // $agents_id=42;
        // $order = Order::with('products')
        // ->with('customers')
        // ->get()->find($orders_id);
        // $agent = Customer::find($agents_id);

        // return view('email.order')
        // ->with([
        //     'order' => $order,
        //     'agent' => $agent,
        // ]);

        $members = Member::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
            ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->where('customers.type', '=', 'member')
            ->orderBy('customers.activation_at')
            ->groupBy('customers.id')
            ->get();

        $out_val = array();
        foreach ($members as $key => $value) {
            $members[$key]->ref_link = env('APP_URL') . "/member?ref=" . Hashids::encode($value->id);
        }

        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();

        $ref_def_link = env('APP_URL') . "/member?ref=" . Hashids::encode($ref_def_id[0]->id);

        return view('admin.members.index', compact('members', 'ref_def_link'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('member_create'), 403);

        $products = Product::where('type', '=', 'package')
            ->get();
        $referals = Customer::select('*')
            ->where('type', 'member')
            ->orWhere('def', '=', '1')
            ->get();
        $agents = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();

        return view('admin.members.create', compact('products', 'referals', 'agents'));
    }

    public function store(StoreMemberRequest $request)
    {
        abort_unless(\Gate::allows('member_create'), 403);

        //get point sponsor
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->input('customers_id'))
            ->where('type', '=', 'D')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->input('customers_id'))
            ->where('type', '=', 'C')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        //get package price & cogs
        $package = Product::select('price', 'cogs')
            ->where('id', '=', $request->input('package_id'))
            ->get();
        $package = json_decode($package, false);
        $cogs_total = $package[0]->cogs;
        $total = $package[0]->price;

        //get stock agent, loop package
        $test_out = 'pb' . $points_balance . ' tot' . $total . '<br>';
        $stock_status = true;
        $package_items = Package::with('products')
            ->where('id', $request->input('package_id'))
            ->get();
        $package_items = json_decode($package_items, false);
        $package_items = $package_items[0]->products;
        //loop items
        foreach ($package_items as $key => $value) {
            //get qty package product & compare sum stock
            $stock_debit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                ->where('type', '=', 'D')
                ->where('status', '=', 'onhand')
                ->where('products_id', $value->id)
                ->sum('quantity');
            $stock_credit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                ->where('type', '=', 'C')
                ->where('status', '=', 'onhand')
                ->where('products_id', $value->id)
                ->sum('quantity');
            $stock_balance = $stock_debit - $stock_credit;
            $test_out .= $value->id . " - " . $stock_balance . " - " . $value->pivot->quantity . '<br>';
            if ($stock_balance < $value->pivot->quantity) {
                $stock_status = false;
            }
        }

        //compare total to point belanja
        if ($points_balance >= $total && $stock_status == true) {
            /*set member */
            $password_def = bcrypt('b2e5l709g');
            $data = array_merge($request->all(), ['status' => 'active', 'type' => 'member', 'password' => $password_def, 'parent_id' => $request->input('customers_id'), 'ref_id' => $request->input('customers_id'), 'ref_bin_id' => $request->input('customers_id')]);
            $member = Member::create($data);
            /*set order*/
            //set def
            $referal_id = $request->input('customers_id');
            $agents_id = $request->input('agents_id');
            $warehouses_id = 1;
            //set order
            $data = array_merge($request->all(), ['memo' => 'Transaksi Paket dari Pendaftaran Member', 'total' => $total, 'type' => 'agent_sale', 'status' => 'approved', 'ledgers_id' => 0, 'customers_id' => $referal_id, 'payment_type' => 'point']);
            $order = Order::create($data);
            //set order products
            $order->products()->attach($request->input('package_id'), ['quantity' => 1, 'price' => $total]);
            //set order order details (inventory stock)
            $package_items = Package::with('products')
                ->where('id', $request->input('package_id'))
                ->get();
            $package_items = json_decode($package_items, false);
            $package_items = $package_items[0]->products;
            //loop items
            foreach ($package_items as $key => $value) {
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $member->id]);
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
            }

            //set trf points
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'customers_id' => $agents_id]);
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'customers_id' => $referal_id]);

            return redirect()->route('admin.members.index');
        } else {
            return back()->withError('Poin atau Stok Barang Tidak Mencukupi!')->withInput();
        }

    }

    public function edit(Member $member)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        $products = Product::where('type', '=', 'package')
            ->get();
        $referals = Customer::select('*')
            ->where('type', 'member')
            ->orWhere('def', '=', '1')
            ->get();
        $agents = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();

        return view('admin.members.edit', compact('member', 'products', 'referals', 'agents'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        $member->update($request->all());

        return redirect()->route('admin.members.index');
    }

    public function show(Member $member)
    {
        abort_unless(\Gate::allows('member_show'), 403);

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

        return view('admin.members.show', compact('member', 'careertypes', 'members'));
    }

    public function destroy(Member $member)
    {
        abort_unless(\Gate::allows('member_delete'), 403);

        //check if pending
        if ($member->status == 'pending') {
            $orders = Order::where('customers_id', $member->id)
                ->orWhere('customers_activation_id', $member->id)
                ->get();
            foreach ($orders as $key => $order) {
                if ($order->ledgers_id > 0) {
                    $ledger = Ledger::find($order->ledgers_id);
                    $ledger->accounts()->detach();
                    $ledger->delete();
                }
                $order->products()->detach();
                $order->productdetails()->detach();
                $order->points()->detach();
                //update pivot BVPairingQueue
                $pairingqueues = BVPairingQueue::where('order_id', $order->id)->get();
                foreach ($pairingqueues as $key => $pairingqueue) {
                    $pairingqueue_upd = BVPairingQueue::find($pairingqueue->id);
                    $pairingqueue_upd->delete();
                }
                $order->delete();
            }
            // $member->delete();
            $member->phone = $member->phone . "xxx";
            $member->email = $member->email . "xxx";
            $member->status = 'closed';
            $member->slot_x = null;
            $member->slot_y = null;
            $member->ref_bin_id = null;
            $member->save();
        } else {
            return back()->withError('Gagal Delete, Member Active!');
        }

        return back();
    }

    public function massDestroy(MassDestroyMemberRequest $request)
    {
        // Member::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return back()->withError('Gagal Delete, Member Active!');
    }
}
