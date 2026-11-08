<?php

namespace App\Modules\Bookings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * عرض حالات الطلبات والطوارئ والتحكم في ظهورها داخل لوحة مزود الخدمة فقط.
 */
class OrderStatusAdminController extends Controller
{
    public function index(): View
    {
        $bookingStatuses = BookingStatus::query()
            ->with('translations')
            ->withCount('bookings')
            ->orderBy('id')
            ->get();

        $emergencyStatuses = EmergencyRequestStatus::query()
            ->with('translations')
            ->withCount('emergencyRequests')
            ->orderBy('id')
            ->get();

        return view('admin.order-statuses.index', compact('bookingStatuses', 'emergencyStatuses'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        // دعم التحديث الفوري لـ Checkbox واحد عبر AJAX أو حفظ النموذج بالكامل
        if ($request->filled('type') && $request->filled('status_id')) {
            $validated = $request->validate([
                'type' => ['required', 'in:booking,emergency'],
                'status_id' => ['required', 'integer'],
                'show_in_provider_portal' => ['required', 'boolean'],
            ]);

            if ($validated['type'] === 'booking') {
                $status = BookingStatus::findOrFail($validated['status_id']);
            } else {
                $status = EmergencyRequestStatus::findOrFail($validated['status_id']);
            }

            $status->update([
                'show_in_provider_portal' => (bool) $validated['show_in_provider_portal'],
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'status_id' => $status->id,
                    'type' => $validated['type'],
                    'show_in_provider_portal' => (bool) $status->show_in_provider_portal,
                    'message' => __('Status visibility updated successfully.'),
                ]);
            }

            return redirect()->route('admin.order-statuses.index')
                ->with('status', __('Status visibility updated successfully.'));
        }

        $validated = $request->validate([
            'booking_status_ids' => ['nullable', 'array'],
            'booking_status_ids.*' => ['integer', 'exists:booking_statuses,id'],
            'emergency_status_ids' => ['nullable', 'array'],
            'emergency_status_ids.*' => ['integer', 'exists:emergency_request_statuses,id'],
        ]);

        $visibleBookingIds = array_map('intval', $validated['booking_status_ids'] ?? []);
        $visibleEmergencyIds = array_map('intval', $validated['emergency_status_ids'] ?? []);

        DB::transaction(function () use ($visibleBookingIds, $visibleEmergencyIds) {
            foreach (BookingStatus::all() as $status) {
                $status->update([
                    'show_in_provider_portal' => in_array((int) $status->id, $visibleBookingIds, true),
                ]);
            }

            foreach (EmergencyRequestStatus::all() as $status) {
                $status->update([
                    'show_in_provider_portal' => in_array((int) $status->id, $visibleEmergencyIds, true),
                ]);
            }
        });

        return redirect()->route('admin.order-statuses.index')
            ->with('status', __('Status visibility updated successfully.'));
    }
}
