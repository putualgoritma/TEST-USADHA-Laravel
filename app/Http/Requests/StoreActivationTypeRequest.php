<?php

namespace App\Http\Requests;

use App\ActivationType;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivationTypeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('activation_type_create');
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
            ],
        ];
    }
}
