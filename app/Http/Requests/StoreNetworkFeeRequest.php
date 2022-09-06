<?php

namespace App\Http\Requests;

use App\NetworkFee;
use Illuminate\Foundation\Http\FormRequest;

class StoreNetworkFeeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('networkfee_create');
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
            ],
        ];
    }
}
