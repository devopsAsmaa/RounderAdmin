<?php

namespace App\Modules\Providers\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Providers\Services\AdminProviderFinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderFinanceController extends Controller
{
    public function __construct(
        private readonly AdminProviderFinanceService $financeService
    ) {}

    /**
     * عرض أرصدة وحسابات مزودي الخدمات (الشاشة الرئيسية للحسابات).
     */
    public function index(Request $request): View
    {
        $data = $this->financeService->balances($request->query());

        return view('admin.providers.finance.index', $data);
    }

    /**
     * عرض سجل العمولات التفصيلي الشامل والمربوط بالطلبات.
     */
    public function commissions(Request $request): View
    {
        $data = $this->financeService->commissions($request->query());

        return view('admin.providers.finance.commissions', $data);
    }

    /**
     * تسوية عمولة أو التبديل بين معلقة ومسددة.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $entry = $this->financeService->toggleStatus($id);

        $statusLabel = $entry->status?->code === 'settled' ? __('مسددة') : __('معلقة');

        return back()->with('status', __('تم تحديث حالة العمولة بنجاح إلى: :status', ['status' => $statusLabel]));
    }
}
