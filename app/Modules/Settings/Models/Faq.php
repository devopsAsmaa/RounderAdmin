<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'category',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class, 'faq_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function getQuestionAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)?->question
            ?? $this->translations->firstWhere('locale', 'ar')?->question
            ?? $this->translations->first()?->question
            ?? '';
    }

    public function getAnswerAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)?->answer
            ?? $this->translations->firstWhere('locale', 'ar')?->answer
            ?? $this->translations->first()?->answer
            ?? '';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'individuals' => 'خدمات الأفراد والحجز',
            'pricing' => 'الأسعار والدفع والضمان',
            'business' => 'حلول الأعمال والشركات',
            'providers' => 'بوابة مزودي الخدمة والفنيين',
            default => $this->category,
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'individuals' => 'fa-solid fa-house-chimney',
            'pricing' => 'fa-solid fa-shield-halved',
            'business' => 'fa-solid fa-briefcase',
            'providers' => 'fa-solid fa-screwdriver-wrench',
            default => 'fa-solid fa-circle-question',
        };
    }

    public static function categories(): array
    {
        return [
            'individuals' => [
                'name' => 'خدمات الأفراد والحجز',
                'icon' => 'fa-solid fa-house-chimney',
                'description' => 'كل ما يخص حجز الخدمات المنزلية، المواعيد، وتتبع الفنيين.',
            ],
            'pricing' => [
                'name' => 'الأسعار والدفع والضمان',
                'icon' => 'fa-solid fa-shield-halved',
                'description' => 'سياسات التسعير، طرق الدفع الآمنة، والضمان الذهبي.',
            ],
            'business' => [
                'name' => 'حلول الأعمال والشركات',
                'icon' => 'fa-solid fa-briefcase',
                'description' => 'عقود الصيانة السنوية والتشغيلية للمكاتب، الفروع، والمباني التجارية.',
            ],
            'providers' => [
                'name' => 'بوابة مزودي الخدمة والفنيين',
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'description' => 'طريقة الانضمام، المتطلبات النظامية، وإدارة فرق العمل.',
            ],
        ];
    }
}

