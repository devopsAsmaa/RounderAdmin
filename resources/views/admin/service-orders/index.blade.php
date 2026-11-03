@extends('layouts.admin')
@section('title', __('Service orders').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <div class="small text-secondary">{{ __('Provider assignment and commissions') }}</div>
        <h1 class="h3 fw-bold mb-0">{{ __('Service orders') }}</h1>
    </div>
    @adminCan('admin.service-orders.create')
    <a class="btn btn-primary" href="{{ route('admin.service-orders.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create service order') }}
    </a>
    @endadminCan
</div>
@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('Order') }}</th><th>{{ __('Resident and home') }}</th><th>{{ __('Service') }}</th><th>{{ __('Photos') }}</th><th>{{ __('Status') }}</th><th>{{ __('Assignment and commission') }}</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td><a href="{{ route('admin.service-orders.show', $order->id) }}" class="fw-bold text-decoration-none fs-6" style="color: var(--petrol);">#{{ $order->id }}</a><div class="small text-secondary">{{ $order->scheduled_at }}</div>@if($order->notes)<div class="small text-muted mt-1 text-truncate" style="max-width:180px" title="{{ $order->notes }}">{{ $order->notes }}</div>@endif<div class="mt-1"><a href="{{ route('admin.service-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.75rem"><i class="fa-solid fa-eye me-1"></i>{{ __('View details') }}</a></div></td>
<td>{{ $order->resident_name }}<div class="small text-secondary">{{ $order->home_name }}</div></td>
<td>{{ $order->service_name ?? $order->service_code }}@if($order->sub_service_name || $order->sub_service_code)<div class="small text-primary">{{ $order->sub_service_name ?? $order->sub_service_code }}</div>@endif<div class="small text-secondary">{{ number_format($order->total_amount,2) }} SAR</div></td>
<td>
@if($order->bookingMedia && $order->bookingMedia->isNotEmpty())
<div class="d-flex flex-wrap gap-1 align-items-center" style="max-width:160px">
@foreach($order->bookingMedia as $media)
<a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" title="{{ __('View full image') }}" class="d-inline-block">
<img src="{{ $media->url }}" alt="Booking photo" class="rounded border shadow-sm" style="width:40px;height:40px;object-fit:cover;cursor:pointer;">
</a>
@endforeach
</div>
@else
<span class="text-secondary small">—</span>
@endif
</td>
<td><span class="pill">{{ $order->status_name }}</span></td>
<td style="min-width:350px">@adminCan('admin.service-orders.update','PUT')<form method="post" action="{{ route('admin.service-orders.update',$order->id) }}" class="d-grid gap-2" enctype="multipart/form-data">@csrf @method('put')
<select class="form-select form-select-sm provider-select" name="provider_profile_id"><option value="">{{ __('Choose provider') }}</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($order->provider_profile_id===$provider->id)>{{ $provider->company_name }} · {{ $provider->name }}</option>@endforeach</select>
<select class="form-select form-select-sm order-status" name="booking_status_id" required>@foreach($statuses as $status)<option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($order->status_code===$status->code)>{{ $status->name }}</option>@endforeach</select>
<input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount" placeholder="{{ __('Final amount for commission') }}">
<input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}">
<div class="d-flex align-items-center gap-1">
<label class="small text-secondary mb-0 text-nowrap" for="img-{{ $order->id }}">{{ __('Add photos') }}:</label>
<input id="img-{{ $order->id }}" type="file" name="images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
</div>
<button class="btn btn-sm btn-primary">{{ __('Save order update') }}</button></form>@endadminCan
@adminCan('admin.service-orders.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.service-orders.destroy',$order->id)])@endadminCan</td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary">{{ __('No service orders found') }}</td></tr>@endforelse
</tbody></table>{{ $orders->links() }}</div></div>
</main></div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.order-status').forEach((select) => {
 const amount = select.closest('form').querySelector('.gross-amount');
 const sync = () => {
   const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
   amount.required = complete;
   amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
 };
 select.addEventListener('change', sync);
 sync();
});
</script>
@endpush
