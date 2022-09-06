<?php

namespace App\Http\Requests;

use App\CogsAllocat;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyCogsAllocatRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('cogsallocat_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:cogs_allocats,id',
        ];
    }
}
