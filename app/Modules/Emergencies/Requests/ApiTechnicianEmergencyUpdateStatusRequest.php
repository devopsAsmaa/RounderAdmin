<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\TechnicianRequest;
use Illuminate\Validation\Rule;

/** التحقق من تحديث حالة طلب الطوارئ من قبل الفني. */
class ApiTechnicianEmergencyUpdateStatusRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['on_the_way', 'resolved'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة طلب الطوارئ مطلوبة.',
            'status.in' => 'الحالة المحددة غير صالحة. الحالات المسموحة: on_the_way أو resolved.',
        ];
    }
}
