<?php

namespace App\Modules\Emergencies\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Requests\ApiTechnicianEmergencyIndexRequest;
use App\Modules\Emergencies\Requests\ApiTechnicianEmergencyUpdateStatusRequest;
use App\Modules\Emergencies\Requests\ApiTechnicianEmergencySendOtpRequest;
use App\Modules\Emergencies\Requests\ApiTechnicianEmergencyConfirmRequest;
use App\Modules\Emergencies\Services\ApiTechnicianEmergencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** متحكم HTTP لطلبات وبلاغات الطوارئ للفني المعتمد. */
class TechnicianEmergencyController extends Controller
{
    public function __construct(private readonly ApiTechnicianEmergencyService $service)
    {
    }

    /**
     * إرجاع قائمة حالات طلبات الطوارئ المعتمدة للفني.
     * @return JsonResponse
     */
    public function statuses(): JsonResponse
    {
        return response()->json($this->service->statuses(), 200);
    }

    /**
     * إرجاع قائمة طلبات الطوارئ المسندة للفني.
     * @param ApiTechnicianEmergencyIndexRequest $request
     * @return JsonResponse
     */
    public function index(ApiTechnicianEmergencyIndexRequest $request): JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * إرجاع تفاصيل طلب طوارئ محدد.
     * @param int $requestId
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $requestId, Request $request): JsonResponse
    {
        return response()->json($this->service->show($requestId, $request->user()), 200);
    }

    /**
     * تحديث حالة طلب الطوارئ (الفريق في الطريق أو تمت المعالجة).
     * @param int $requestId
     * @param ApiTechnicianEmergencyUpdateStatusRequest $request
     * @return JsonResponse
     */
    public function updateStatus(int $requestId, ApiTechnicianEmergencyUpdateStatusRequest $request): JsonResponse
    {
        return response()->json($this->service->updateStatus($requestId, $request->validated(), $request->user()), 200);
    }

    /**
     * إرسال رمز تحقق OTP للعميل لمعالجة وإتمام طلب الطوارئ.
     * @param int $requestId
     * @param ApiTechnicianEmergencySendOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(int $requestId, ApiTechnicianEmergencySendOtpRequest $request): JsonResponse
    {
        return response()->json($this->service->sendOtp($requestId, $request->user()), 200);
    }

    /**
     * تأكيد معالجة طلب الطوارئ برمز OTP للعميل وصورة الإنجاز.
     * @param int $requestId
     * @param ApiTechnicianEmergencyConfirmRequest $request
     * @return JsonResponse
     */
    public function confirm(int $requestId, ApiTechnicianEmergencyConfirmRequest $request): JsonResponse
    {
        return response()->json($this->service->confirm($requestId, $request->validated(), $request->user()), 200);
    }

    /**
     * تأكيد معالجة طلب الطوارئ.
     * @param int $requestId
     * @param ApiTechnicianEmergencyUpdateStatusRequest $request
     * @return JsonResponse
     */
    public function resolve(int $requestId, ApiTechnicianEmergencyUpdateStatusRequest $request): JsonResponse
    {
        $input = $request->validated();
        if (!empty($input['otp'])) {
            return response()->json($this->service->confirm($requestId, $input, $request->user()), 200);
        }

        $input['status'] = 'resolved';

        return response()->json($this->service->updateStatus($requestId, $input, $request->user()), 200);
    }
}
