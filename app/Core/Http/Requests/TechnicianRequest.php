<?php

namespace App\Core\Http\Requests;

use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/** قاعدة طلبات الفنيين المعتمدين. */
abstract class TechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && (bool) $user->is_technician;
    }
}
