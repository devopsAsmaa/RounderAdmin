<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\{Admin, Role, User};
use App\Modules\Providers\Enums\CommissionStatusCode;
use App\Modules\Providers\Models\{ProviderCommissionEntry, ProviderCommissionStatus, ProviderContract, ProviderProfile};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProviderFinanceTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): Admin
    {
        $this->seed();
        return Admin::create([
            'is_super_admin' => true,
            'is_active' => true,
            'name' => 'Finance Admin',
            'email' => 'finance-admin@example.test',
            'password' => 'StrongPassword123!',
            'locale' => 'ar',
            'role_id' => Role::where('code', 'admin')->value('id'),
        ]);
    }

    public function test_admin_can_view_provider_finance_balances_and_kpis(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $response = $this->get(route('admin.providers.finance.index'));

        $response->assertOk()
            ->assertSee('أرصدة وحسابات المزودين')
            ->assertSee('إجمالي قيمة العمليات')
            ->assertSee('إجمالي عمولات المنصة')
            ->assertSee('المستحق على المزودين')
            ->assertSee(route('admin.providers.commissions.index'));
    }

    public function test_admin_can_view_finance_page_in_english(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $response = $this->withSession(['locale' => 'en'])->get(route('admin.providers.finance.index'));

        $response->assertOk()
            ->assertSee('Provider accounts & balances')
            ->assertSee('Total orders value')
            ->assertSee('Total platform commissions')
            ->assertSee('Outstanding dues on providers');
    }

    public function test_admin_can_filter_providers_with_dues(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $response = $this->get(route('admin.providers.finance.index', ['has_dues' => 1]));

        $response->assertOk();
    }

    public function test_admin_can_view_detailed_commissions_ledger(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $response = $this->get(route('admin.providers.commissions.index'));

        $response->assertOk()
            ->assertSee('سجل العمولات التفصيلي')
            ->assertSee('حالة العمولة')
            ->assertSee('نوع الطلب');
    }

    public function test_admin_can_toggle_commission_status_between_pending_and_settled(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $pendingStatus = ProviderCommissionStatus::where('code', CommissionStatusCode::Pending->value)->firstOrFail();
        $settledStatus = ProviderCommissionStatus::where('code', CommissionStatusCode::Settled->value)->firstOrFail();

        // Create or get an entry
        $providerRole = Role::firstOrCreate(['code' => 'service_provider'], ['name' => 'Provider']);
        $user = User::create([
            'role_id' => $providerRole->id,
            'name' => 'Provider Contact',
            'email' => 'provider_fin_'.uniqid().'@example.test',
            'phone' => '055'.random_int(1000000, 9999999),
            'password' => 'StrongPass123!',
        ]);

        $provider = ProviderProfile::create([
            'user_id' => $user->id,
            'company_name' => 'Finance Test Provider',
            'is_approved' => 1,
        ]);

        $contractStatus = \App\Modules\Providers\Models\ProviderContractStatus::first() 
            ?? \App\Modules\Providers\Models\ProviderContractStatus::create(['code' => 'active', 'name' => 'Active']);

        $contract = $provider->contracts()->create([
            'reference' => 'CONT-TEST-01',
            'status_id' => $contractStatus->id,
            'service_commission_percent' => 15,
            'emergency_commission_percent' => 20,
            'starts_on' => now()->toDateString(),
        ]);

        $entry = ProviderCommissionEntry::create([
            'provider_profile_id' => $provider->id,
            'provider_contract_id' => $contract->id,
            'status_id' => $pendingStatus->id,
            'gross_amount' => 500.00,
            'commission_percent_snapshot' => 15.00,
            'commission_amount' => 75.00,
            'calculated_at' => now(),
        ]);

        $this->assertSame($pendingStatus->id, $entry->status_id);

        // Toggle to settled
        $response = $this->post(route('admin.providers.commissions.toggle-status', $entry->id));
        $response->assertRedirect();

        $entry->refresh();
        $this->assertSame($settledStatus->id, $entry->status_id);
        $this->assertNotNull($entry->settled_at);

        // Toggle back to pending
        $response2 = $this->post(route('admin.providers.commissions.toggle-status', $entry->id));
        $response2->assertRedirect();

        $entry->refresh();
        $this->assertSame($pendingStatus->id, $entry->status_id);
        $this->assertNull($entry->settled_at);
    }
}
