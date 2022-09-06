<?php

namespace App\Http\Requests;

use App\Martregister;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateMartregisterRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('martregister_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
