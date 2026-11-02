<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;

/** Validate optional service filter for the resident provider list. */
class ApiResidentServiceProvidersRequest extends ResidentRequest
{
    public function rules(): array
    {
        return [
            'service_id' => ['sometimes', 'required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
        ];
    }
}
