<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\TechnicianRequest;
use Illuminate\Validation\Rule;

/** التحقق من تحديث حالة الطلب من قبل الفني. */
class ApiTechnicianUpdateStatusRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['in_progress', 'completed'])],
            'otp' => ['required_if:status,completed', 'nullable', 'string', 'max:10'],
            'image_after' => ['required_if:status,completed', 'nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الطلب مطلوبة.',
            'otp.required_if' => 'رمز التحقق المستلم من العميل مطلوب عند إتمام الطلب.',
            'image_after.required_if' => 'صورة إتمام العمل بعد التنفيذ مطلوبة عند إتمام الطلب.',
        ];
    }
}
