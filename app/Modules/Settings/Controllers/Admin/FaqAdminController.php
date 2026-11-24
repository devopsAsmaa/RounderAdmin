<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Faq;
use App\Modules\Settings\Requests\AdminFaqStoreRequest;
use App\Modules\Settings\Requests\AdminFaqUpdateRequest;
use App\Modules\Settings\Services\AdminFaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqAdminController extends Controller
{
    public function __construct(private readonly AdminFaqService $service)
    {
    }

    /**
     * عرض قائمة الأسئلة الشائعة مع التصفية والبحث.
     */
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $q = $request->input('q');

        $faqs = $this->service->list($category, $q);
        $categories = Faq::categories();

        return view('admin.faqs.index', compact('faqs', 'categories', 'category', 'q'));
    }

    /**
     * شاشة إضافة سؤال جديد.
     */
    public function create(): View
    {
        $categories = Faq::categories();
        return view('admin.faqs.create', compact('categories'));
    }

    /**
     * حفظ السؤال الجديد.
     */
    public function store(AdminFaqStoreRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.faqs.index')->with('status', 'تمت إضافة السؤال الجديد بنجاح.');
    }

    /**
     * شاشة تعديل السؤال.
     */
    public function edit(int $id): View
    {
        $faq = $this->service->find($id);
        $categories = Faq::categories();

        $transAr = $faq->translations->firstWhere('locale', 'ar');
        $transEn = $faq->translations->firstWhere('locale', 'en');

        return view('admin.faqs.edit', compact('faq', 'categories', 'transAr', 'transEn'));
    }

    /**
     * تحديث بيانات السؤال.
     */
    public function update(AdminFaqUpdateRequest $request, int $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return redirect()->route('admin.faqs.index')->with('status', 'تم تحديث السؤال بنجاح.');
    }

    /**
     * حذف سؤال.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()->route('admin.faqs.index')->with('status', 'تم حذف السؤال بنجاح.');
    }

    /**
     * تفعيل أو تعطيل السؤال.
     */
    public function toggle(int $id): RedirectResponse
    {
        $state = $this->service->toggle($id);
        $msg = $state ? 'تم تفعيل السؤال بنجاح.' : 'تم تعطيل السؤال بنجاح.';

        return redirect()->route('admin.faqs.index')->with('status', $msg);
    }
}

