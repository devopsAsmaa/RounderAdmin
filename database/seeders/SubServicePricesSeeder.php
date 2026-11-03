<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServicePrice;
use App\Modules\Properties\Models\Region;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServicePricesSeeder extends Seeder
{
    /**
     * تشغيل بذر الأسعار لجميع الخدمات الفرعية في كل المناطق لمزود الخدمة المعتمد.
     */
    public function run(): void
    {
        $provider = ProviderProfile::where('is_approved', true)->first() ?? ProviderProfile::first();

        if (!$provider) {
            $this->command?->error('لا يوجد مزود خدمة في قاعدة البيانات.');
            return;
        }

        // ربط المزود بجميع الخدمات المتاحة
        $allServiceIds = Service::where('is_active', true)->pluck('id')->all();
        $provider->services()->syncWithoutDetaching($allServiceIds);

        // المناطق التي تحتوي على مدن
        $regions = Region::whereHas('cities')->pluck('id')->all();

        // جميع الخدمات الفرعية النشطة
        $subServices = SubService::with('service')->where('is_active', true)->get();

        $basePrices = [
            'plumbing' => 150,
            'electrical' => 180,
            'home_cleaning' => 250,
            'appliances' => 140,
            'pest_control' => 200,
            'carpentry' => 130,
            'water_heaters' => 120,
            'ikea' => 90,
            'water_filters' => 100,
            'blacksmith' => 150,
            'ac_split' => 160,
            'ac_window' => 130,
            'ac_central' => 280,
            'ac_concealed' => 220,
            'refrigerators' => 170,
            'ovens' => 140,
            'washing_machines' => 150,
        ];

        $this->command?->info("بدء إضافة الأسعار لـ {$subServices->count()} خدمة فرعية في " . count($regions) . " مناطق...");

        $createdCount = 0;

        foreach ($subServices as $subService) {
            $serviceCode = $subService->service?->code ?? '';
            $base = $basePrices[$serviceCode] ?? ($subService->service?->base_price > 0 ? (float) $subService->service->base_price : 120);

            // تدرج واقعي في السعر لكل خدمة فرعية
            $variation = (($subService->id * 17) % 75) - 20;
            $price = max(50, round(($base + $variation) / 5) * 5);

            foreach ($regions as $regionId) {
                $priceRecord = SubServicePrice::where('provider_profile_id', $provider->id)
                    ->where('sub_service_id', $subService->id)
                    ->where('region_id', $regionId)
                    ->first();

                if (!$priceRecord) {
                    $priceRecord = SubServicePrice::create([
                        'provider_profile_id' => $provider->id,
                        'sub_service_id' => $subService->id,
                        'region_id' => $regionId,
                        'price' => $price,
                        'is_active' => true,
                    ]);
                    $createdCount++;
                }

                // ربط مدن المنطقة بالتسعير
                DB::statement(
                    'INSERT IGNORE INTO sub_service_price_cities (sub_service_price_id, city_id) SELECT ?, id FROM cities WHERE region_id = ?',
                    [$priceRecord->id, $regionId]
                );
            }
        }

        $this->command?->info("تم بنجاح! إجمالي سجلات التسعير الجديدة: {$createdCount}");
    }
}

