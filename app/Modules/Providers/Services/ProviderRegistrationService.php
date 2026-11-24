<?php

namespace App\Modules\Providers\Services;

use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Services\ProviderAccountService;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProviderRegistrationService
{
    public function __construct(
        private readonly ProviderAccountService $accounts,
        private readonly ProviderDocumentService $documents
    ) {}

    /** @return \Illuminate\Database\Eloquent\Collection<int, Service> */
    public function services(): \Illuminate\Database\Eloquent\Collection
    {
        return Service::query()->with('translations')->where('is_active', true)->orderBy('code')->get();
    }

    public function serviceCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Modules\Catalog\Models\ServiceCategory::with([
            'translations',
            'services' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('code'),
        ])->where('is_active', true)->orderBy('id')->get();
    }

    /** @param array<string, mixed> $input */
    public function register(array $input): ProviderProfile
    {
        return DB::transaction(function () use ($input): ProviderProfile {
            $user = $this->accounts->create([
                'name' => !empty($input['name']) ? $input['name'] : $input['trade_name'],
                'email' => $input['email'] ?? null,
                'phone' => $input['phone'],
                'locale' => $input['locale'] ?? 'ar',
                'password' => $input['password'],
            ], null);

            $profile = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'company_name' => $input['company_name'],
                'trade_name' => $input['trade_name'],
                'provider_type' => $input['provider_type'],
                'years_of_experience' => $input['years_of_experience'],
                'commercial_registration' => $input['commercial_registration'] ?? null,
                'legal_representative' => $input['legal_representative'] ?? null,
                'legal_representative_id' => $input['legal_representative_id'] ?? null,
                'is_approved' => false,
            ]);
            $profile->services()->sync($input['service_ids']);
            $user->update(['provider_profile_id' => $profile->id]);
            $this->documents->save($profile, $input);

            $draftStatusId = ProviderContractStatus::query()
                ->where('code', ContractStatusCode::Draft->value)
                ->value('id');
            if (!$draftStatusId) {
                throw new \RuntimeException('Provider draft contract status is not configured.');
            }

            ProviderContract::query()->create([
                'provider_profile_id' => $profile->id,
                'status_id' => $draftStatusId,
                'reference' => 'PC-' . Str::upper(Str::random(12)),
                'service_commission_percent' => 10,
                'emergency_commission_percent' => 15,
                'starts_on' => today(),
            ]);

            return $profile;
        });
    }
}