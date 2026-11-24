<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;

/** التحقق من مدخلات تسجيل دخول الفني قبل تشغيل الخدمة. */
class ApiTechnicianLoginRequest extends PublicRequest
{
    protected function prepareForValidation(): void
    {
        $raw = (string) ($this->input('mobile') ?? $this->input('phone') ?? '');
        $cleaned = preg_replace('/[^\d+]/', '', $raw);

        if (str_starts_with($cleaned, '+966')) {
            $cleaned = '0' . substr($cleaned, 4);
        } elseif (str_starts_with($cleaned, '00966')) {
            $cleaned = '0' . substr($cleaned, 5);
        } elseif (str_starts_with($cleaned, '966')) {
            $cleaned = '0' . substr($cleaned, 3);
        } elseif (strlen($cleaned) === 9 && str_starts_with($cleaned, '5')) {
            $cleaned = '0' . $cleaned;
        }

        if (!empty($cleaned)) {
            $this->merge([
                'mobile' => $cleaned,
                'phone' => $cleaned,
            ]);
        }
    }

    /**
     * قواعد التحقق من صحة بيانات تسجيل الدخول.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required_without:email', 'nullable', 'string', 'regex:/^05[0-9]{8}$/'],
            'email' => ['required_without:mobile', 'nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
        ];
    }

    /**
     * رسائل التحقق المخصصة.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'mobile.required_without' => 'رقم الجوال أو البريد الإلكتروني مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ];
    }
}
