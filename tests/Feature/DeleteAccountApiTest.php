<?php

namespace Tests\Feature;

use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeleteAccountApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_unauthenticated_user_cannot_delete_account(): void
    {
        $this->deleteJson('/api/v1/resident/account')->assertUnauthorized();
        $this->deleteJson('/api/v1/resident/profile')->assertUnauthorized();
        $this->deleteJson('/api/v1/account')->assertUnauthorized();
        $this->postJson('/api/v1/auth/delete-account')->assertUnauthorized();
    }

    public function test_resident_can_delete_account_via_resident_account_endpoint(): void
    {
        $residentRole = DB::table('roles')->where('code', RoleCode::Resident->value)->value('id');
        $phone = '05' . random_int(10000000, 99999999);
        $password = 'Secret123!';

        $user = User::query()->create([
            'name' => 'ساكن تجريبي للحذف',
            'email' => 'resident-delete-' . uniqid() . '@example.test',
            'phone' => $phone,
            'password' => Hash::make($password),
            'role_id' => $residentRole,
            'locale' => 'ar',
        ]);

        $token = $user->createToken('test-mobile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/resident/account', [
                'reason' => 'لم أعد بحاجة للخدمة',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', __('Account deleted successfully.'));

        // Assert soft delete
        $this->assertNull(User::query()->find($user->id));
        $softDeletedUser = User::withTrashed()->find($user->id);
        $this->assertNotNull($softDeletedUser);
        $this->assertNotNull($softDeletedUser->deleted_at);

        // Assert tokens were revoked
        $this->assertSame(0, $softDeletedUser->tokens()->count());

        // Subsequent authenticated request with old token must be unauthorized
        app('auth')->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();

        // Login attempt must fail
        $this->postJson('/api/v1/auth/login', [
            'mobile' => $phone,
            'password' => $password,
        ])->assertUnprocessable();
    }

    public function test_resident_can_delete_account_via_resident_profile_endpoint(): void
    {
        $residentRole = DB::table('roles')->where('code', RoleCode::Resident->value)->value('id');
        $phone = '05' . random_int(10000000, 99999999);

        $user = User::query()->create([
            'name' => 'ساكن حذف بروفايل',
            'phone' => $phone,
            'password' => Hash::make('Password123!'),
            'role_id' => $residentRole,
            'locale' => 'ar',
        ]);

        $token = $user->createToken('test-profile-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/resident/profile');

        $response->assertOk()
            ->assertJsonPath('message', __('Account deleted successfully.'));

        $this->assertNull(User::query()->find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id)?->deleted_at);
    }

    public function test_user_can_delete_account_via_v1_account_endpoint(): void
    {
        $residentRole = DB::table('roles')->where('code', RoleCode::Resident->value)->value('id');
        $phone = '05' . random_int(10000000, 99999999);

        $user = User::query()->create([
            'name' => 'مستخدم حذف حساب',
            'phone' => $phone,
            'password' => Hash::make('Password123!'),
            'role_id' => $residentRole,
            'locale' => 'ar',
        ]);

        $token = $user->createToken('test-account-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/account');

        $response->assertOk()
            ->assertJsonPath('message', __('Account deleted successfully.'));

        $this->assertNull(User::query()->find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id)?->deleted_at);
    }

    public function test_user_can_delete_account_via_post_auth_delete_account_endpoint(): void
    {
        $residentRole = DB::table('roles')->where('code', RoleCode::Resident->value)->value('id');
        $phone = '05' . random_int(10000000, 99999999);

        $user = User::query()->create([
            'name' => 'مستخدم حذف بوست',
            'phone' => $phone,
            'password' => Hash::make('Password123!'),
            'role_id' => $residentRole,
            'locale' => 'ar',
        ]);

        $token = $user->createToken('test-post-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/delete-account');

        $response->assertOk()
            ->assertJsonPath('message', __('Account deleted successfully.'));

        $this->assertNull(User::query()->find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id)?->deleted_at);
    }
}
