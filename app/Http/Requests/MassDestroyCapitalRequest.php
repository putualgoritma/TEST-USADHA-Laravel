<?php

namespace App\Http\Requests;

use App\Capital;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyCapitalRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('capital_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:capitals,id',
        ];
    }
}
