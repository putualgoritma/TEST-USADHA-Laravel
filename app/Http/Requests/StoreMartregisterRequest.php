<?php

namespace App\Http\Requests;

use App\Martregister;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreMartregisterRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('martregister_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => [
                'required',
            ],
        ];
    }
}
