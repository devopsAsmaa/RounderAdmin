<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\AdminRequest;

class AdminEmergencySettingUpdateRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'cancellation_fee' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'estimated_time_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => 'حقل سعر خدمة الطوارئ مطلوب.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية صحيحة.',
            'price.min' => 'لا يمكن أن يكون السعر أقل من 0.',
        ];
    }
}
