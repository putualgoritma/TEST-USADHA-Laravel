<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\City;
use App\Http\Controllers\Controller;
use App\Province;
use Illuminate\Http\Request;

class OngkirApiController extends Controller
{
    public function provinceCityTest(Request $request)
    {
        $city_from = City::where('id', '=', $request->from)
            ->first();
        $province_from = Province::where('id', '=', $city_from->province_id)
            ->first();
        $city_to = City::where('id', '=', $request->to)
            ->first();
        $province_to = Province::where('id', '=', $city_to->province_id)
            ->first();
        $from = $city_from->title . " " . $province_from->title;
        $to = $city_to->title . " " . $province_to->title;
        $from = $city_from->title . " " . $province_from->title;
        $to = $city_to->title . " " . $province_to->title;
        return $this->getGeocoding($from);
        if ($geocoding_from = $this->getGeocoding($from)) {
            if ($geocoding_to = $this->getGeocoding($to)) {
                $origin = str_replace(",","",$geocoding_from['lat']).",".str_replace(",","",$geocoding_from['lng']);
                $destination = str_replace(",","",$geocoding_to['lat']).",".str_replace(",","",$geocoding_to['lng']);
                return $origin."*".$destination;
                // if ($distance = $this->getDistance($origin, $destination)) {
                //     return response()->json([
                //         'success' => true,
                //         'distance' => $distance,
                //     ]);
                // }
            } else {
                return response()->json([
                    'success' => false,
                    'distance' => 0,
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'distance' => 0,
            ]);
        }
    }
    
    public function provinceCity(Request $request)
    {
        $city_from = City::where('id', '=', $request->from)
            ->first();
        $province_from = Province::where('id', '=', $city_from->province_id)
            ->first();
        $city_to = City::where('id', '=', $request->to)
            ->first();
        $province_to = Province::where('id', '=', $city_to->province_id)
            ->first();
        $from = $city_from->title . " " . $province_from->title;
        $to = $city_to->title . " " . $province_to->title;
        if ($geocoding_from = $this->getGeocoding($from)) {
            if ($geocoding_to = $this->getGeocoding($to)) {
                $origin = str_replace(",","",$geocoding_from['lat']).",".str_replace(",","",$geocoding_from['lng']);
                $destination = str_replace(",","",$geocoding_to['lat']).",".str_replace(",","",$geocoding_to['lng']);
                if ($distance = $this->getDistance($origin, $destination)) {
                    return response()->json([
                        'success' => true,
                        'distance' => $distance,
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'distance' => 0,
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'distance' => 0,
            ]);
        }
    }

    public function getGeocoding($address)
    {

        // https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=AIzaSyBxJpfNfWPonmRTm-TktgyaNEVyQxpBHd0
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=AIzaSyBxJpfNfWPonmRTm-TktgyaNEVyQxpBHd0';
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // tutup curl
        curl_close($ch);
        $hasil = [];
        // menampilkan hasil curl
        $data = json_decode($output);
        $status = $data->status;
        if ($status == "OK") {
            $hasil['lat'] = $data->results[0]->geometry->location->lat;
            $hasil['lng'] = $data->results[0]->geometry->location->lng;
            return $hasil;
        } else {
            return false;
        }
    }

    public function getDistance($origin, $destination)
    {

        // https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=-8.522808,115.268234&destinations=-8.6725072,115.154232&key=AIzaSyBxJpfNfWPonmRTm-TktgyaNEVyQxpBHd0
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=' . $origin . '&destinations=' . $destination . '&key=AIzaSyBxJpfNfWPonmRTm-TktgyaNEVyQxpBHd0';
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // tutup curl
        curl_close($ch);
        $hasil = [];
        // menampilkan hasil curl
        $data = json_decode($output);
        $status = $data->status;
        if ($status == "OK") {
            return $data->rows[0]->elements[0]->distance->value;
        } else {
            return false;
        }
    }
}
