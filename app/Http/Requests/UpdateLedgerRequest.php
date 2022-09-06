<?php

namespace App\Http\Requests;

use App\Ledger;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateLedgerRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('ledger_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'customers_id' => [
                'required',
            ],
            'accounts.*'    => [
                'integer',
            ],
            'accounts'      => [
                'array',
            ],
        ];
    }
}
