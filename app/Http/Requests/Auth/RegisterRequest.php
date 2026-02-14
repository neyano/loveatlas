<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:' . User::class,
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/(?=.*[a-zA-Z])(?=.*[0-9])/',
            ],
            'display_name' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'ユーザー名は英数字とアンダースコアのみ使用できます',
            'username.unique' => 'このユーザー名は既に使われています',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.regex' => 'パスワードは英字と数字を両方含めてください',
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}
