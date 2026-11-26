<?php

namespace App\Modules\Emergencies\Services;

use App\Modules\Emergencies\Models\EmergencySetting;
use App\Modules\Properties\Models\Region;
use Illuminate\Support\Facades\DB;

class AdminEmergencySettingService
{
    /**
     * جلب قائمة إعدادات الطوارئ مع المناطق والإحصائيات.
     *
     * @return array
     */
    public function list(): array
    {
        $locale = app()->getLocale();

        $settings = EmergencySetting::with([
            'region.translations',
            'region.cities.translations',
        ])->get();

        // المناطق التي لم يتم تخصيص إعداد لها بعد
        $configuredRegionIds = $settings->pluck('region_id')->all();
        $availableRegions = Region::with('translations')
            ->whereNotIn('id', $configuredRegionIds)
            ->get();

        // إحصائيات سريعة للبطاقات
        $stats = [
            'total_regions' => $settings->count(),
            'active_regions' => $settings->where('is_active', true)->count(),
            'inactive_regions' => $settings->where('is_active', false)->count(),
            'average_price' => $settings->count() > 0 ? round($settings->avg('price'), 2) : 0.00,
        ];

        return compact('settings', 'availableRegions', 'stats', 'locale');
    }

    /**
     * حفظ تسعيرة طوارئ لمنطقة جديدة.
     *
     * @param array $data
     * @return EmergencySetting
     */
    public function store(array $data): EmergencySetting
    {
        return DB::transaction(function () use ($data) {
            return EmergencySetting::create([
                'region_id' => $data['region_id'],
                'price' => $data['price'],
                'cancellation_fee' => $data['cancellation_fee'] ?? 0.00,
                'estimated_time_minutes' => $data['estimated_time_minutes'] ?? 30,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * تحديث جماعي لكافة أسعار وحالات الطوارئ دفعة واحدة.
     *
     * @param array $items
     * @return void
     */
    public function bulkUpdate(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    EmergencySetting::where('id', $item['id'])->update([
                        'price' => $item['price'],
                        'estimated_time_minutes' => $item['estimated_time_minutes'] ?? 30,
                        'is_active' => !empty($item['is_active']),
                    ]);
                }
            }
        });
    }

    /**
     * تحديث تسعيرة أو إعدادات المنطقة الحالية.
     *
     * @param int $id
     * @param array $data
     * @return EmergencySetting
     */
    public function update(int $id, array $data): EmergencySetting
    {
        return DB::transaction(function () use ($id, $data) {
            $setting = EmergencySetting::findOrFail($id);

            $setting->update([
                'price' => $data['price'],
                'cancellation_fee' => $data['cancellation_fee'] ?? $setting->cancellation_fee,
                'estimated_time_minutes' => $data['estimated_time_minutes'] ?? $setting->estimated_time_minutes,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $setting->is_active,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $setting->notes,
            ]);

            return $setting;
        });
    }

    /**
     * تبديل حالة تفعيل الخدمة في المنطقة (تشغيل/إيقاف).
     *
     * @param int $id
     * @return EmergencySetting
     */
    public function toggle(int $id): EmergencySetting
    {
        $setting = EmergencySetting::findOrFail($id);
        $setting->is_active = !$setting->is_active;
        $setting->save();

        return $setting;
    }

    /**
     * حذف إعدادات تسعيرة المنطقة.
     *
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        $setting = EmergencySetting::findOrFail($id);
        return (bool) $setting->delete();
    }
}
