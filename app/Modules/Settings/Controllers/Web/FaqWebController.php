<?php

namespace App\Modules\Settings\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Settings\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqWebController extends Controller
{
    /**
     * عرض صفحة الأسئلة الشائعة المتكاملة.
     */
    public function index(Request $request): View
    {
        $settings = CompanySetting::current();
        $categories = $this->getFaqData();

        return view('pages.faq', compact('settings', 'categories'));
    }

    /**
     * جلب قائمة الأسئلة والأجوبة المصنفة ديناميكياً من قاعدة البيانات.
     */
    private function getFaqData(): array
    {
        $allFaqs = Faq::with('translations')->active()->ordered()->get();
        $metaCategories = Faq::categories();
        $result = [];

        foreach ($metaCategories as $catKey => $catMeta) {
            $catFaqs = $allFaqs->where('category', $catKey);

            if ($catFaqs->isNotEmpty()) {
                $questions = [];
                foreach ($catFaqs as $faq) {
                    $questions[] = [
                        'id' => $faq->id,
                        'q' => $faq->question,
                        'a' => $faq->answer,
                    ];
                }

                $result[] = [
                    'id' => $catKey,
                    'name' => $catMeta['name'],
                    'icon' => $catMeta['icon'],
                    'description' => $catMeta['description'],
                    'questions' => $questions,
                ];
            }
        }

        return $result;
    }
}
