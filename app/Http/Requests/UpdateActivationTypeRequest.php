<?php

namespace App\Http\Requests;

use App\ActivationType;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivationTypeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('activation_type_edit');
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
