<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Member;
use App\Traits\TraitModel;
use App\Http\Requests\StoreMemberRegRequest;
use App\Mail\MemberEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\QueryException;

class MembersController extends Controller
{
    use TraitModel;
    
    public function index(Request $request)
    {
        $products = Product::where('type', '=', 'package')
        ->get();
        $referals = Member::select('*')
            ->where('type', 'member')
            ->orWhere('def', '=', '1')    
            ->get();
        $agents = Member::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();

        if ($request->has('ref')){            
            $referals_id= \Hashids::decode($request->get('ref'))[0];
        }else{
            $ref_def_id = Member::select('id')
            ->Where('def', '=', '1')    
            ->get();
            $referals_id=$ref_def_id[0]->id;
        }   
        
        return view('members.index', compact('products','referals_id','agents'));
    }

    public function store(StoreMemberRegRequest $request)
    {
        $last_code=$this->mbr_get_last_code();
        $code=acc_code_generate($last_code,8,3);
        $password=passw_gnr(7);
        $password_ency=bcrypt($password);
        $data=array_merge($request->all(), ['type' => 'member','status' => 'pending','code' => $code,'password' => $password_ency,'parent_id' => $request->input('customers_id'),'ref_id' => $request->input('customers_id')]);    
        
        //check ref_id
        $ref_row = Member::find($request->input('customers_id'));
        if ($ref_row->status!='active') {
            return response()->json([
                'success' => false,
                'message' => 'Register Gagal, Status Referal belum Activasi.',
            ], 401);
        }

        try {
            $customer=Member::create($data);
        } catch (QueryException $exception) {
            return back()->withError('Duplicate Email or Phone Number ' . $request->input('email'))->withInput();
        }

        foreach ($customer as $key => $value) {
            $customer->password_raw=$password;
        }
        
        Mail::to($request->input('email'))->send(new MemberEmail($customer));

        return view('members.succes');
    }
}
