<?php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => ['required', 'email', Rule::exists('users', 'email')->withoutTrashed()],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ];
    }
}
