@extends('layouts.admin')
@section('title', __('Residents').' · Rounder')
@section('body')
<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ __('Residents') }}</h1>
            <p class="text-secondary mb-0">{{ __('Resident accounts registered in the mobile application.') }}</p>
        </div>
        <span class="pill px-3 py-2 fw-bold" style="font-size: 0.95rem;">
            <i class="fa-solid fa-users me-1"></i> {{ __('Total residents') }}: {{ $residents->total() }}
        </span>
    </div>

    @include('admin.partials.feedback')

    <div class="panel p-3">
        <form class="d-flex gap-2 mb-3" method="get" action="{{ route('admin.residents.index') }}">
            <input name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('Search name, email or phone') }}">
            <button class="btn btn-outline-secondary">{{ __('Filter') }}</button>
            @if(request('q'))
                <a href="{{ route('admin.residents.index') }}" class="btn btn-outline-danger">{{ __('Clear') }}</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email address') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Linked home') }}</th>
                        <th>{{ __('Bookings') }}</th>
                        <th>{{ __('Registration date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $resident)
                        <tr>
                            <td>{{ $resident->id }}</td>
                            <td class="fw-bold">
                                <i class="fa-solid fa-user text-secondary me-1"></i> {{ $resident->name }}
                            </td>
                            <td>{{ $resident->email ?? '—' }}</td>
                            <td><span dir="ltr">{{ $resident->phone ?? '—' }}</span></td>
                            <td>
                                @if($resident->residentHomes->isNotEmpty())
                                    @foreach($resident->residentHomes as $home)
                                        <span class="pill me-1 mb-1 d-inline-block">
                                            <i class="fa-solid fa-building me-1"></i>{{ $home->name }}
                                            @if($home->pivot->unit_label)
                                                ({{ __('Unit') }}: {{ $home->pivot->unit_label }})
                                            @endif
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $resident->bookings_count }}
                                </span>
                            </td>
                            <td>{{ $resident->created_at?->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">{{ __('No residents found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $residents->links() }}
    </div>
</main>
@endsection
