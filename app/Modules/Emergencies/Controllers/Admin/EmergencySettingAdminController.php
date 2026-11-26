<?php

namespace App\Modules\Emergencies\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Requests\AdminEmergencySettingStoreRequest;
use App\Modules\Emergencies\Requests\AdminEmergencySettingUpdateRequest;
use App\Modules\Emergencies\Services\AdminEmergencySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencySettingAdminController extends Controller
{
    public function __construct(
        private readonly AdminEmergencySettingService $service
    ) {}

    /**
     * عرض صفحة إعدادات وتسعير طلبات الطوارئ حسب المناطق.
     */
    public function index(): View
    {
        $data = $this->service->list();
        return view('admin.emergencies.settings.index', $data);
    }

    /**
     * تحديث جماعي لكافة أسعار وإعدادات المناطق دفعة واحدة.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.id' => ['required', 'integer', 'exists:emergency_settings,id'],
            'settings.*.price' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'settings.*.estimated_time_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'settings.*.is_active' => ['nullable'],
        ]);

        $this->service->bulkUpdate($validated['settings']);

        return redirect()->route('admin.emergency-settings.index')
            ->with('status', 'تم حفظ وتحديث كافة أسعار وإعدادات الطوارئ بنجاح.');
    }

    /**
     * حفظ تسعيرة طوارئ لمنطقة جديدة.
     */
    public function store(AdminEmergencySettingStoreRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return redirect()->route('admin.emergency-settings.index')
            ->with('status', 'تمت إضافة تسعيرة الطوارئ للمنطقة بنجاح.');
    }

    /**
     * تحديث بيانات وسعر المنطقة.
     */
    public function update(AdminEmergencySettingUpdateRequest $request, int $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return redirect()->route('admin.emergency-settings.index')
            ->with('status', 'تم تحديث تسعيرة وإعدادات المنطقة بنجاح.');
    }

    /**
     * تبديل حالة تفعيل الخدمة في المنطقة.
     */
    public function toggle(int $id): RedirectResponse
    {
        $setting = $this->service->toggle($id);
        $statusText = $setting->is_active ? 'تفعيل' : 'إيقاف';

        return redirect()->route('admin.emergency-settings.index')
            ->with('status', "تم {$statusText} خدمة الطوارئ للمنطقة المحددة.");
    }

    /**
     * حذف تسعيرة المنطقة.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->destroy($id);

        return redirect()->route('admin.emergency-settings.index')
            ->with('status', 'تم حذف تسعيرة المنطقة بنجاح.');
    }
}
