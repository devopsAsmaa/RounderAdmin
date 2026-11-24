@extends('layouts.provider')
@section('title', __('Technicians'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">{{ __('Technicians') }}</h1>
        <p class="text-secondary mb-0">{{ __('Manage technicians, their service skills, and order assignments.') }}</p>
    </div>
    <a class="btn btn-orange" href="{{ route('provider.technicians.create') }}">
        <i class="fa-solid fa-user-plus me-1"></i>{{ __('Add technician') }}
    </a>
</div>

<section class="panel p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Services') }}</th>
                    <th>{{ __('Assigned orders') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($technicians as $technician)
                <tr>
                    <td class="fw-semibold">{{ $technician->name }}</td>
                    <td dir="ltr">{{ $technician->phone }}</td>
                    <td>
                        @php($serviceCount = $technician->technicianServices->count())
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal"
                            data-bs-target="#technicianServices{{ $technician->id }}"
                            aria-label="{{ __('View technician services') }}: {{ $technician->name }}"
                            title="{{ __('View technician services') }}">
                            <i class="fa-solid fa-list-check"></i>
                            <span class="ms-1">{{ $serviceCount }}</span>
                        </button>
                        <div class="modal fade" id="technicianServices{{ $technician->id }}" tabindex="-1"
                            aria-labelledby="technicianServicesTitle{{ $technician->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div>
                                            <h2 class="modal-title fs-5 fw-bold" id="technicianServicesTitle{{ $technician->id }}">{{ __('Technician services') }}</h2>
                                            <div class="small text-secondary">{{ $technician->name }}</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                    </div>
                                    <div class="modal-body">
                                        @forelse($technician->technicianServices as $service)
                                            @php($serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code)
                                            <section class="border rounded-3 p-3 mb-2">
                                                <h3 class="h6 fw-bold mb-2">
                                                    <i class="fa-solid fa-wrench text-primary me-1"></i>{{ $serviceName }}
                                                </h3>
                                                @php($subServices = $technician->technicianSubServices->where('service_id', $service->id))
                                                @if($subServices->isNotEmpty())
                                                    <div class="small text-secondary mb-2">{{ __('Sub-services') }}</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($subServices as $subService)
                                                            <span class="badge bg-light text-dark border">
                                                                {{ $subService->translations->firstWhere('locale', app()->getLocale())?->name ?? $subService->code }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </section>
                                        @empty
                                            <p class="text-secondary mb-0">{{ __('No services assigned') }}</p>
                                        @endforelse
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $technician->assigned_bookings_count }}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('provider.technicians.edit', $technician->id) }}">{{ __('Edit') }}</a>
                        <form class="d-inline" method="post" action="{{ route('provider.technicians.destroy', $technician->id) }}"
                            onsubmit="return confirm('{{ __('Delete this technician?') }}')">
                            @csrf @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit"
                                @disabled($technician->assigned_bookings_count > 0)
                                title="{{ $technician->assigned_bookings_count > 0 ? __('A technician assigned to service orders cannot be deleted.') : '' }}">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No technicians have been added.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $technicians->links() }}</div>
</section>
@endsection
