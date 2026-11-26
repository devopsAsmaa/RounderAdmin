@extends('layouts.admin')
@section('title', __('Providers and contracts').' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-handshake text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Providers and contracts') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage registered service providers and active contracts.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-building me-1 text-primary"></i>
                {{ __('Total providers') }}: <strong class="text-dark">{{ $providers->total() }}</strong>
            </span>
            @adminCan('admin.providers.create')
            <a class="btn btn-primary shadow-sm" href="{{ route('admin.providers.create') }}">
                <i class="fa-solid fa-plus me-1"></i> {{ __('Add provider') }}
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4">
        {{-- Luxury Search & Filters Bar --}}
        <form method="get" action="{{ route('admin.providers.index') }}" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-7 col-lg-5">
                    <div class="input-group search-input-group shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #dce6e3;">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 py-2"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Search company, contact, email or phone') }}..."
                               aria-label="{{ __('Search') }}">
                        @if(request('q'))
                            <a href="{{ route('admin.providers.index') }}" class="btn btn-white border-0 text-muted d-flex align-items-center px-2" title="{{ __('Clear search') }}" style="background: #fff;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-semibold border-0">
                            <i class="fa-solid fa-filter me-1"></i> {{ __('Filter') }}
                        </button>
                    </div>
                </div>

                @if(request('q'))
                    <div class="col-auto">
                        <span class="pill" style="background:#eaf2f4; color:var(--petrol);">
                            {{ __('Search') }}: <strong>"{{ request('q') }}"</strong>
                            <a href="{{ route('admin.providers.index') }}" class="text-danger ms-1 text-decoration-none" title="{{ __('Clear') }}">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    </div>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle" style="min-width: 780px;">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>{{ __('Company name') }}</th>
                        <th>{{ __('Responsible and contact') }}</th>
                        <th>{{ __('Approval') }}</th>
                        <th>{{ __('Commission rate') }}</th>
                        <th>{{ __('Commission accrued') }}</th>
                        <th>{{ __('Contract') }}</th>
                        <th style="width: 100px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        <tr>
                            <td class="text-muted text-nowrap">{{ $provider->provider_id }}</td>
                            <td class="text-nowrap">
                                <div class="fw-bold text-dark">{{ $provider->company_name }}</div>
                            </td>
                            <td class="text-nowrap">
                                <div class="fw-semibold text-dark">{{ $provider->name }}</div>
                                <div class="small text-muted mt-1 d-flex flex-column gap-1">
                                    @if($provider->phone)
                                        <div class="d-inline-flex align-items-center gap-1 text-nowrap">
                                            <i class="fa-solid fa-phone text-secondary opacity-75" style="font-size: 0.72rem;"></i>
                                            <span dir="ltr" class="font-monospace">{{ $provider->phone }}</span>
                                        </div>
                                    @endif
                                    @if($provider->email)
                                        <div class="d-inline-flex align-items-center gap-1 text-nowrap">
                                            <i class="fa-solid fa-envelope text-secondary opacity-75" style="font-size: 0.72rem;"></i>
                                            <span class="font-monospace text-secondary">{{ $provider->email }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-nowrap">
                                @if($provider->is_approved)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i>{{ __('Approved') }}
                                    </span>
                                @else
                                    @adminCan('admin.providers.edit')
                                        <button type="button" 
                                                class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 btn btn-link text-decoration-none d-inline-flex align-items-center gap-1 shadow-none"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveProviderModal{{ $provider->provider_id }}"
                                                title="اضغط لتأكيد الاعتماد">
                                            <i class="fa-solid fa-hourglass-half"></i>
                                            <span>{{ __('Pending approval') }}</span>
                                            <i class="fa-solid fa-arrow-pointer ms-1 small opacity-75"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Pending approval') }}
                                        </span>
                                    @endadminCan
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex flex-column gap-1" style="font-size: 0.82rem;">
                                    <span>
                                        <span class="text-muted">{{ __('Regular') }}:</span>
                                        <strong class="text-dark">{{ $provider->service_commission_percent !== null ? $provider->service_commission_percent.'%' : '—' }}</strong>
                                    </span>
                                    <span>
                                        <span class="text-muted">{{ __('Emergency') }}:</span>
                                        <strong class="text-dark">{{ $provider->emergency_commission_percent !== null ? $provider->emergency_commission_percent.'%' : '—' }}</strong>
                                    </span>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <strong class="text-primary font-monospace" style="font-size: 0.92rem;">
                                    {{ number_format($provider->commission_total ?? 0, 2) }} <span class="small fw-normal text-muted">{{ __('SAR') }}</span>
                                </strong>
                            </td>
                            <td class="text-nowrap">
                                <div class="small fw-bold text-dark font-monospace">
                                    {{ $provider->contract_reference ?? '—' }}
                                </div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    @if($provider->contract_status)
                                        <span class="badge {{ $provider->contract_status === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-secondary border' }}" style="font-size: 0.72rem;">
                                            {{ __($provider->contract_status) }}
                                        </span>
                                    @endif
                                    <a class="btn btn-sm btn-outline-danger py-0 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.76rem; border-radius: 6px;" href="{{ route('admin.providers.contract.pdf', $provider->provider_id) }}" title="{{ __('Download contract PDF') }}">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <span>{{ __('Download contract PDF') }}</span>
                                    </a>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center gap-1">
                                    @if(!$provider->is_approved)
                                        @adminCan('admin.providers.edit')
                                            <button type="button" 
                                                    class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm px-2.5 py-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveProviderModal{{ $provider->provider_id }}"
                                                    title="اعتماد وتفعيل حساب المزود">
                                                <i class="fa-solid fa-check"></i>
                                                <span>اعتماد</span>
                                            </button>
                                        @endadminCan
                                    @endif
                                    @adminCan('admin.providers.edit')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.providers.edit', $provider->provider_id) }}" title="{{ __('Edit') }}">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('Edit') }}
                                        </a>
                                    @endadminCan
                                    @adminCan('admin.providers.destroy', 'DELETE')
                                        @include('admin.partials.delete-button', ['action' => route('admin.providers.destroy', $provider->provider_id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-building-circle-xmark fa-2x mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-bold fs-6 text-dark">{{ __('No providers found') }}</div>
                                    <div class="small text-secondary mt-1">{{ __('Try adjusting your search criteria') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="mt-3 pt-3 border-top">
                {{ $providers->links() }}
            </div>
        @endif
    </div>

    {{-- مودالات اعتماد المزودين --}}
    @foreach($providers as $provider)
        @if(!$provider->is_approved)
            <div class="modal fade" id="approveProviderModal{{ $provider->provider_id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $provider->provider_id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-bottom pb-3">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                                    <i class="fa-solid fa-shield-check fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold mb-0 text-dark" id="approveModalLabel{{ $provider->provider_id }}">اعتماد مزود الخدمة</h5>
                                    <span class="text-muted small">تفعيل حساب المنشأة وإرسال رسالة تفعيل نصية SMS</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.providers.approve', $provider->provider_id) }}">
                            @csrf
                            <div class="modal-body py-3">
                                <div class="p-3 rounded-3 bg-light border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light-subtle">
                                        <span class="text-secondary small">اسم المنشأة:</span>
                                        <strong class="text-dark">{{ $provider->company_name }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light-subtle">
                                        <span class="text-secondary small">الشخص المسؤول:</span>
                                        <span class="fw-semibold text-dark">{{ $provider->name }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light-subtle">
                                        <span class="text-secondary small">رقم الجوال:</span>
                                        <span dir="ltr" class="font-monospace fw-bold text-primary">{{ $provider->phone ?: '—' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary small">مرجع العقد:</span>
                                        <span class="badge bg-white text-secondary border font-monospace">{{ $provider->contract_reference ?? '—' }}</span>
                                    </div>
                                </div>

                                <div class="alert alert-success d-flex align-items-start gap-2.5 py-2.5 px-3 mb-0 small rounded-3">
                                    <i class="fa-solid fa-paper-plane mt-1 text-success fs-6"></i>
                                    <div>
                                        عند الضغط على تأكيد الاعتماد، سيتم تفعيل حساب المزود، تفعيل العقد الرسمي، وإرسال رسالة <strong>SMS نصية فورية</strong> لجوال الشريك لإبلاغه بتفعيل حسابه في راوندر مع رابط تسجيل الدخول.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top pt-2">
                                <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success rounded-3 px-4 fw-bold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>تأكيد الاعتماد وإرسال SMS</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</main>
@endsection
