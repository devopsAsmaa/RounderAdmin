<?php

namespace App\Modules\Emergencies\Models;

use App\Modules\Properties\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * يمثل إعدادات وتسعير طلبات الطوارئ حسب كل منطقة جغرافية.
 */
class EmergencySetting extends Model
{
    protected $table = 'emergency_settings';

    protected $fillable = [
        'region_id',
        'price',
        'cancellation_fee',
        'estimated_time_minutes',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'region_id' => 'integer',
            'price' => 'decimal:2',
            'cancellation_fee' => 'decimal:2',
            'estimated_time_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * المنطقة الجغرافية المرتبطة بالإعداد.
     *
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * تصفية الإعدادات النشطة فقط.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * جلب إعدادات الطوارئ لمنطقة معينة.
     *
     * @param int $regionId
     * @return static|null
     */
    public static function forRegion(int $regionId): ?static
    {
        return static::query()->where('region_id', $regionId)->first();
    }

    /**
     * جلب سعر الطوارئ لمنطقة معينة مع سعر افتراضي في حال عدم وجود تخصيص.
     *
     * @param int|null $regionId
     * @param float $default
     * @return float
     */
    public static function priceForRegion(?int $regionId, float $default = 0.0): float
    {
        if (!$regionId) {
            return $default;
        }

        $setting = static::forRegion($regionId);

        return $setting && $setting->is_active ? (float) $setting->price : $default;
    }
}
