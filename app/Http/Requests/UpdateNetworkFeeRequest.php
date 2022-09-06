<?php

namespace App\Http\Requests;

use App\NetworkFee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNetworkFeeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('networkfee_edit');
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
