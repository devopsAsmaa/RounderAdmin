<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\TechnicianRequest;

/** التحقق من طلب إرسال رمز التحقق للعميل لإتمام ومعالجة بلاغ الطوارئ. */
class ApiTechnicianEmergencySendOtpRequest extends TechnicianRequest
{
    public function rules(): array
    {
        return [];
    }
}
