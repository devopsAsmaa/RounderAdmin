@extends('layouts.admin')
@section('title', __('Emergency requests').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <div class="small text-secondary">{{ __('Emergency dispatch') }}</div>
        <h1 class="h3 fw-bold mb-0">{{ __('Emergency requests') }}</h1>
    </div>
    @adminCan('admin.emergencies.create')
    <a class="btn btn-primary" href="{{ route('admin.emergencies.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create emergency request') }}
    </a>
    @endadminCan
</div>
@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('Request') }}</th><th>{{ __('Resident and home') }}</th><th>{{ __('Emergency type') }}</th><th>{{ __('Location and details') }}</th><th>{{ __('Status') }}</th><th>{{ __('Dispatch and commission') }}</th></tr></thead><tbody>
@forelse($requests as $item)
<tr><td><strong>{{ $item->reference }}</strong><div class="small text-secondary">{{ $item->requested_at }}</div><div class="small">{{ $item->resident_phone }}</div></td>
<td>{{ $item->resident_name }}<div class="small text-secondary">{{ $item->home_name }}</div></td>
<td><span class="pill">{{ $item->type_name }}</span></td>
<td><strong>{{ $item->location_details }}</strong><div class="small text-secondary text-wrap" style="max-width:260px">{{ $item->description }}</div></td>
<td><span class="pill">{{ $item->status_name }}</span>@if($item->provider_name)<div class="small mt-1">{{ $item->provider_name }}</div>@endif</td>
<td style="min-width:330px">@adminCan('admin.emergencies.update','PUT')<form method="post" action="{{ route('admin.emergencies.update',$item->id) }}" class="d-grid gap-2">@csrf @method('put')
<select class="form-select form-select-sm provider-select" name="provider_profile_id"><option value="">{{ __('Choose provider') }}</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($item->provider_name===$provider->company_name)>{{ $provider->company_name }} · {{ $provider->name }}</option>@endforeach</select>
<select class="form-select form-select-sm emergency-status" name="status_id" required>@foreach($statuses as $status)<option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($item->status_code===$status->code)>{{ $status->name }}</option>@endforeach</select>
<input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount" placeholder="{{ __('Final amount for commission') }}">
<input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}">
<button class="btn btn-sm btn-primary">{{ __('Save dispatch update') }}</button>
</form>@endadminCan
@adminCan('admin.emergencies.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.emergencies.destroy',$item->id)])@endadminCan</td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary">{{ __('No emergency requests found') }}</td></tr>@endforelse
</tbody></table>{{ $requests->links() }}</div></div>
</main></div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.emergency-status').forEach((select) => {
 const form = select.closest('form');
 const amount = form.querySelector('.gross-amount');
 const sync = () => {
   const code = select.options[select.selectedIndex]?.dataset.code;
   amount.required = code === 'resolved';
   amount.placeholder = amount.required ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
 };
 select.addEventListener('change', sync);
 sync();
});
</script>
@endpush
