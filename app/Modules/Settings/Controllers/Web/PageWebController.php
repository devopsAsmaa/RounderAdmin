<?php

namespace App\Modules\Settings\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * متحكم عرض الصفحات الثابتة (سياسة الخصوصية، الشروط والأحكام) للويب وتطبيقات الجوال.
 */
class PageWebController extends Controller
{
    /**
     * عرض صفحة سياسة الخصوصية.
     */
    public function privacy(Request $request): View
    {
        return $this->renderPage('privacy', $request);
    }

    /**
     * عرض صفحة الشروط والأحكام.
     */
    public function terms(Request $request): View
    {
        return $this->renderPage('terms', $request);
    }

    /**
     * عرض أي صفحة ثابتة بناءً على الـ slug.
     */
    public function show(string $slug, Request $request): View
    {
        return $this->renderPage($slug, $request);
    }

    /**
     * جلب بيانات الصفحة وترجمتها وعرضها في القالب.
     */
    private function renderPage(string $slug, Request $request): View
    {
        $page = Page::with('translations')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $locale = app()->getLocale();
        $translation = $page->translations->firstWhere('locale', $locale)
            ?? $page->translations->firstWhere('locale', 'ar')
            ?? $page->translations->first();

        return view('pages.show', [
            'page' => $page,
            'title' => $translation?->title ?? $page->slug,
            'content' => $translation?->content ?? '',
            'updatedAt' => $page->updated_at,
        ]);
    }
}

