<?php

namespace App\Http\Requests;

use App\Accountlock;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountlockRequest extends FormRequest
{
    public function authorize()
    {
        return \Gate::allows('accountlock_edit');
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
