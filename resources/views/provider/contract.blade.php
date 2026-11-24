@extends('layouts.provider')
@section('title', __('Provider contract'))
@section('body')
<div class="row justify-content-center py-4 contract-page-wrapper">
    <div class="col-12 col-lg-9">
        <section class="panel p-4 p-lg-5 contract-panel">
            <div class="mb-4 d-print-none">
                <div class="text-secondary">{{ __('Contract reference') }}: {{ $contract->reference }}</div>
                <h1 class="h3 fw-bold mt-2">{{ __('Provider contract') }}</h1>
                <p class="text-secondary mb-0">{{ $profile->contract_accepted_at ? __('Your contract has been accepted.') : __('Review the contract and accept it to access the provider dashboard.') }}</p>
            </div>
            @if($terms)
                <div class="border rounded-3 p-3 p-lg-4 mb-4 contract-terms-wrapper">{!! $terms !!}</div>
                <div class="d-flex flex-wrap gap-2 d-print-none mb-4">
                    <a class="btn btn-primary" href="{{ route('provider.contract.pdf') }}"><i class="fa-solid fa-file-pdf me-1"></i>{{ __('Download contract PDF') }}</a>
                    <button class="btn btn-outline-secondary" type="button" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>{{ __('Print contract') }}</button>
                </div>
                @unless($profile->contract_accepted_at)
                    <form method="post" action="{{ route('provider.contract.accept') }}" class="d-print-none">
                        @csrf
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="accept-terms" name="accept_contract" value="1" required>
                            <label class="form-check-label" for="accept-terms">{{ __('I confirm that I have read and agree to the contract terms.') }}</label>
                        </div>
                        <button class="btn btn-orange" type="submit">{{ __('Accept contract and continue') }}</button>
                    </form>
                @endunless
            @else
                <div class="alert alert-warning mb-0">{{ __('The contract terms are not available yet.') }}</div>
            @endif
        </section>
    </div>
</div>

@push('styles')
<style>
@media print {
    *, *::before, *::after {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    @page {
        margin: 12mm 10mm;
        size: A4 portrait;
    }
    body {
        background: #ffffff !important;
        color: #1e293b !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    .topbar, .provider-sidebar, #provider-menu, .modal, .d-print-none, .btn {
        display: none !important;
    }
    .provider-main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .contract-page-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .contract-page-wrapper .col-12,
    .contract-page-wrapper .col-lg-9 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .contract-page-wrapper .panel,
    .contract-panel {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
    }
    .contract-terms-wrapper {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .rounder-contract-doc {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    table, tr, td {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        page-break-inside: avoid;
        break-inside: avoid;
    }
}
</style>
@endpush
@endsection
