<?php

namespace App\Http\Requests;

use App\Careertype;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCareertypeRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('careertype_edit');
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
