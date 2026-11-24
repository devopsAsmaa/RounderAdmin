<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\TechnicianRequest;
use Illuminate\Validation\Rule;

/** التحقق من فلاتر قائمة طلبات الفني. */
class ApiTechnicianOrderIndexRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [
            'status_id' => ['nullable', 'integer'],
            'status_code' => ['nullable', 'string', 'max:50'],
            'tab' => ['nullable', 'string', Rule::in(['active', 'completed', 'all'])],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
