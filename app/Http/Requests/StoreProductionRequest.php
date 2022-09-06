<?php

namespace App\Http\Requests;

use App\Production;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreProductionRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
            ],
            'memo' => [
                'required',
            ],
            'register' => [
                'required',
            ],
            'products.*'    => [
                'integer',
            ],
            'products'      => [
                'array',
            ],
        ];
    }
}
