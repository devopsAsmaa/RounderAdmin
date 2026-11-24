@extends('layouts.admin')
@section('title', __('Ticket #:reference · Support', ['reference' => $ticket->reference]))
@section('body')
<main class="container py-4">
    {{-- Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.support-tickets.index') }}" class="text-decoration-none">{{ __('Support tickets and messages') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Ticket #:reference', ['reference' => $ticket->reference]) }}</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0 text-dark">{{ __('Ticket details #:reference', ['reference' => $ticket->reference]) }}</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-2 fs-6 {{ $ticket->status_badge_class }}">
                {{ $ticket->status_label }}
            </span>
            <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Back to list') }}
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    <div class="row g-4">
        {{-- Right Column: Ticket details --}}
        <div class="col-lg-7">
            {{-- Ticket info card --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #ffffff;">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                    <div>
                        <span class="text-secondary small fw-bold">{{ __('Message category') }}:</span>
                        <div class="mt-1">
                            @php
                                $categoryPillStyle = match($ticket->category) {
                                    'order' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
                                    'payment' => 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                    'housing' => 'background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;',
                                    'subscription' => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                                    'business' => 'background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;',
                                    default => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                                };
                            @endphp
                            <span class="badge rounded-pill fw-semibold px-3 py-1 fs-6" style="{{ $categoryPillStyle }}">
                                {{ $ticket->category_label }}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-secondary small fw-bold">{{ __('Date sent') }}:</span>
                        <div class="fw-semibold text-dark mt-1 font-monospace">
                            {{ $ticket->created_at?->translatedFormat('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary small fw-bold mb-1">{{ __('Subject') }}:</label>
                    <div class="h5 fw-bold text-dark">{{ $ticket->subject }}</div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary small fw-bold mb-2">{{ __('Problem description (message text)') }}:</label>
                    <div class="p-3 rounded-3 bg-light border text-dark" style="white-space: pre-wrap; line-height: 1.7; font-size: 0.95rem;">{{ $ticket->message }}</div>
                </div>

                @if($ticket->attachment_url)
                    <div>
                        <label class="text-secondary small fw-bold mb-2">{{ __('Attached image') }}:</label>
                        <div class="border rounded-4 p-2 bg-light d-inline-block">
                            <a href="{{ $ticket->attachment_url }}" target="_blank" rel="noopener">
                                <img src="{{ $ticket->attachment_url }}" alt="{{ __('Ticket attachment') }}" style="max-width: 100%; max-height: 380px; object-fit: contain; border-radius: 12px;">
                            </a>
                            <div class="mt-2 text-center">
                                <a href="{{ $ticket->attachment_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('Open full size image') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Previous Reply History if exists --}}
            @if($ticket->admin_reply)
                <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff; border-right: 4px solid #10b981 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-success mb-0">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ __('Registered admin reply') }}
                        </h5>
                        <div class="small text-secondary font-monospace">
                            {{ $ticket->replied_at?->translatedFormat('Y-m-d H:i') }}
                        </div>
                    </div>
                    @if($ticket->repliedByAdmin)
                        <div class="small text-muted mb-2">
                            {{ __('By') }}: <strong>{{ $ticket->repliedByAdmin->name }}</strong>
                        </div>
                    @endif
                    <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-dark" style="white-space: pre-wrap; line-height: 1.6;">
                        {{ $ticket->admin_reply }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Left Column: Resident Info & Actions --}}
        <div class="col-lg-5">
            {{-- Resident card --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #ffffff;">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-user-circle text-secondary me-2"></i> {{ __('Resident information') }}
                </h5>
                <dl class="row mb-0 g-2">
                    <dt class="col-sm-4 text-secondary small">{{ __('Full name') }}:</dt>
                    <dd class="col-sm-8 fw-bold text-dark">{{ $ticket->user?->name ?? __('Unknown') }}</dd>

                    <dt class="col-sm-4 text-secondary small">{{ __('Phone number') }}:</dt>
                    <dd class="col-sm-8 font-monospace text-dark" dir="ltr">
                        @if($ticket->user?->phone)
                            <a href="tel:{{ $ticket->user->phone }}" class="text-decoration-none fw-semibold">
                                {{ $ticket->user->phone }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-secondary small">{{ __('Email address') }}:</dt>
                    <dd class="col-sm-8 font-monospace text-dark">
                        @if($ticket->user?->email)
                            <a href="mailto:{{ $ticket->user->email }}" class="text-decoration-none">
                                {{ $ticket->user->email }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-secondary small">{{ __('Registration date') }}:</dt>
                    <dd class="col-sm-8 text-secondary small font-monospace">{{ $ticket->user?->created_at?->format('Y-m-d') ?? '—' }}</dd>
                </dl>
            </div>

            {{-- Actions & Reply Form --}}
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff;">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-reply text-primary me-2"></i> {{ __('Follow up ticket and update status') }}
                </h5>
                <form method="POST" action="{{ route('admin.support-tickets.update', $ticket->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold text-dark small">{{ __('Ticket status') }} <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="open" @selected(old('status', $ticket->status) === 'open')>{{ __('New (pending)') }}</option>
                            <option value="in_progress" @selected(old('status', $ticket->status) === 'in_progress')>{{ __('In progress and investigation') }}</option>
                            <option value="resolved" @selected(old('status', $ticket->status) === 'resolved')>{{ __('Replied and resolved') }}</option>
                            <option value="closed" @selected(old('status', $ticket->status) === 'closed')>{{ __('Closed') }}</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="admin_reply" class="form-label fw-bold text-dark small">
                            {{ __('Reply text (visible to resident in ticket details)') }}
                        </label>
                        <textarea name="admin_reply" id="admin_reply" rows="5" class="form-control" placeholder="{{ __('Write support team reply here to assist the resident and inform them of the action taken...') }}">{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                        <div class="form-text text-muted">{{ __('The resident will be able to view this reply directly inside the mobile app.') }}</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-bold">
                            <i class="fa-solid fa-floppy-disk me-2"></i> {{ __('Save update and reply') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
