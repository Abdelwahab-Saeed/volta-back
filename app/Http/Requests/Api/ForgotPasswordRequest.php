<?php
namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::exists('users', 'email')->withoutTrashed()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'إذا كان هذا البريد موجوداً في نظامنا، فقد تم إرسال رابط استعادة كلمة المرور.',
        ];
    }
}
