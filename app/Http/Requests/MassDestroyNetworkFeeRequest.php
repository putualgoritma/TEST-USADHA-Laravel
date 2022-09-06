<?php

namespace App\Http\Requests;

use App\NetworkFee;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyNetworkFeeRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('networkfee_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:network_fees,id',
        ];
    }
}
