<?php

namespace App\Http\Requests;

use App\Ledger;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreLedgerRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('ledger_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'register' => [
                'required',
            ],
            'title'      => 'required|min:5',
        ];
    }

    public function messages()
    {
        return [
            'register.required'          => 'Register wajib diisi.',
            'title.required'      => 'Title wajib diisi.',
            'title.min'           => 'Title minimal diisi dengan 5 karakter.'
        ];
    }
}
