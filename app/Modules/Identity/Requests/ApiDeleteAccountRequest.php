<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\AuthenticatedRequest;

/** التحقق من طلب حذف الحساب (Soft Delete). */
class ApiDeleteAccountRequest extends AuthenticatedRequest
{
    /**
     * قواعد المدخلات.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['sometimes', 'nullable', 'string'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}

