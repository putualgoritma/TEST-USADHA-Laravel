<?php

namespace App\Http\Requests;

use App\Payreceivable;
use Illuminate\Foundation\Http\FormRequest;

class StorePayreceivableRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('payable_create');
    }

    public function rules()
    {
        return [
            'customer_id' => [
                'required',
            ],
        ];
    }
}
