<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\IrisService;
class IrisTestController extends Controller
{
    public $url =  'https://app.midtrans.com/iris/api/v1/payouts';
    public $key =  "IRIS-1a335c7a-49b7-4ee5-a0ca-05a65e9b393c : '' ";

    public function postIris($url, $key, $data)
    {
        // config('iris.irisBaseUrl');
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL =>$url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>$data,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            // 'Authorization: Basic SVJJUy03MzBkOWJhMy1mODA0LTQ0MTktYTVkNC01ZmE2ZjEwMzlmZTM6'
        ),
        // CURLOPT_USERPWD => "IRIS-dabe2962-7986-4810-ace2-35cbbc3248fd:null",
        ));
        curl_setopt($curl, CURLOPT_USERPWD,$key); 
        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function validasiAccountBank()
    {
        $curl = curl_init();
        $data = array(
            'bank' => 'mandiri',
            'account' => '1111222233333'            
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => array('bank' => 'mandiri','account' => '1111222233333'),
            CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            // 'Authorization: Basic SVJJUy1kYWJlMjk2Mi03OTg2LTQ4MTAtYWNlMi0zNWNiYmMzMjQ4ZmQ6'
            ),
        ));
        curl_setopt($curl, CURLOPT_USERPWD,$key); 

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }


    public function createPayouts()
    {
        // $data = '{
        //     "payouts": [
        //         {
        //             "beneficiary_name": "send from php test 2",
        //             "beneficiary_account": "27101998",
        //             "beneficiary_bank": "bni",
        //             "beneficiary_email": "fajarprayoga23@gmail.com",
        //             "amount": "100000.00",
        //             "notes": "Payout April 17"
        //         }
        //     ]
        // }';

        // $response = $this->postIris($url, $key, $data);

        $iris = new IrisService();

        dd($iris);

    }
}