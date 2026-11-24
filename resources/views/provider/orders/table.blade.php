@php
    $canManage = $canManage ?? (auth('provider')->check() && app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile ?? app(\App\Modules\Providers\Services\ProviderPortalService::class)->profileFor(auth('provider')->user()), 'orders.manage'));
    $techniciansList = $technicians ?? collect();
@endphp
<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                @if($canManage)
                    <th style="width: 38px;">
                        <input type="checkbox" id="selectAllProviderOrders" class="form-check-input" title="{{ __('Select all') }}">
                    </th>
                @endif
                <th>{{ __('Order') }}</th>
                <th>{{ __('Resident and home') }}</th>
                <th>{{ __('Service') }}</th>
                <th>{{ __('Technician / Delegate') }}</th>
                <th>{{ __('Scheduled at') }}</th>
                <th>{{ __('Status') }}</th>
                @if($canManage)
                    <th class="text-end" style="width: 130px;">{{ __('Actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                @if($canManage)
                    <td>
                        <input type="checkbox" value="{{ $order->id }}" class="form-check-input provider-order-cb" data-order-id="{{ $order->id }}">
                    </td>
                @endif
                <td><a href="{{ route('provider.orders.show', $order->id) }}" class="fw-bold text-decoration-none">#{{ $order->id }}</a></td>
                <td>
                    <div class="fw-semibold text-dark">{{ $order->resident_name ?? $order->resident?->name }}</div>
                    <div class="small text-secondary">{{ $order->home_name ?? $order->home?->name }} @if($order->unit_label) · {{ $order->unit_label }} @endif</div>
                </td>
                <td>{{ $order->service_name ?? $order->service_code ?? $order->service?->translations?->firstWhere('locale', app()->getLocale())?->name }}</td>
                <td>
                    @if($order->technician_name)
                        <span class="badge" style="background: #e8f4f8; color: var(--petrol); font-weight: 600;">
                            <i class="fa-solid fa-user-gear text-rounder-orange me-1"></i>{{ $order->technician_name }}
                        </span>
                    @else
                        <span class="text-secondary small fst-italic">—</span>
                    @endif
                </td>
                <td>{{ $order->scheduled_at?->translatedFormat('Y-m-d H:i') }}</td>
                <td>
                    <span class="badge text-bg-light border">
                        {{ $order->status_name ?? $order->status?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $order->status?->code }}
                    </span>
                </td>
                @if($canManage)
                    <td class="text-end">
                        <div class="d-inline-flex gap-1 align-items-center">
                            @if(in_array($order->status_code ?? $order->status?->code, ['pending', 'confirmed', 'in_progress'], true))
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#providerOrderModal-{{ $order->id }}" style="border-radius: 8px; font-size: 0.8rem; padding: 0.32rem 0.65rem;">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>{{ __('Quick update') }}</span>
                                </button>
                            @endif
                            <a href="{{ route('provider.orders.show', $order->id) }}" class="btn btn-sm btn-light border d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; width: 32px; height: 32px; padding: 0;" title="{{ __('View details') }}">
                                <i class="fa-solid fa-eye text-secondary"></i>
                            </a>
                        </div>

                        <!-- Individual Order Quick Update Modal (Status + Technician in the SAME form!) -->
                        <div class="modal fade" id="providerOrderModal-{{ $order->id }}" tabindex="-1" aria-labelledby="providerOrderModalLabel-{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered text-start">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header py-3 px-4" style="background: var(--petrol); color: #fff;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge" style="background: var(--orange); color: #fff; font-size: 0.85rem; padding: 0.35rem 0.7rem; border-radius: 8px;">
                                                #{{ $order->id }}
                                            </span>
                                            <h5 class="modal-title fw-bold mb-0 fs-6" id="providerOrderModalLabel-{{ $order->id }}">
                                                {{ __('Update status & delegate') }}
                                            </h5>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="post" action="{{ route('provider.orders.update', $order->id) }}">
                                        @csrf
                                        @method('put')
                                        <div class="modal-body p-4">
                                            <div class="p-3 rounded-3 mb-3" style="background: #f7faf9; border: 1px solid #dce8e4;">
                                                <div class="fw-bold text-dark">{{ $order->resident_name ?? $order->resident?->name }}</div>
                                                <div class="small text-secondary">{{ $order->service_name ?? $order->service_code }} · {{ $order->home_name ?? $order->home?->name }}</div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-dark" for="modal_status_{{ $order->id }}">
                                                    <i class="fa-solid fa-circle-dot text-rounder-orange me-1"></i> {{ __('Status') }} <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-select" id="modal_status_{{ $order->id }}" name="status" required style="border-radius: 10px;" onchange="const gWrap = document.getElementById('modal_gross_{{ $order->id }}'); if (gWrap) gWrap.style.display = (this.value === 'completed' ? 'block' : 'none');">
                                                    @php
                                                        $currentCode = $order->status_code ?? $order->status?->code;
                                                        $seq = ['pending' => ['confirmed'], 'confirmed' => ['in_progress'], 'in_progress' => ['completed']];
                                                        $nextOptions = $seq[$currentCode] ?? ['confirmed', 'in_progress', 'completed'];
                                                    @endphp
                                                    @foreach(['confirmed', 'in_progress', 'completed'] as $stCode)
                                                        @php
                                                            $stLabel = match($stCode) {
                                                                'confirmed' => __('Accept order'),
                                                                'in_progress' => __('Start work'),
                                                                'completed' => __('Complete order'),
                                                                default => $stCode,
                                                            };
                                                        @endphp
                                                        <option value="{{ $stCode }}" @selected($currentCode === $stCode)>{{ $stLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-dark" for="modal_technician_{{ $order->id }}">
                                                    <i class="fa-solid fa-user-gear text-rounder-orange me-1"></i> {{ __('Technician / Delegate') }}
                                                </label>
                                                <select class="form-select" id="modal_technician_{{ $order->id }}" name="technician_id" style="border-radius: 10px;">
                                                    <option value="">{{ __('No technician / Not assigned') }}</option>
                                                    <option value="unassign">{{ __('Unassign technician') }}</option>
                                                    @foreach($techniciansList as $tech)
                                                        <option value="{{ $tech->id }}" @selected((int)$order->technician_id === (int)$tech->id)>{{ $tech->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="modal_gross_{{ $order->id }}" class="mb-3" style="display: none;">
                                                <label class="form-label fw-bold small text-dark" for="modal_amount_{{ $order->id }}">{{ __('Final amount') }}</label>
                                                <input class="form-control" id="modal_amount_{{ $order->id }}" name="gross_amount" type="number" min="0" step="0.01" value="{{ old('gross_amount', $order->total_amount) }}" style="border-radius: 10px;">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-dark" for="modal_note_{{ $order->id }}">{{ __('Note') }}</label>
                                                <textarea class="form-control" id="modal_note_{{ $order->id }}" name="note" maxlength="2000" rows="2" style="border-radius: 10px;"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer py-3 px-4 bg-light d-flex justify-content-between">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal" style="border-radius: 8px;">
                                                {{ __('Cancel') }}
                                            </button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 8px; font-weight: 600;">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save order update') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $canManage ? 8 : 6 }}" class="text-center text-secondary py-4">
                    <i class="fa-solid fa-clipboard-list fs-1 mb-2 text-secondary opacity-50"></i>
                    <div>{{ __('No service orders found.') }}</div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
