<?php

namespace App\Http\Requests;

use App\PayreceivableTrs;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayreceivableTrsRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('payable_edit');
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
