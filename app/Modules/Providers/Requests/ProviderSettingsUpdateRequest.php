<?php

namespace App\Modules\Providers\Requests;

use App\Core\Enums\SupportedLocale;
use App\Core\Support\SaudiPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * طلب التحقق من تحديث إعدادات وملف مزود الخدمة من بوابته الخاصة.
 */
class ProviderSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->providerProfile !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone') || $this->has('mobile')) {
            $raw = (string) ($this->input('phone') ?? $this->input('mobile') ?? '');
            $this->merge([
                'phone' => SaudiPhone::normalize($raw),
            ]);
        }

        if ($this->boolean('_service_ids_submitted') && !$this->exists('service_ids')) {
            $this->merge(['service_ids' => []]);
        }
    }

    public function rules(): array
    {
        $user = $this->user();
        $userId = $user->id;

        return [
            // بيانات الحساب
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, Rule::unique('users', 'phone')->ignore($userId)],
            'locale' => ['required', Rule::enum(SupportedLocale::class)],
            'password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],

            // بيانات المنشأة
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'trade_name' => ['nullable', 'string', 'min:2', 'max:160'],
            'provider_type' => ['nullable', 'string', 'max:80'],
            'years_of_experience' => ['nullable', 'integer', 'between:0,100'],

            // بيانات السجل والممثل النظامي
            'commercial_registration' => ['nullable', 'string', 'max:80'],
            'legal_representative' => ['nullable', 'string', 'max:160'],
            'legal_representative_id' => ['nullable', 'string', 'max:30'],

            // المستندات والمرفقات الرسمية
            'company_seal' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'commercial_registration_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'legal_representative_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'signed_annex_a' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'municipal_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // الخدمات
            'service_ids' => ['sometimes', 'array'],
            'service_ids.*' => ['integer', 'distinct', 'exists:services,id'],
            '_service_ids_submitted' => ['sometimes', 'accepted'],

            // إقرار الموافقة على العقد
            'accept_contract' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المسؤول مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 خانات على الأقل.',
            'password.regex' => 'كلمة المرور يجب أن تتضمن حرفاً كبيراً (A-Z) وحرفاً صغيراً (a-z) ورقماً (0-9).',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ];
    }
}

