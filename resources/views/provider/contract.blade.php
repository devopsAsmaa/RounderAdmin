@extends('layouts.provider')
@section('title', 'عقد مزود الخدمة')
@section('body')
<div class="row justify-content-center py-4">
    <div class="col-12 col-lg-9">
        <section class="panel p-4 p-lg-5">
            <div class="mb-4">
                <div class="text-secondary">رقم العقد: {{ $contract->reference }}</div>
                <h1 class="h3 fw-bold mt-2">عقد مزود الخدمة</h1>
                <p class="text-secondary mb-0">يرجى مراجعة العقد والموافقة عليه للانتقال إلى لوحة التحكم.</p>
            </div>
            @if($terms)
                <div class="border rounded-3 p-3 p-lg-4 mb-4">{!! $terms !!}</div>
                <form method="post" action="{{ route('provider.contract.accept') }}">
                    @csrf
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="accept-terms" name="accept_contract" value="1" required>
                        <label class="form-check-label" for="accept-terms">أقر بأنني قرأت العقد وأوافق على بنوده.</label>
                    </div>
                    <button class="btn btn-primary" type="submit">الموافقة على العقد والدخول للوحة التحكم</button>
                </form>
            @else
                <div class="alert alert-warning mb-0">لم تستكمل الإدارة بنود العقد بعد. ستتمكن من الموافقة عليه بعد تحديثه.</div>
            @endif
        </section>
    </div>
</div>
@endsection
