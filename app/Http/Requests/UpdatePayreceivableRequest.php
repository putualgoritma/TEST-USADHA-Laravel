<?php

namespace App\Http\Requests;

use App\Payreceivable;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayreceivableRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('payable_edit');
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
