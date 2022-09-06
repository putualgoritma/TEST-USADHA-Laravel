<?php

namespace App\Http\Requests;

use App\Asset;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyAssetRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('asset_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:assets,id',
        ];
    }
}
