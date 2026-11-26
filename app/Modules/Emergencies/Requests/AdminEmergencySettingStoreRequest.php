<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;

class AdminEmergencySettingStoreRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regions', 'id'),
                Rule::unique('emergency_settings', 'region_id'),
            ],
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
            'region_id.required' => 'يرجى اختيار المنطقة.',
            'region_id.exists' => 'المنطقة المحددة غير موجودة بالنظام.',
            'region_id.unique' => 'تم تعيين إعدادات طوارئ لهذه المنطقة مسبقاً، يمكنك تعديلها مباشرة.',
            'price.required' => 'حقل سعر خدمة الطوارئ مطلوب.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية صحيحة.',
            'price.min' => 'لا يمكن أن يكون السعر أقل من 0.',
        ];
    }
}
