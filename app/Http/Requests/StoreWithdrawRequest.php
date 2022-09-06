<?php

namespace App\Http\Requests;

use App\Withdraw;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreWithdrawRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('withdraw_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
