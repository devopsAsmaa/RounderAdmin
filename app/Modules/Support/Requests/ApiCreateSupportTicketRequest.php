<?php

namespace App\Modules\Support\Requests;

use App\Core\Http\Requests\ResidentRequest;

class ApiCreateSupportTicketRequest extends ResidentRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('message') && !$this->has('description')) {
            $this->merge(['description' => $this->input('message')]);
        }
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'نوع المشكلة أو التصنيف مطلوب.',
            'subject.required' => 'موضوع الرسالة مطلوب.',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 190 حرفاً.',
            'description.required' => 'شرح المشكلة مطلوب.',
            'description.max' => 'نص الرسالة يجب ألا يتجاوز 5000 حرف.',
            'attachment.image' => 'يجب أن يكون المرفق صورة بصيغة صالحة.',
            'attachment.mimes' => 'صيغ الصور المسموحة هي PNG و JPG فقط.',
            'attachment.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }
}
