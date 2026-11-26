<?php

namespace App\Modules\Providers\Services;

use App\Modules\Providers\Enums\CommissionStatusCode;
use App\Modules\Providers\Models\{ProviderCommissionEntry, ProviderCommissionStatus, ProviderProfile};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * خدمة إدارة حسابات ومستحقات وعمولات مزودي الخدمات في لوحة الإدارة.
 */
class AdminProviderFinanceService
{
    /**
     * جلب أرصدة وحسابات مزودي الخدمات مع الإحصائيات العامة.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function balances(array $input = []): array
    {
        $settledStatus = ProviderCommissionStatus::query()->firstWhere('code', CommissionStatusCode::Settled->value);
        $pendingStatus = ProviderCommissionStatus::query()->firstWhere('code', CommissionStatusCode::Pending->value);
        $settledId = $settledStatus?->id;
        $pendingId = $pendingStatus?->id;

        // إحصائيات عامة للمنصة ككل
        $kpis = [
            'total_gross' => (float) ProviderCommissionEntry::query()->sum('gross_amount'),
            'total_commission' => (float) ProviderCommissionEntry::query()->sum('commission_amount'),
            'total_settled' => (float) ProviderCommissionEntry::query()
                ->when($settledId, fn (Builder $q) => $q->where('status_id', $settledId))
                ->sum('commission_amount'),
            'total_pending' => (float) ProviderCommissionEntry::query()
                ->when($pendingId, fn (Builder $q) => $q->where('status_id', $pendingId))
                ->sum('commission_amount'),
            'total_orders' => (int) ProviderCommissionEntry::query()->count(),
        ];

        // استعلام أرصدة المزودين
        $query = ProviderProfile::query()
            ->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->leftJoin('provider_contracts', function ($join) {
                $join->on('provider_profiles.id', '=', 'provider_contracts.provider_profile_id')
                    ->whereRaw('provider_contracts.id = (SELECT MAX(pc2.id) FROM provider_contracts as pc2 WHERE pc2.provider_profile_id = provider_profiles.id)');
            })
            ->select([
                'provider_profiles.id as provider_id',
                'provider_profiles.company_name',
                'provider_profiles.trade_name',
                'provider_profiles.is_approved',
                'users.id as user_id',
                'users.name as contact_name',
                'users.phone as contact_phone',
                'users.email as contact_email',
                'provider_contracts.service_commission_percent',
                'provider_contracts.emergency_commission_percent',
                'provider_contracts.reference as contract_reference',
            ])
            ->selectSub(
                ProviderCommissionEntry::query()
                    ->selectRaw('COALESCE(SUM(gross_amount), 0)')
                    ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id'),
                'gross_total'
            )
            ->selectSub(
                ProviderCommissionEntry::query()
                    ->selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id'),
                'commission_total'
            )
            ->selectSub(
                ProviderCommissionEntry::query()
                    ->selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id')
                    ->when($settledId, fn (Builder $q) => $q->where('status_id', $settledId)),
                'settled_total'
            )
            ->selectSub(
                ProviderCommissionEntry::query()
                    ->selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id')
                    ->when($pendingId, fn (Builder $q) => $q->where('status_id', $pendingId)),
                'pending_total'
            )
            ->selectSub(
                ProviderCommissionEntry::query()
                    ->selectRaw('COUNT(id)')
                    ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id'),
                'orders_count'
            );

        // بحث بالاسم أو الهاتف أو البريد
        if (!empty($input['q'])) {
            $term = trim($input['q']);
            $query->where(function (Builder $q) use ($term) {
                $q->where('provider_profiles.company_name', 'like', "%{$term}%")
                    ->orWhere('provider_profiles.trade_name', 'like', "%{$term}%")
                    ->orWhere('users.name', 'like', "%{$term}%")
                    ->orWhere('users.phone', 'like', "%{$term}%")
                    ->orWhere('users.email', 'like', "%{$term}%");
            });
        }

        // فلترة المزودين الذين عليهم مستحقات فقط
        if (!empty($input['has_dues'])) {
            $query->having('pending_total', '>', 0);
        }

        // الترتيب: المزودون الأكثر مديونية أولاً ثم حسب رقم المعرف
        $providers = $query->orderByDesc('pending_total')
            ->orderByDesc('commission_total')
            ->paginate((int) ($input['per_page'] ?? 20))
            ->withQueryString();

        return compact('kpis', 'providers', 'input');
    }

    /**
     * جلب سجل العمولات التفصيلي الشامل مع الفلاتر والبحث.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function commissions(array $input = []): array
    {
        $query = ProviderCommissionEntry::query()->with([
            'providerProfile.user',
            'status',
            'booking.service.translations',
            'emergencyRequest.emergencyType.translations',
        ]);

        // فلترة بحسب مزود معين
        if (!empty($input['provider_id'])) {
            $query->where('provider_profile_id', (int) $input['provider_id']);
        }

        // فلترة بحسب الحالة (معلقة / مسددة / ملغاة)
        if (!empty($input['status']) && $input['status'] !== 'all') {
            $query->whereHas('status', fn (Builder $q) => $q->where('code', $input['status']));
        }

        // فلترة بنوع الطلب (طلب خدمة أو طوارئ)
        if (!empty($input['type'])) {
            if ($input['type'] === 'booking') {
                $query->whereNotNull('booking_id');
            } elseif ($input['type'] === 'emergency') {
                $query->whereNotNull('emergency_request_id');
            }
        }

        // فلترة بالتاريخ من
        if (!empty($input['date_from'])) {
            $query->where('calculated_at', '>=', $input['date_from'] . ' 00:00:00');
        }

        // فلترة بالتاريخ إلى
        if (!empty($input['date_to'])) {
            $query->where('calculated_at', '<=', $input['date_to'] . ' 23:59:59');
        }

        // بحث سريع
        if (!empty($input['q'])) {
            $term = trim($input['q']);
            $query->where(function (Builder $q) use ($term) {
                if (is_numeric($term)) {
                    $q->where('booking_id', (int) $term)
                        ->orWhere('id', (int) $term);
                }
                $q->orWhereHas('emergencyRequest', fn (Builder $er) => $er->where('reference', 'like', "%{$term}%"))
                    ->orWhereHas('providerProfile', fn (Builder $pp) => $pp->where('company_name', 'like', "%{$term}%"));
            });
        }

        // حساب مجاميع الفلترة الحالية
        $totalsQuery = clone $query;
        $filteredTotals = [
            'gross' => (float) (clone $totalsQuery)->sum('gross_amount'),
            'commission' => (float) (clone $totalsQuery)->sum('commission_amount'),
            'count' => (int) (clone $totalsQuery)->count(),
        ];

        $entries = $query->latest('calculated_at')
            ->latest('id')
            ->paginate((int) ($input['per_page'] ?? 25))
            ->withQueryString();

        // قائمة المزودين للفلتر
        $providersList = ProviderProfile::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'trade_name']);

        // قائمة الحالات
        $statusesList = ProviderCommissionStatus::query()->get();

        return compact('entries', 'filteredTotals', 'providersList', 'statusesList', 'input');
    }

    /**
     * تغيير حالة عمولة فردية بين معلقة ومسددة (تسوية سريعة).
     *
     * @param int $id
     * @return ProviderCommissionEntry
     */
    public function toggleStatus(int $id): ProviderCommissionEntry
    {
        $entry = ProviderCommissionEntry::query()->with('status')->findOrFail($id);

        $pendingStatus = ProviderCommissionStatus::query()->firstWhere('code', CommissionStatusCode::Pending->value);
        $settledStatus = ProviderCommissionStatus::query()->firstWhere('code', CommissionStatusCode::Settled->value);

        if ($entry->status?->code === CommissionStatusCode::Settled->value) {
            $entry->update([
                'status_id' => $pendingStatus?->id,
                'settled_at' => null,
            ]);
        } else {
            $entry->update([
                'status_id' => $settledStatus?->id,
                'settled_at' => now(),
            ]);
        }

        return $entry->fresh(['status']);
    }
}
