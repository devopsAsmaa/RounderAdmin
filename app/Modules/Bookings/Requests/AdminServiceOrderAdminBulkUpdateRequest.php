<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\AdminRequest;

/** التحقق من مدخلات ServiceOrderAdminController::bulkUpdate قبل تشغيل الخدمة. */
class AdminServiceOrderAdminBulkUpdateRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'exists:bookings,id'],
            'booking_status_id' => ['nullable', 'integer', 'exists:booking_statuses,id'],
            'technician_id' => ['nullable'],
            'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
            'note' => ['nullable', 'string', 'max:2000'],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}
