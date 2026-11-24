<?php

namespace App\Modules\Support\Requests;

use App\Core\Support\SaudiPhone;
use App\Modules\Support\Enums\SupportCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => SaudiPhone::normalize((string) $this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX],
            'email' => ['required', 'email:rfc', 'max:254'],
            'category' => ['required', 'string', Rule::in(array_column(SupportCategory::cases(), 'value'))],
            'subject' => ['required', 'string', 'min:3', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال الاسم بالكامل.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'phone.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'category.required' => 'يرجى اختيار تصنيف الاستفسار.',
            'subject.required' => 'عنوان الرسالة مطلوب.',
            'message.required' => 'يرجى كتابة تفاصيل رسالتك أو استفسارك.',
            'message.min' => 'نص الرسالة يجب ألا يقل عن 10 أحرف.',
            'attachment.max' => 'حجم المرفق يجب ألا يتجاوز 10 ميجابايت.',
            'attachment.mimes' => 'المرفق يجب أن يكون ملف PDF أو صورة (JPG, PNG).',
        ];
    }
}

