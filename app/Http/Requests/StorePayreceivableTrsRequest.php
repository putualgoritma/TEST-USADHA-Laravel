<?php

namespace App\Http\Requests;

use App\PayreceivableTrs;
use Illuminate\Foundation\Http\FormRequest;

class StorePayreceivableTrsRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('payable_create');
    }

    public function rules()
    {
        return [
            'payreceivable_id' => [
                'required',
            ],
        ];
    }
}
