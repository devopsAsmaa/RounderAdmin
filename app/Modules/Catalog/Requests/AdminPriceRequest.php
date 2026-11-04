<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * يتحقق من جميع عمليات أسعار الخدمات الفرعية في لوحة الإدارة (عرض، إنشاء متعدد، تعديل).
 */
class AdminPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * تجهيز ومعالجة البيانات قبل التحقق منها؛ لضمان عدم التأثر بحد max_input_vars في PHP.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('city_ids_json') && !empty($this->input('city_ids_json'))) {
            $decoded = json_decode((string) $this->input('city_ids_json'), true);
            if (is_array($decoded)) {
                $this->merge(['city_ids' => $decoded]);
            }
        } elseif (is_string($this->input('city_ids'))) {
            $decoded = json_decode($this->input('city_ids'), true);
            if (is_array($decoded)) {
                $this->merge(['city_ids' => $decoded]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [
                'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
                'service_id' => ['nullable', 'integer', 'exists:services,id'],
                'sub_service_id' => ['nullable', 'integer', 'exists:sub_services,id'],
                'region_id' => ['nullable', 'integer', 'exists:regions,id'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'status' => ['nullable', 'string', 'in:all,active,inactive'],
                'search' => ['nullable', 'string', 'max:100'],
            ];
        }

        if ($this->isMethod('POST')) {
            return [
                'provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
                'service_id' => ['required', 'integer', 'exists:services,id'],
                'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
                'region_id' => ['required', 'integer', 'exists:regions,id'],
                'city_ids' => ['required', 'array', 'min:1'],
                'city_ids.*' => ['required', 'integer', 'exists:cities,id'],
                'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
                'is_active' => ['nullable', 'boolean'],
            ];
        }

        // PUT / PATCH
        return [
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['required', 'integer', 'exists:cities,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'provider_profile_id' => __('Provider'),
            'service_id' => __('Service'),
            'sub_service_id' => __('Sub service'),
            'region_id' => __('Region'),
            'city_ids' => __('Cities'),
            'price' => __('Price'),
            'is_active' => __('Status'),
        ];
    }
}
