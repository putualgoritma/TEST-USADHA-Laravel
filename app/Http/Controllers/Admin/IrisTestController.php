<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\IrisService;
class IrisTestController extends Controller
{
    // public $url =  'https://app.sandbox.midtrans.com/iris/api/v1/payouts';
    // public $key =  "IRIS-730d9ba3-f804-4419-a5d4-5fa6f1039fe3 : '' ";

    public function createPayouts()
    {
        $data = [
            "payouts"=> [
                [
                    "beneficiary_name"=> "send from php test class obetc perbaiki",
                    "beneficiary_account"=> "27101998",
                    "beneficiary_bank"=> "bni",
                    "beneficiary_email"=> "fajarprayoga23@gmail.com",
                    "amount"=> "100000.00",
                    "notes"=> "Payout April 17"
                ]
            ]
        ];

        $iris = new IrisService();

        $res = $iris->createPayouts($data);

        dd($res);

    }


    public function checkValidasiBank()
    {
        $data = array(
            'bank' => 'mandiri',
            'account' => '1111222233333'
        );

        $iris = new IrisService();

        $res = $iris->validasiBank($data);

        dd($res);

    }

}