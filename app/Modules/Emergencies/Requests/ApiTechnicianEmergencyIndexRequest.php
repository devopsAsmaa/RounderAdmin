<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\TechnicianRequest;
use Illuminate\Validation\Rule;

/** التحقق من فلاتر قائمة طلبات الطوارئ للفني. */
class ApiTechnicianEmergencyIndexRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [
            'status_id' => ['nullable', 'integer'],
            'status_code' => ['nullable', 'string', 'max:50'],
            'tab' => ['nullable', 'string', Rule::in(['all', 'active', 'in_progress', 'completed', 'resolved'])],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
