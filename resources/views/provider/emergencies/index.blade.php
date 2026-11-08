@extends('layouts.provider')
@section('title', __('Emergency requests'))
@section('body')
<div class="mb-4">
    <h1 class="h3 fw-bold">{{ __('Emergency requests') }}</h1>
</div>
<section class="panel p-3 p-lg-4">
    @include('provider.emergencies.table', ['emergencies' => $emergencies])
    <div class="mt-3">{{ $emergencies->links() }}</div>
</section>
@endsection
