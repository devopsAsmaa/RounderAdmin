<?php

namespace App\Modules\Emergencies\Services;

use App\Modules\Emergencies\Models\EmergencySetting;
use App\Modules\Emergencies\Models\EmergencyType;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\ResidentHome;

/** Read the active emergency type catalog and settings for residents. */
class ApiEmergencyTypeService
{
    /**
     * @param array<string, mixed> $input
     * @param User|null $actor
     * @return array{data: \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>, setting: array<string, mixed>}
     */
    public function index(array $input, ?User $actor): array
    {
        $locale = app()->getLocale();

        $types = EmergencyType::query()
            ->active()
            ->with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (EmergencyType $type) => [
                'id' => $type->id,
                'code' => $type->code,
                'name' => $type->translations->firstWhere('locale', $locale)?->name ?? $type->code,
            ])
            ->values();

        $setting = $this->resolveSetting($input, $actor, $locale);

        return [
            'data' => $types,
            'setting' => $setting,
        ];
    }

    /**
     * Resolve setting based on resident_home_id or actor's primary home.
     *
     * @param array<string, mixed> $input
     * @param User|null $actor
     * @param string $locale
     * @return array<string, mixed>
     */
    private function resolveSetting(array $input, ?User $actor, string $locale): array
    {
        $membership = null;

        if ($actor) {
            $query = ResidentHome::query()
                ->where('user_id', $actor->id)
                ->with(['home.city.region.translations']);

            if (!empty($input['resident_home_id'])) {
                $membership = (clone $query)->where('id', (int) $input['resident_home_id'])->first();
            }

            if (!$membership) {
                $membership = (clone $query)->where('is_primary', true)->first()
                    ?? (clone $query)->first();
            }
        }

        $region = $membership?->home?->city?->region;
        $setting = $region ? EmergencySetting::forRegion($region->id) : null;

        $regionName = null;
        if ($region) {
            $regionName = $region->translations->firstWhere('locale', $locale)?->name
                ?? $region->translations->firstWhere('locale', 'ar')?->name
                ?? $region->code;
        }

        return [
            'region_id' => $region?->id,
            'region_name' => $regionName,
            'is_available' => $setting ? (bool) $setting->is_active : true,
            'price' => $setting ? (float) $setting->price : 150.00,
            'currency' => 'SAR',
            'cancellation_fee' => $setting ? (float) $setting->cancellation_fee : 0.00,
            'estimated_time_minutes' => $setting ? (int) $setting->estimated_time_minutes : 30,
        ];
    }
}
