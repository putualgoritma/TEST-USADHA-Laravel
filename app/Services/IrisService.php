<?php
namespace App\Services;
class IrisService {
    var $baseUrl = 'https://app.midtrans.com/iris/';//https://app.sandbox.midtrans.com/iris/
    var $key_approver = 'IRIS-75cabe75-c847-4e3f-9a1d-9eee97796135';//IRIS-dabe2962-7986-4810-ace2-35cbbc3248fd
    var $key_creator = 'IRIS-1a335c7a-49b7-4ee5-a0ca-05a65e9b393c';//IRIS-730d9ba3-f804-4419-a5d4-5fa6f1039fe3
    var $password = '';

    public function createPayouts($data=[])
    {
        if(count($data) > 0){
            $data = json_encode($data);
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL =>$this->baseUrl.'api/v1/payouts',
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
    
            $key =  "IRIS-1a335c7a-49b7-4ee5-a0ca-05a65e9b393c : '' ";
            curl_setopt($curl, CURLOPT_USERPWD,$this->key_creator.':'.$this->password); 
            $response = curl_exec($curl);
    
            curl_close($curl);
            return json_decode($response);
        }else{
            return 'Mohon isi Data';
        }
    }

    public function validasiBank($data=[])
    {
        $curl = curl_init();
        $key =  "IRIS-1a335c7a-49b7-4ee5-a0ca-05a65e9b393c : '' ";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl."api/v1/account_validation?bank=".$data['bank']."&account=".$data['account'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            // CURLOPT_POSTFIELDS => array('bank' => 'mandiri','account' => '1111222233333'),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                // 'Authorization: Basic SVJJUy1kYWJlMjk2Mi03OTg2LTQ4MTAtYWNlMi0zNWNiYmMzMjQ4ZmQ6'
            ),
        ));
        curl_setopt($curl, CURLOPT_USERPWD,$this->key_approver.':'.$this->password); 
        $response = curl_exec($curl);
        $e = curl_error($curl);
        curl_close($curl);

        // dd($response);
        return json_decode($response);
    }

    public function approved($data)
    {
        $curl = curl_init();
        // $data = json_encode($)
        // if(count($data) > 0){
            $data = json_encode($data);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->baseUrl.'api/v1/payouts/approve',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    // 'Authorization: Basic SVJJUy1kYWJlMjk2Mi03OTg2LTQ4MTAtYWNlMi0zNWNiYmMzMjQ4ZmQ6aXIyQjEjJXM='
                ),
            ));

            curl_setopt($curl, CURLOPT_USERPWD,$this->key_approver.':'.$this->password); 
            
            $response = curl_exec($curl);
            
            // curl_close($curl);
            return json_decode($response);
        // }else{
        //     return 'Mohon Isi Data Anda';
        // }
    }
}