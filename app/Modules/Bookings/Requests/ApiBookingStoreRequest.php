<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use Carbon\Carbon;

/** التحقق من مدخلات BookingController::store قبل تشغيل الخدمة. */
class ApiBookingStoreRequest extends ResidentRequest
{
    /**
     * تحويل تاريخ الموعد ISO 8601 إلى صيغة التاريخ المتوافقة مع قاعدة البيانات.
     */
    protected function prepareForValidation(): void
    {
        $scheduledAt = $this->input('scheduled_at');
        if ($scheduledAt) {
            try {
                $this->merge(['scheduled_at' => Carbon::parse($scheduledAt)->format('Y-m-d H:i:s')]);
            } catch (\Throwable) {}
        }
    }

    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'home_id' => ['required', 'integer', 'exists:homes,id',
                Rule::exists('resident_homes', 'home_id')->where('user_id', $this->user()->id)],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
