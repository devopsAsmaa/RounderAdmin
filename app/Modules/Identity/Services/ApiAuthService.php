<?php

namespace App\Modules\Identity\Services;
use App\Modules\Identity\Enums\RoleCode;
use App\Core\Enums\SupportedLocale;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;
use App\Modules\Identity\Services\SMS;
use Illuminate\Support\Facades\Log;

/** قواعد عمل Identity؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiAuthService
{

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function register(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performRegister($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthRegister, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performRegister(array $input, ?User $actor): mixed
    {
        $data = $input;
        $mobile = $data['mobile'] ?? $data['phone'] ?? null;
        if ($mobile !== null) {
            $data['phone'] = $mobile;
            unset($data['mobile']);
        }

        $role = Role::where('code', RoleCode::Resident->value)->firstOrFail();
        $user = User::create($data + ['role_id' => $role->id, 'locale' => $data['locale'] ?? SupportedLocale::Arabic->value]);
        return ['data' => ['user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + ['mobile' => $user->phone], 'token' => $user->createToken('rounder-mobile')->plainTextToken]];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function login(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLogin($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthLogin, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLogin(array $input, ?User $actor): mixed
    {
        $data = $input;
        $mobile = $data['mobile'] ?? $data['phone'] ?? null;

        $user = User::with('role')
            ->where(function ($query) use ($mobile, $data) {
                if ($mobile) {
                    $international = '+966' . substr($mobile, 1);
                    $query->where('phone', $mobile)
                          ->orWhere('phone', $international)
                          ->orWhere('phone', ltrim($mobile, '0'));
                } elseif (!empty($data['email'])) {
                    $query->where('email', $data['email']);
                }
            })
            ->first();

        $isAllowed = ($user && (($user->role?->code === RoleCode::Resident->value) || (bool) $user->is_technician));
        if (!$user || !Hash::check($data['password'], $user->password) || !$isAllowed) {
            throw ValidationException::withMessages(['mobile' => __('auth.failed')]);
        }
        return ['data' => [
            'user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + [
                'mobile' => $user->phone,
                'is_technician' => (bool) $user->is_technician,
                'provider_profile_id' => $user->provider_profile_id,
            ],
            'token' => $user->createToken('rounder-mobile')->plainTextToken,
        ]];
    }

    /**
     * تسجيل دخول الفني المعتمد.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function technicianLogin(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performTechnicianLogin($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiTechnicianLogin, $actor?->id)));
            return $result;
        });
    }

    private function performTechnicianLogin(array $input, ?User $actor): array
    {
        $data = $input;
        $mobile = $data['mobile'] ?? $data['phone'] ?? null;

        $user = User::with(['role', 'providerProfile'])
            ->where(function ($query) use ($mobile, $data) {
                if ($mobile) {
                    $international = '+966' . substr($mobile, 1);
                    $query->where('phone', $mobile)
                          ->orWhere('phone', $international)
                          ->orWhere('phone', ltrim($mobile, '0'));
                } elseif (!empty($data['email'])) {
                    $query->where('email', $data['email']);
                }
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password) || !(bool) $user->is_technician) {
            throw ValidationException::withMessages([
                'mobile' => __('auth.failed'),
            ]);
        }

        return [
            'status' => true,
            'message' => __('Login successful.'),
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'mobile' => $user->phone,
                    'locale' => $user->locale,
                    'is_technician' => true,
                    'provider_profile_id' => $user->provider_profile_id,
                    'provider_name' => $user->providerProfile?->company_name,
                ],
                'token' => $user->createToken('rounder-technician')->plainTextToken,
            ],
        ];
    }

    /**
     * إرجاع بيانات الملف الشخصي للفني مع التخصصات والإحصائيات.
     * @param ?User $actor
     * @return array
     */
    public function technicianProfile(?User $actor): array
    {
        DomainViolation::unless($actor && (bool) $actor->is_technician, FailureReason::Forbidden, __('Technician profile not found.'));

        $locale = app()->getLocale();
        $actor->loadMissing([
            'providerProfile',
            'technicianServices.translations',
            'technicianSubServices.translations',
        ]);

        $activeOrdersCount = $actor->technicianBookings()
            ->whereHas('status', fn ($q) => $q->whereIn('code', [
                BookingStatusCode::Pending->value,
                BookingStatusCode::Confirmed->value,
                BookingStatusCode::InProgress->value,
            ]))->count();

        $completedOrdersCount = $actor->technicianBookings()
            ->whereHas('status', fn ($q) => $q->where('code', BookingStatusCode::Completed->value))->count();

        $services = $actor->technicianServices->map(fn ($service) => [
            'id' => $service->id,
            'code' => $service->code,
            'name' => $service->translations->firstWhere('locale', $locale)?->name ?? $service->code,
        ])->values();

        $subServices = $actor->technicianSubServices->map(fn ($sub) => [
            'id' => $sub->id,
            'service_id' => $sub->service_id,
            'code' => $sub->code,
            'name' => $sub->translations->firstWhere('locale', $locale)?->name ?? $sub->code,
        ])->values();

        return [
            'status' => true,
            'data' => [
                'id' => $actor->id,
                'name' => $actor->name,
                'email' => $actor->email,
                'phone' => $actor->phone,
                'mobile' => $actor->phone,
                'locale' => $actor->locale,
                'is_technician' => true,
                'provider' => $actor->providerProfile ? [
                    'id' => $actor->providerProfile->id,
                    'company_name' => $actor->providerProfile->company_name,
                    'trade_name' => $actor->providerProfile->trade_name,
                    'provider_type' => $actor->providerProfile->provider_type,
                ] : null,
                'services' => $services,
                'sub_services' => $subServices,
                'stats' => [
                    'assigned_orders_count' => $actor->technicianBookings()->count(),
                    'active_orders_count' => $activeOrdersCount,
                    'completed_orders_count' => $completedOrdersCount,
                ],
            ],
        ];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function logout(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLogout($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthLogout, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLogout(array $input, ?User $actor): mixed
    {
        $actor->currentAccessToken()?->delete();return ['message'=>'Signed out'];
    }

    /**
     * حذف حساب المستخدم نهائياً بطريقة الحذف المرن (Soft Delete).
     * @param array $input
     * @param ?User $actor
     * @return array
     * @throws \Throwable
     */
    public function deleteAccount(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor): array {
            $result = $this->performDeleteAccount($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthDeleteAccount, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد عملية حذف الحساب؛ إلغاء الرموز وتطبيق الحذف المرن.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    private function performDeleteAccount(array $input, ?User $actor): array
    {
        if (!$actor) {
            throw ValidationException::withMessages([
                'account' => __('Unauthenticated.'),
            ]);
        }

        // إلغاء جميع رموز الوصول (Sanctum Tokens)
        $actor->tokens()->delete();

        // تنفيذ الحذف المرن (Soft Delete)
        $actor->delete();

        return [
            'message' => __('Account deleted successfully.'),
        ];
    }

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function me(array $input, ?User $actor): mixed
    {
        return ['data' => $actor->only(['id', 'name', 'email', 'phone', 'locale']) + [
            'mobile' => $actor->phone,
            'is_technician' => (bool) $actor->is_technician,
            'provider_profile_id' => $actor->provider_profile_id,
        ]];
    }

    /**
     * التحقق من صحة رقم الجوال وهل هو مسجل مسبقاً.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function validateMobile(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);

        $user = User::where(function ($q) use ($mobile, $international) {
            $q->where('phone', $mobile)
              ->orWhere('phone', $international)
              ->orWhere('phone', ltrim($mobile, '0'));
        })->first();

        return [
            'status' => true,
            'message' => 'رقم الجوال صحيح ومقبول.',
            'data' => [
                'mobile' => $mobile,
                'formatted_mobile' => $international,
                'is_registered' => (bool) $user,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ] : null,
            ],
        ];
    }

    /**
     * إرسال رمز تحقق OTP مكون من 6 أرقام.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function sendOtp(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);

        // $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = '542187'; // رمز التحقق الثابت لأغراض الاختبار
        try {
            SMS::send($mobile, 'Your OTP code is ' . $otp);
        } catch (\Throwable $e) {
            Log::error('Failed to send OTP', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        // حفظ الكود في Cache لمدة 3 دقائق
        Cache::put('otp_' . $mobile, $otp, now()->addMinutes(3));
        Cache::put('otp_' . $international, $otp, now()->addMinutes(3));

        $response = [
            'status' => true,
            'message' => 'تم إرسال رمز التحقق المكون من 6 أرقام بنجاح.',
            'data' => [
                'mobile' => $mobile,
                'formatted_mobile' => $international,
                'expires_in_seconds' => 180,
            ],
        ];

        // إرجاع الكود في بيئة التطوير لتسهيل التجربة في Postman والتطبيق
        if (app()->environment('local', 'testing') || config('app.debug')) {
            $response['data']['otp'] = $otp;
        }

        return $response;
    }

    /**
     * التحقق من صحة رمز OTP وتأكيد الدخول.
     * @param array $input
     * @param ?User $actor
     * @return array
     * @throws ValidationException
     */
    public function verifyOtp(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);
        $otp = (string) ($input['otp'] ?? '');

        $cachedOtp = Cache::get('otp_' . $mobile) ?? Cache::get('otp_' . $international);

        $isMatch = is_string($cachedOtp) && hash_equals($cachedOtp, $otp);

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        // مسح الكود بعد الاستخدام
        Cache::forget('otp_' . $mobile);
        Cache::forget('otp_' . $international);

        // فحص وجود المستخدم
        $user = User::with('role')->where(function ($q) use ($mobile, $international) {
            $q->where('phone', $mobile)
              ->orWhere('phone', $international)
              ->orWhere('phone', ltrim($mobile, '0'));
        })->first();

        if ($user) {
            $token = $user->createToken('rounder-mobile')->plainTextToken;
            return [
                'status' => true,
                'message' => 'تم التحقق بنجاح وتسجيل الدخول.',
                'data' => [
                    'is_registered' => true,
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + [
                        'mobile' => $user->phone,
                        'is_technician' => (bool) $user->is_technician,
                        'provider_profile_id' => $user->provider_profile_id,
                    ],
                    'token' => $token,
                ],
            ];
        }

        return [
            'status' => true,
            'message' => 'تم التحقق من رقم الجوال بنجاح، يُرجى إكمال إنشاء الحساب.',
            'data' => [
                'is_registered' => false,
                'mobile' => $mobile,
                'formatted_mobile' => $international,
            ],
        ];
    }
}
