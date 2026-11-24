<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Faq;
use App\Modules\Settings\Models\FaqTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AdminFaqService
{
    /**
     * قائمة الأسئلة مع التصفية والبحث والترتيب.
     */
    public function list(?string $category = null, ?string $q = null): Collection
    {
        $query = Faq::with('translations')->ordered();

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        if (!empty($q)) {
            $query->whereHas('translations', function ($sq) use ($q) {
                $sq->where('question', 'like', "%{$q}%")
                   ->orWhere('answer', 'like', "%{$q}%");
            });
        }

        return $query->get();
    }

    /**
     * جلب سؤال محدد مع ترجماته.
     */
    public function find(int $id): Faq
    {
        return Faq::with('translations')->findOrFail($id);
    }

    /**
     * إضافة سؤال جديد وترجماته.
     */
    public function create(array $data): Faq
    {
        return DB::transaction(function () use ($data) {
            $faq = Faq::create([
                'category' => $data['category'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->saveTranslations($faq, $data);

            return $faq;
        });
    }

    /**
     * تحديث بيانات السؤال وترجماته.
     */
    public function update(int $id, array $data): Faq
    {
        return DB::transaction(function () use ($id, $data) {
            $faq = $this->find($id);

            $faq->update([
                'category' => $data['category'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $this->saveTranslations($faq, $data);

            return $faq;
        });
    }

    /**
     * حذف سؤال.
     */
    public function delete(int $id): bool
    {
        $faq = $this->find($id);
        return $faq->delete();
    }

    /**
     * تبديل حالة التفعيل.
     */
    public function toggle(int $id): bool
    {
        $faq = $this->find($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return $faq->is_active;
    }

    /**
     * حفظ وتحديث الترجمات (عربي وإنجليزي).
     */
    private function saveTranslations(Faq $faq, array $data): void
    {
        // العربية (إلزامية)
        FaqTranslation::updateOrCreate(
            ['faq_id' => $faq->id, 'locale' => 'ar'],
            [
                'question' => $data['question_ar'],
                'answer' => $data['answer_ar'],
            ]
        );

        // الإنجليزية (اختيارية)
        if (!empty($data['question_en']) && !empty($data['answer_en'])) {
            FaqTranslation::updateOrCreate(
                ['faq_id' => $faq->id, 'locale' => 'en'],
                [
                    'question' => $data['question_en'],
                    'answer' => $data['answer_en'],
                ]
            );
        }
    }
}

