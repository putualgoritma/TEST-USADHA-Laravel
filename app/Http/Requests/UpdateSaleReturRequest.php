<?php

namespace App\Http\Requests;

use App\Order;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateSaleReturRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('saleretur_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'customers_id' => [
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
