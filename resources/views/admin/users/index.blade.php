@extends('layouts.admin')
@section('title', __('Users').' · Rounder')
@section('body')
<main class="container py-4"><div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-0">{{ __('Users') }}</h1>
        <div class="small text-secondary">{{ __('Manage administrative accounts only.') }}</div>
    </div>
    @adminCan('admin.users.create')
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
        <i class="fa-solid fa-plus me-1"></i> {{ __('Create user') }}
    </a>
    @endadminCan
</div>
@include('admin.partials.feedback')
<div class="panel p-3"><form class="d-flex gap-2 mb-3"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('Search name, email or phone') }}"><button class="btn btn-outline-secondary">{{ __('Filter') }}</button></form>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Name') }}</th><th>{{ __('Email address') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Permissions') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($users as $user)<tr><td>{{ $user->id }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone }}</td>
<td>{{ $user->is_super_admin ? __('Super administrator') : ($user->permissionSet?->{'name_'.app()->getLocale()} ?? '—') }}</td><td>{{ $user->is_active ? __('Active') : __('Inactive') }}</td><td>
@if(!$user->is_super_admin && auth()->id()!==$user->id)
@adminCan('admin.users.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit',$user) }}">{{ __('Edit') }}</a>@endadminCan
@include('admin.partials.delete-button',['action'=>route('admin.users.destroy',$user)])
@endif
</td></tr>@empty<tr><td colspan="7">{{ __('No users found') }}</td></tr>@endforelse
</tbody></table></div>{{ $users->links() }}</div>
</main>
@endsection
