<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;

/** Validate optional service, sub-service, and home filters for the resident provider list. */
class ApiResidentServiceProvidersRequest extends ResidentRequest
{
    /**
     * تجهيز المدخلات وتوحيد مسمى الخدمة الفرعية sub_service_id / sub_services_id
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('sub_service_id') && $this->has('sub_services_id')) {
            $this->merge(['sub_service_id' => $this->input('sub_services_id')]);
        }
    }

    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'sub_service' => ['nullable'],
            'sub_service_id' => ['nullable'],
            'sub_services_id' => ['nullable'],
            'home_id' => ['nullable', 'integer'],
        ];
    }
}
