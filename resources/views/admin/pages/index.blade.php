@extends('layouts.admin')
@section('title', __('Pages and policies').' · Rounder')
@section('body')
<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h3 mb-1">{{ __('Pages and policies') }}</h1>
        <p class="text-secondary small mb-0">{{ __('Manage terms and conditions, privacy policy, and public content.') }}</p>
    </div>
</div>

@include('admin.partials.feedback')

<div class="panel p-3 p-lg-4 mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Page') }}</th>
                    <th>{{ __('Slug') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Last updated') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                    @php
                        $trans = $page->translations->firstWhere('locale', app()->getLocale()) 
                            ?? $page->translations->firstWhere('locale', 'ar') 
                            ?? $page->translations->first();
                    @endphp
                    <tr>
                        <td>{{ $page->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $trans?->title ?? $page->slug }}</div>
                            <div class="small text-secondary text-truncate" style="max-width: 320px;">
                                {{ Str::limit(strip_tags($trans?->content ?? ''), 70) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <code>{{ $page->slug }}</code>
                            </span>
                        </td>
                        <td>
                            @if($page->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> {{ __('Active') }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> {{ __('Inactive') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-secondary" dir="ltr">{{ $page->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                        </td>
                        <td class="text-end">
                            @adminCan('admin.pages.edit')
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-pen-to-square me-1"></i> {{ __('Edit') }}
                            </a>
                            @endadminCan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">
                            {{ __('No records') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</main>
@endsection

