<?php

namespace App\Http\Requests;

use App\Accountlock;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountlockRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('accountlock_create');
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
            ],
        ];
    }
}
