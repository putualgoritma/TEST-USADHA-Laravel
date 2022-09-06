<?php

namespace App\Http\Requests;

use App\Capital;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCapitalRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('capital_edit');
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
