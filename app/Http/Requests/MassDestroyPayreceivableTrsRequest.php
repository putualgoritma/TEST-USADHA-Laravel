<?php

namespace App\Http\Requests;

use App\PayreceivableTrs;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyPayreceivableTrsRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('payable_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:payreceivables_trs,id',
        ];
    }
}
