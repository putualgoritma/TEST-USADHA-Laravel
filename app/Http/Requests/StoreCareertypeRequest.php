<?php

namespace App\Http\Requests;

use App\Careertype;
use Illuminate\Foundation\Http\FormRequest;

class StoreCareertypeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('careertype_create');
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
