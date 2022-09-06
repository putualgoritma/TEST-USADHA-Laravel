<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomerApi;
use App\Http\Controllers\Controller;
use App\Martregister;
use App\Traits\TraitModel;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MartregisterApiController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function join(Request $request)
    {
        $input['customer_id'] = $request['customer_id'];
        $input['type'] = 'join';
        $input['status'] = 'pending';

        try {
            $martregister = Martregister::create($input);
            return response()->json([
                'success' => true,
                'data' => $martregister,
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Request Join Gagal.',
            ], 401);
        }
    }

    public function upgrade(Request $request)
    {
        $input['customer_id'] = $request['customer_id'];
        $input['type'] = 'upgrade';
        $input['status'] = 'pending';

        try {
            $martregister = Martregister::create($input);
            return response()->json([
                'success' => true,
                'data' => $martregister,
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Request Upgrade Gagal.',
            ], 401);
        }
    }

    public function registerdownline(Request $request)
    {
        $input['customer_id'] = $request['customer_id'];
        $input['referal_id'] = $request['referal_id'];
        $input['password'] = $request['password'];
        $input['email'] = $request['email'];
        $input['phone'] = $request['phone'];
        $input['address'] = $request['address'];
        $input['name'] = $request['name'];
        $input['type'] = 'registerdownline';
        $input['status'] = 'pending';

        if (CustomerApi::where('email', '=', $request['email'])->count() == 0 && CustomerApi::where('phone', '=', $request['phone'])->count() == 0) {

            try {
                $martregister = Martregister::create($input);
                return response()->json([
                    'success' => true,
                    'data' => $martregister,
                ]);
            } catch (QueryException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => $input,
                ], 401);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => $input,
            ], 401);
        }
    }
}
