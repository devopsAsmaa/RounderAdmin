<?php

namespace App\Modules\Bookings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Requests\ApiTechnicianConfirmOrderRequest;
use App\Modules\Bookings\Requests\ApiTechnicianOrderIndexRequest;
use App\Modules\Bookings\Requests\ApiTechnicianSendOtpRequest;
use App\Modules\Bookings\Requests\ApiTechnicianUpdateStatusRequest;
use App\Modules\Bookings\Services\ApiTechnicianBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** متحكم HTTP لطلبات الفني المعتمد؛ يفوض كافة قواعد العمل إلى الخدمة. */
class TechnicianBookingController extends Controller
{
    public function __construct(private readonly ApiTechnicianBookingService $service)
    {
    }

    /**
     * إرجاع قائمة حالات الطلبات المتاحة للفني.
     * @return JsonResponse
     */
    public function statuses(): JsonResponse
    {
        return response()->json($this->service->statuses(), 200);
    }

    /**
     * إرجاع قائمة الطلبات المسندة للفني.
     * @param ApiTechnicianOrderIndexRequest $request
     * @return JsonResponse
     */
    public function index(ApiTechnicianOrderIndexRequest $request): JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * إرجاع تفاصيل طلب محدد للفني.
     * @param int $bookingId
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $bookingId, Request $request): JsonResponse
    {
        return response()->json($this->service->show($bookingId, $request->user()), 200);
    }

    /**
     * توليد وإرسال رمز تحقق OTP للعميل لتأكيد إتمام الخدمة.
     * @param int $bookingId
     * @param ApiTechnicianSendOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(int $bookingId, ApiTechnicianSendOtpRequest $request): JsonResponse
    {
        return response()->json($this->service->sendOtp($bookingId, $request->user()), 200);
    }

    /**
     * تأكيد إتمام الطلب بالصورة بعد التنفيذ والرمز المستلم من العميل.
     * @param int $bookingId
     * @param ApiTechnicianConfirmOrderRequest $request
     * @return JsonResponse
     */
    public function confirmOrder(int $bookingId, ApiTechnicianConfirmOrderRequest $request): JsonResponse
    {
        $input = $request->validated();
        if ($request->hasFile('image_after')) {
            $input['image_after'] = $request->file('image_after');
        }

        return response()->json($this->service->confirmOrder($bookingId, $input, $request->user()), 200);
    }

    /**
     * تحديث حالة الطلب من قبل الفني (مثلاً: الفني في الطريق أو مكتمل).
     * @param int $bookingId
     * @param ApiTechnicianUpdateStatusRequest $request
     * @return JsonResponse
     */
    public function updateStatus(int $bookingId, ApiTechnicianUpdateStatusRequest $request): JsonResponse
    {
        $input = $request->validated();
        if ($request->hasFile('image_after')) {
            $input['image_after'] = $request->file('image_after');
        }

        return response()->json($this->service->updateStatus($bookingId, $input, $request->user()), 200);
    }
}
