<?php

namespace App\Modules\Support\Enums;

enum SupportCategory: string
{
    case Order = 'order';
    case Payment = 'payment';
    case Housing = 'housing';
    case Subscription = 'subscription';
    case Business = 'business';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Order => 'مشكلة في طلب',
            self::Payment => 'الدفع',
            self::Housing => 'السكن',
            self::Subscription => 'الاشتراكات والباقات',
            self::Business => 'حلول الأعمال والشركات',
            self::Other => 'أخرى',
        };
    }
}
