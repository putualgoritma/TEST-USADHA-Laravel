<?php

namespace App\Http\Requests;

use App\Member;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class MassDestroyMemberRequest extends FormRequest
{
    public function authorize()
    {
        return abort_if(Gate::denies('member_delete'), 403, '403 Forbidden') ?? true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:customers,id',
        ];
    }
}
