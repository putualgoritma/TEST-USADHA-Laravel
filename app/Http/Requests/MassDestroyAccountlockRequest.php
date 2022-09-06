<?php

namespace App\Http\Requests;

use App\Accountlock;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyAccountlockRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('accountlock_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:accountlocks,id',
        ];
    }
}
