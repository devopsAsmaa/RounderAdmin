<?php

namespace App\Modules\Identity\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\ApiAuthLogoutRequest;
use App\Modules\Identity\Requests\ApiTechnicianLoginRequest;
use App\Modules\Identity\Services\ApiAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** متحكم عمليات المصادقة والملف الشخصي للفنيين. */
class TechnicianAuthController extends Controller
{
    public function __construct(private readonly ApiAuthService $service)
    {
    }

    /**
     * تسجيل دخول الفني.
     * @param ApiTechnicianLoginRequest $request
     * @return JsonResponse
     */
    public function login(ApiTechnicianLoginRequest $request): JsonResponse
    {
        return response()->json($this->service->technicianLogin($request->validated(), null), 200);
    }

    /**
     * إرجاع بيانات الملف الشخصي للفني.
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($this->service->technicianProfile($request->user()), 200);
    }

    /**
     * تسجيل خروج الفني.
     * @param ApiAuthLogoutRequest $request
     * @return JsonResponse
     */
    public function logout(ApiAuthLogoutRequest $request): JsonResponse
    {
        return response()->json($this->service->logout($request->validated(), $request->user()), 200);
    }
}
