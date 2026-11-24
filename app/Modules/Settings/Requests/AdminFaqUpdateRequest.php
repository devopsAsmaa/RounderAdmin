<?php

namespace App\Modules\Settings\Requests;

use App\Modules\Settings\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminFaqUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(array_keys(Faq::categories()))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'question_ar' => ['required', 'string', 'min:5', 'max:255'],
            'answer_ar' => ['required', 'string', 'min:10'],
            'question_en' => ['nullable', 'string', 'max:255'],
            'answer_en' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'يرجى اختيار تصنيف السؤال.',
            'category.in' => 'التصنيف المختار غير صالح.',
            'question_ar.required' => 'نص السؤال باللغة العربية مطلوب.',
            'question_ar.min' => 'نص السؤال يجب ألا يقل عن 5 أحرف.',
            'answer_ar.required' => 'نص الإجابة باللغة العربية مطلوب.',
            'answer_ar.min' => 'نص الإجابة يجب ألا يقل عن 10 أحرف.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => (int) ($this->input('sort_order', 0) ?: 0),
        ]);
    }
}

