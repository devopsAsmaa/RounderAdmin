<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\TechnicianRequest;

/** التحقق من طلب إرسال رمز التحقق للعميل لإتمام الطلب. */
class ApiTechnicianSendOtpRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [];
    }
}
