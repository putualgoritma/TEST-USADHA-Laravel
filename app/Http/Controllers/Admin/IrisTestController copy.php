<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IrisTestController extends Controller
{
    public function apiCurl($url, $key, $data)
    {
        // set username and password
        $username = $key;
        $password='';

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept : application/json'
        ));


        // curl_setopt($s,CURLOPT_POST,true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_USERPWD, "$username:$password"); 
        // execute!
        $response = curl_exec($curl);
        $e = curl_error($curl);
        // if($e = curl_error($curl)){
        //     return $e;
        // }else{
        //     return ($response);
        // }
        // close the connection, release resources used
        curl_close($curl);
            dd(json_decode($response));

    }

    public function createPayouts()
    {
        $data =[
            "payouts" => [
                [
                        "beneficiary_name"=> "testtest22222222222",
                        "beneficiary_account"=> "27101998",
                        "beneficiary_bank"=> "bni",
                        "beneficiary_email"=> "fajarprayoga23@gmail.com",
                        "amount"=> "100000.00",
                        "notes"=> "Payout April 17"
                ]
          
            ]
       ];

        // $data = "title: 'foo',
        // body: 'bar',
        // userId: 1,";

        $data = json_encode($data);
        // $data = json_decode($data);
        // // dd($data->payouts[0]->notes);
        $this->apiCurl('https://app.sandbox.midtrans.com/iris/api/v1/account_validation?bank=mandiri&account=1111222233333', 'IRIS-730d9ba3-f804-4419-a5d4-5fa6f1039fe3', $data);

        // dd(json));
    }

}