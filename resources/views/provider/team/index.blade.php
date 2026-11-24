@extends('layouts.provider')
@section('title', __('Team and permissions'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">{{ __('Team and permissions') }}</h1>
        <p class="text-secondary mb-0">{{ __('Manage provider team accounts and the sections they can access.') }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
            <i class="fa-solid fa-users text-primary me-1"></i>
            {{ __('Total members') }}: <strong class="text-dark">{{ $members->total() }}</strong>
        </span>
        <a class="btn btn-orange" href="{{ route('provider.team.create') }}">
            <i class="fa-solid fa-user-plus me-1"></i>{{ __('Add team member') }}
        </a>
    </div>
</div>

<section class="panel p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="min-width: 200px;">{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th style="min-width: 220px;">{{ __('Permissions') }}</th>
                    <th class="text-end" style="min-width: 140px;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($members as $member)
                @php
                    $isOwner = (int) $member->id === $ownerId;
                    $memberPerms = collect($member->permissions ?? []);
                    $mappedPermissions = $memberPerms->map(fn ($permission) => [
                        'code' => $permission,
                        'name' => match ($permission) {
                            'dashboard.view' => __('View dashboard'),
                            'orders.view' => __('View service orders'),
                            'orders.create' => __('Create service orders'),
                            'orders.manage' => __('Accept and update service orders'),
                            'prices.view' => __('View service prices'),
                            'prices.manage' => __('Submit and resubmit prices for approval'),
                            'emergencies.view' => __('View assigned emergency requests'),
                            'users.manage' => __('Manage team users and permissions'),
                            'finance.view' => __('Financial accounts'),
                            'ratings.view' => __('Ratings and reviews'),
                            'profile.manage' => __('Company profile settings'),
                            'contract.view' => __('View service contract'),
                            default => $permission,
                        }
                    ])->values();
                    $hasAllPermissions = $isOwner || ($mappedPermissions->count() >= 8 && $mappedPermissions->count() >= count($permissions));
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 38px; height: 38px; font-size: 0.95rem; border: 1px solid #dce6e3; flex-shrink: 0;">
                                {{ mb_substr($member->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $member->name }}</div>
                                @if($isOwner)
                                    <span class="badge" style="background:#fff3ea; color:var(--orange); font-size:0.7rem; border:1px solid #ffd8bf;">
                                        <i class="fa-solid fa-crown me-1 text-rounder-orange"></i>{{ __('Primary account') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($member->email)
                            <span class="text-secondary font-monospace" style="font-size:0.86rem;">{{ $member->email }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($member->phone)
                            <span dir="ltr" class="text-secondary font-monospace" style="font-size:0.88rem;">{{ $member->phone }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($isOwner)
                            <div class="d-inline-flex align-items-center gap-1">
                                <span class="badge" style="background:#fff3ea; color:var(--orange); border:1px solid #ffd8bf; padding: 6px 12px; border-radius: 50rem; font-size: 0.82rem; font-weight: 600;">
                                    <i class="fa-solid fa-crown me-1 text-rounder-orange"></i>{{ __('Full permissions') }}
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary view-permissions-btn py-1 px-2"
                                    data-name="{{ $member->name }}"
                                    data-permissions='@json($mappedPermissions)'
                                    data-is-owner="1"
                                    title="{{ __('View permissions') }}">
                                    <i class="fa-solid fa-eye text-muted" style="font-size:0.75rem;"></i>
                                </button>
                            </div>
                        @elseif($hasAllPermissions)
                            <div class="d-inline-flex align-items-center gap-1">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-check-double me-1"></i>{{ __('All permissions') }}
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary view-permissions-btn py-1 px-2"
                                    data-name="{{ $member->name }}"
                                    data-permissions='@json($mappedPermissions)'
                                    data-is-owner="0"
                                    title="{{ __('View permissions') }}">
                                    <i class="fa-solid fa-eye text-muted" style="font-size:0.75rem;"></i>
                                </button>
                            </div>
                        @elseif($mappedPermissions->isEmpty())
                            <span class="text-muted">—</span>
                        @elseif($mappedPermissions->count() <= 2)
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                @foreach($mappedPermissions as $p)
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-shield-halved text-primary me-1" style="font-size: 0.72rem;"></i>{{ $p['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-secondary view-permissions-btn py-1 px-2"
                                data-name="{{ $member->name }}"
                                data-permissions='@json($mappedPermissions)'
                                data-is-owner="0"
                                title="{{ __('View permissions') }}">
                                <i class="fa-solid fa-shield-halved me-1 text-primary"></i>
                                <span class="fw-semibold">{{ $mappedPermissions->count() }}</span> {{ __('Permissions') }}
                                <i class="fa-solid fa-eye ms-1 text-muted" style="font-size:0.75rem;"></i>
                            </button>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if($isOwner)
                            <span class="badge bg-light text-muted border font-monospace" style="font-size:0.75rem;">
                                <i class="fa-solid fa-shield me-1"></i>{{ __('Protected') }}
                            </span>
                        @else
                            <div class="d-inline-flex align-items-center gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('provider.team.edit', $member->id) }}" title="{{ __('Edit') }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('Edit') }}
                                </a>
                                <form class="d-inline" method="post" action="{{ route('provider.team.destroy', $member->id) }}" onsubmit="return confirm('{{ __('Revoke this team member access?') }}')">
                                    @csrf @method('delete')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="{{ __('Revoke access') }}">
                                        <i class="fa-solid fa-user-xmark me-1"></i>{{ __('Revoke access') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">{{ __('No team members have been added.') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $members->links() }}</div>
</section>

{{-- نافذة منبثقة لعرض صلاحيات المستخدم --}}
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3" style="background: #f8fafc;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="permissionsModalLabel">{{ __('Permissions') }}</h5>
                        <div class="small text-muted" id="modalMemberName"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-3 p-lg-4">
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="modalPermissionSearch" class="form-control border-start-0" placeholder="{{ __('Search permissions') }}">
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="badge bg-primary rounded-pill px-3 py-2" id="modalPermissionsCount">0</span>
                    </div>
                </div>

                <div id="modalPermissionsList" class="d-flex flex-wrap gap-2 pt-2" style="max-height: 340px; overflow-y: auto;">
                    {{-- بطاقات الصلاحيات تضاف هنا ديناميكياً --}}
                </div>
            </div>
            <div class="modal-footer border-top py-2 bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const permissionsModalEl = document.getElementById('permissionsModal');
    if (!permissionsModalEl) return;

    const permissionsModal = new bootstrap.Modal(permissionsModalEl);
    const modalMemberName = document.getElementById('modalMemberName');
    const modalPermissionsCount = document.getElementById('modalPermissionsCount');
    const modalPermissionsList = document.getElementById('modalPermissionsList');
    const searchInput = document.getElementById('modalPermissionSearch');

    document.querySelectorAll('.view-permissions-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const memberName = this.dataset.name || '';
            const isOwner = this.dataset.isOwner === '1';
            let perms = [];
            try {
                perms = JSON.parse(this.dataset.permissions || '[]');
            } catch (e) {
                perms = [];
            }

            modalMemberName.textContent = memberName + (isOwner ? ' ({{ __('Primary account') }})' : '');
            modalPermissionsCount.textContent = perms.length + ' {{ __('Permissions') }}';
            searchInput.value = '';

            renderModalPermissions(perms);
            permissionsModal.show();
        });
    });

    function renderModalPermissions(items) {
        modalPermissionsList.innerHTML = '';
        if (!items || !items.length) {
            modalPermissionsList.innerHTML = '<span class="text-muted small p-2">{{ __('No permissions found') }}</span>';
            return;
        }

        const fragment = document.createDocumentFragment();
        items.forEach(item => {
            const chip = document.createElement('div');
            chip.className = 'badge bg-light text-dark border p-2 modal-perm-chip d-inline-flex align-items-center gap-2';
            chip.style.borderRadius = '8px';
            chip.style.fontSize = '0.85rem';
            chip.style.fontWeight = '500';
            chip.dataset.name = (item.name || '').toLowerCase();

            chip.innerHTML = `
                <i class="fa-solid fa-circle-check text-success"></i>
                <span class="perm-title">${item.name}</span>
            `;

            fragment.appendChild(chip);
        });

        modalPermissionsList.appendChild(fragment);
    }

    searchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        modalPermissionsList.querySelectorAll('.modal-perm-chip').forEach(chip => {
            const name = chip.dataset.name || '';
            if (!term || name.includes(term)) {
                chip.classList.remove('d-none');
            } else {
                chip.classList.add('d-none');
            }
        });
    });
});
</script>
@endsection
