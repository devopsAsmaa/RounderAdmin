<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\TechnicianRequest;

/** التحقق من مدخلات تأكيد إتمام الطلب بالصورة والرمز. */
class ApiTechnicianConfirmOrderRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [
            'otp' => ['required', 'string', 'max:10'],
            'image_after' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'note' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'رمز التحقق المستلم من العميل مطلوب.',
            'image_after.required' => 'صورة إتمام العمل بعد التنفيذ مطلوبة.',
            'image_after.image' => 'يجب رفع ملف صورة صالح.',
            'image_after.mimes' => 'صيغة الصورة يجب أن تكون: jpeg, png, jpg, webp.',
            'image_after.max' => 'حجم الصورة لا يجب أن يتجاوز 10 ميجابايت.',
        ];
    }
}
