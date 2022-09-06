<?php

namespace App\Http\Requests;

use App\Martregister;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyMartregisterRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('martregister_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:martregisters,id',
        ];
    }
}
