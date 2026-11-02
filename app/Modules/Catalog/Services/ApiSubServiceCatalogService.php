<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\SubService;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Storage;

/** قراءة الخدمات الفرعية الفعالة للعملاء (التطبيق)، ويمكن فلترتها بالخدمة الأم. */
class ApiSubServiceCatalogService
{
    /**
     * @param array $input يقبل service_id اختياريًا.
     * @param ?User $actor
     * @return array{data: \Illuminate\Support\Collection}
     */
    public function __invoke(array $input, ?User $actor): array
    {
        $locale = app()->getLocale();

        $subServices = SubService::with('translations')
            ->active()
            ->when($input['service_id'] ?? null, fn ($q, $id) => $q->where('service_id', $id))
            ->orderBy('service_id')->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(function (SubService $sub) use ($locale) {
                $translation = $sub->translations->firstWhere('locale', $locale);

                return [
                    'id' => $sub->id,
                    'service_id' => $sub->service_id,
                    'code' => $sub->code,
                    'name' => $translation?->name ?? $sub->code,
                    'description' => $translation?->description,
                    'image_url' => $sub->image_path ? Storage::disk('public')->url($sub->image_path) : null,
                ];
            });

        return ['data' => $subServices];
    }
}
