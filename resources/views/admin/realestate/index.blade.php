@extends('layouts.admin')
@section('title', __('Homes').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h1 class="h3 fw-bold mb-0">{{ __('Homes') }}</h1>
    @adminCan('admin.realestate.create')
    <a class="btn btn-primary" href="{{ route('admin.realestate.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Add home') }}
    </a>
    @endadminCan
</div>
@include('admin.partials.feedback')
<div class="panel p-3 table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Home') }}</th><th>{{ __('City') }}</th><th>{{ __('Floors count') }}</th><th>{{ __('Apartments') }}</th><th>{{ __('Residents') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($homes as $home)<tr><td>{{ $home->id }}</td><td>{{ $home->name }}<div class="small text-secondary">{{ $home->property_code }}</div></td><td>{{ $home->city->translations->firstWhere('locale',app()->getLocale())?->name ?? $home->city->code }}</td><td>{{ $home->floors_count ?? '—' }}</td><td>{{ $home->units_count }}</td><td>{{ $home->residents_count }}</td><td>
@adminCan('admin.realestate.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.realestate.edit',$home->id) }}">{{ __('Edit') }}</a>@endadminCan
@include('admin.partials.delete-button',['action'=>route('admin.realestate.destroy',$home->id)])
</td></tr>@empty<tr><td colspan="7">{{ __('No homes found') }}</td></tr>@endforelse
</tbody></table>{{ $homes->links() }}</div>
</main>
@endsection
