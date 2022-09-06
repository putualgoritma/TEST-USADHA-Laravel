<?php

namespace App\Http\Requests;

use App\Account;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyAccountRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('account_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:accounts,id',
        ];
    }
}
