<?php

namespace App\Http\Requests;

use App\Capital;
use Illuminate\Foundation\Http\FormRequest;

class StoreCapitalRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('capital_create');
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
