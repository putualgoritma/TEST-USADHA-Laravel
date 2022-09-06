<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreMemberRegRequest extends FormRequest
{
    public function rules()
    {
        return [
            'register' => [
                'required',
            ],
            'name'      => 'required|min:5',
            'phone'      => 'required',
            'email'      => 'required',
            'address'      => 'required',
        ];
    }

    public function messages()
    {
        return [
            'register.required'          => 'Register wajib diisi.',
            'name.required'      => 'Nama wajib diisi.',
            'name.min'           => 'Nama minimal diisi dengan 5 karakter.',
            'phone.required'      => 'Telfon wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'address.required'      => 'Alamat wajib diisi.',
        ];
    }
}
