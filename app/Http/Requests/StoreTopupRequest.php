<?php

namespace App\Http\Requests;

use App\Topup;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreTopupRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('topup_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'customers_id' => [
                'required',
            ],
        ];
    }
}
