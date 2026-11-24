@extends('layouts.admin')
@section('title', 'تعديل السؤال الشائع · Rounder')

@section('body')
<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>تعديل السؤال الشائع #{{ $faq->id }}</span>
            </h1>
            <p class="text-secondary mb-0 small">تعديل نصوص السؤال والإجابة والتصنيف وحالة النشر.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-arrow-right me-1"></i> العودة للقائمة
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Column 1: Question and Answer (Arabic & English) --}}
            <div class="col-12 col-lg-8">
                {{-- Arabic Content --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #ffffff;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">AR</span>
                        <h5 class="fw-bold text-dark mb-0">المحتوى باللغة العربية (إلزامي)</h5>
                    </div>

                    <div class="mb-3">
                        <label for="question_ar" class="form-label fw-bold text-dark small">نص السؤال <span class="text-danger">*</span></label>
                        <input type="text" name="question_ar" id="question_ar" class="form-control @error('question_ar') is-invalid @enderror" value="{{ old('question_ar', $transAr?->question) }}" required placeholder="مثال: كيف أطلب خدمة صيانة منزلية من راوندر؟">
                        @error('question_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="answer_ar" class="form-label fw-bold text-dark small">نص الإجابة والتوضيح <span class="text-danger">*</span></label>
                        <textarea name="answer_ar" id="answer_ar" rows="5" class="form-control @error('answer_ar') is-invalid @enderror" required placeholder="اكتب الإجابة المفصلة التي ستظهر للمستخدم...">{{ old('answer_ar', $transAr?->answer) }}</textarea>
                        @error('answer_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- English Content (Optional) --}}
                <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">EN</span>
                        <h5 class="fw-bold text-dark mb-0">English Content (Optional)</h5>
                    </div>

                    <div class="mb-3">
                        <label for="question_en" class="form-label fw-bold text-dark small">Question (English)</label>
                        <input type="text" name="question_en" id="question_en" class="form-control @error('question_en') is-invalid @enderror" value="{{ old('question_en', $transEn?->question) }}" dir="ltr" placeholder="e.g. How do I book a home maintenance service?">
                        @error('question_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="answer_en" class="form-label fw-bold text-dark small">Answer (English)</label>
                        <textarea name="answer_en" id="answer_en" rows="4" class="form-control @error('answer_en') is-invalid @enderror" dir="ltr" placeholder="Detailed answer in English...">{{ old('answer_en', $transEn?->answer) }}</textarea>
                        @error('answer_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Column 2: Settings & Placement --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #ffffff;">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">إعدادات النشر والتصنيف</h5>

                    {{-- Category --}}
                    <div class="mb-3">
                        <label for="category" class="form-label fw-bold text-dark small">تصنيف السؤال <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            @foreach($categories as $catKey => $catInfo)
                                <option value="{{ $catKey }}" @selected(old('category', $faq->category) === $catKey)>
                                    {{ $catInfo['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Sort Order --}}
                    <div class="mb-3">
                        <label for="sort_order" class="form-label fw-bold text-dark small">ترتيب الظهور (الأقل أولاً)</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control font-monospace @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $faq->sort_order) }}" min="0" max="9999">
                        <div class="form-text small text-muted">الرقم 1 يظهر قبل 2 وهكذا.</div>
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Active Toggle --}}
                    <div class="form-check form-switch mt-3 pt-2 border-top">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $faq->is_active))>
                        <label class="form-check-label fw-bold text-dark small" for="is_active">تفعيل السؤال وعرضه للجمهور</label>
                    </div>

                    <div class="mt-3 pt-2 border-top small text-secondary">
                        <div>تاريخ الإنشاء: <span dir="ltr">{{ $faq->created_at?->format('Y-m-d H:i') }}</span></div>
                        <div>آخر تحديث: <span dir="ltr">{{ $faq->updated_at?->format('Y-m-d H:i') }}</span></div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('admin.faqs.index') }}" class="btn btn-light border w-100 py-2 rounded-3 text-secondary">
                        إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</main>
@endsection

