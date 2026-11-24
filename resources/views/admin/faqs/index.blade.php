@extends('layouts.admin')
@section('title', 'إدارة الأسئلة الشائعة · Rounder')

@section('body')
<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-question text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>إدارة الأسئلة الشائعة</span>
            </h1>
            <p class="text-secondary mb-0 small">إضافة وتعديل وترتيب الأسئلة والإجابات المعروضة في مركز المساعدة والصفحة الرئيسية.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('faq') }}" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 rounded-3">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>معاينة الصفحة العامة</span>
            </a>
            @adminCan('admin.faqs.create')
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 rounded-3">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة سؤال جديد</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Filter & Search Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3" style="background: #ffffff;">
        <form method="GET" action="{{ route('admin.faqs.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control border-start-0" placeholder="ابحث في نص السؤال أو الإجابة...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="category" class="form-select">
                    <option value="all">كافة التصنيفات</option>
                    @foreach($categories as $catKey => $catInfo)
                        <option value="{{ $catKey }}" @selected(($category ?? '') === $catKey)>
                            {{ $catInfo['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3">
                    <i class="fa-solid fa-filter me-1"></i> تصفية
                </button>
                @if(!empty($q) || (!empty($category) && $category !== 'all'))
                    <a href="{{ route('admin.faqs.index') }}" class="btn btn-light border rounded-3" title="إعادة تعيين">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- FAQs Table --}}
    <div class="panel p-3 p-lg-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th style="width: 170px;">التصنيف</th>
                        <th>السؤال والإجابة</th>
                        <th class="text-center" style="width: 80px;">الترتيب</th>
                        <th class="text-center" style="width: 110px;">الحالة</th>
                        <th class="text-end pe-3" style="width: 150px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $item)
                        @php
                            $transAr = $item->translations->firstWhere('locale', 'ar');
                            $badgeStyle = match($item->category) {
                                'individuals' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
                                'pricing' => 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                'business' => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                                'providers' => 'background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;',
                                default => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                            };
                        @endphp
                        <tr>
                            <td class="fw-semibold text-secondary">#{{ $item->id }}</td>
                            <td>
                                <span class="badge rounded-pill fw-semibold px-2.5 py-1.5" style="{{ $badgeStyle }}">
                                    <i class="{{ $item->category_icon }} me-1"></i>
                                    {{ $item->category_label }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    {{ $transAr?->question ?? '—' }}
                                </div>
                                <div class="text-secondary small text-truncate" style="max-width: 460px;">
                                    {{ Str::limit(strip_tags($transAr?->answer ?? ''), 110) }}
                                </div>
                            </td>
                            <td class="text-center font-monospace fw-bold text-secondary">
                                {{ $item->sort_order }}
                            </td>
                            <td class="text-center">
                                @adminCan('admin.faqs.edit')
                                <form method="POST" action="{{ route('admin.faqs.toggle', $item->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="انقر لتغيير الحالة">
                                        @if($item->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle-check me-1"></i> نشط
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> معطل
                                            </span>
                                        @endif
                                    </button>
                                </form>
                                @else
                                    @if($item->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">نشط</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">معطل</span>
                                    @endif
                                @endadminCan
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @adminCan('admin.faqs.edit')
                                <a href="{{ route('admin.faqs.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-2 px-2.5 py-1 me-1">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                    <span>تعديل</span>
                                </a>
                                @endadminCan

                                @adminCan('admin.faqs.delete')
                                <form method="POST" action="{{ route('admin.faqs.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا السؤال نهائياً؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="حذف">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                                @endadminCan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-circle-question fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">لا توجد أسئلة شائعة مطابقة</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection

