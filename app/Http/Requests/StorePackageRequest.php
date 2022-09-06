<?php

namespace App\Http\Requests;

use App\Package;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePackageRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('package_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required'          => 'Nama wajib diisi.'
        ];
    }
}
