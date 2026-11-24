@extends('layouts.provider')
@section('title', __('Service orders'))
@section('body')
@php
    $canManage = app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile ?? app(\App\Modules\Providers\Services\ProviderPortalService::class)->profileFor(auth('provider')->user()), 'orders.manage');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">{{ __('Service orders') }}</h1>
    </div>
    @if(!empty($visibleStatuses) && $visibleStatuses->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('provider.orders.index') }}" class="btn btn-sm {{ empty($selectedStatus) ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ __('All') }}
            </a>
            @foreach($visibleStatuses as $st)
                <a href="{{ route('provider.orders.index', ['status' => $st->code]) }}" class="btn btn-sm {{ ($selectedStatus ?? null) === $st->code ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? $st->code }}
                </a>
            @endforeach
        </div>
    @endif
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-check-circle me-1"></i> {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($canManage)
    <div class="panel p-3 mb-4 shadow-sm" style="border-radius: 14px; border: 1.5px solid #dce8e4; background: #ffffff;">
        <form id="providerBulkActionForm" method="post" action="{{ route('provider.orders.bulk-update') }}">
            @csrf
            @method('put')
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(2, 51, 62, 0.08); color: var(--petrol);">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark small">{{ __('Bulk action') }}</div>
                        <span class="badge rounded-pill bg-light text-secondary border fw-normal" id="providerSelectedBadge">
                            <span id="providerSelectedCount">0</span> {{ __('orders selected') }}
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-end">
                    <!-- Status select -->
                    <div style="min-width: 170px;">
                        <select class="form-select form-select-sm" name="status" style="border-radius: 8px;">
                            <option value="">-- {{ __('Status (no change)') }} --</option>
                            @foreach($visibleStatuses as $st)
                                @if(in_array($st->code, ['confirmed', 'in_progress', 'completed']))
                                    <option value="{{ $st->code }}">
                                        {{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? $st->code }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Technician / Delegate select -->
                    <div style="min-width: 185px;">
                        <select class="form-select form-select-sm" name="technician_id" style="border-radius: 8px;">
                            <option value="">-- {{ __('Technician (no change)') }} --</option>
                            <option value="unassign">{{ __('Unassign technician') }}</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Note input -->
                    <div style="min-width: 160px;">
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="{{ __('Note (optional)') }}" style="border-radius: 8px;" maxlength="500">
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" id="providerBulkSubmitBtn" disabled style="border-radius: 8px; font-weight: 600; padding: 0.42rem 1rem;">
                        <i class="fa-solid fa-check-double"></i>
                        <span>{{ __('Apply to selected') }}</span>
                    </button>
                </div>
            </div>
            <!-- Hidden container for selected order IDs -->
            <div id="providerSelectedOrdersContainer"></div>
        </form>
    </div>
@endif

<section class="panel p-3 p-lg-4">
    @include('provider.orders.table', ['orders' => $orders, 'technicians' => $technicians ?? collect(), 'canManage' => $canManage])
    <div class="mt-3">{{ $orders->links() }}</div>
</section>

@if($canManage)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bulkForm = document.getElementById('providerBulkActionForm');
    const selectAll = document.getElementById('selectAllProviderOrders');
    const checkboxes = document.querySelectorAll('.provider-order-cb');
    const countEl = document.getElementById('providerSelectedCount');
    const submitBtn = document.getElementById('providerBulkSubmitBtn');
    const container = document.getElementById('providerSelectedOrdersContainer');

    function updateSelection() {
        const selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                selected.push(cb.value);
            }
        });

        if (countEl) {
            countEl.textContent = selected.length;
        }

        if (submitBtn) {
            submitBtn.disabled = selected.length === 0;
        }

        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        }

        if (container) {
            container.innerHTML = '';
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateSelection();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const selected = container ? container.querySelectorAll('input[name="order_ids[]"]') : [];
            if (selected.length === 0) {
                e.preventDefault();
                alert(@json(__('Select at least one order.')));
                return;
            }

            const statusVal = bulkForm.querySelector('[name="status"]')?.value;
            const techVal = bulkForm.querySelector('[name="technician_id"]')?.value;

            if (!statusVal && !techVal) {
                e.preventDefault();
                alert(@json(__('Select a status or a technician to update.')));
                return;
            }
        });
    }
});
</script>
@endif
@endsection
