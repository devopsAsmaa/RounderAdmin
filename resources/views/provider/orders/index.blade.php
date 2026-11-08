@extends('layouts.provider')
@section('title', __('Service orders'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 fw-bold mb-0">{{ __('Service orders') }}</h1>
   
</div>
<section class="panel p-3 p-lg-4">
    @include('provider.orders.table', ['orders' => $orders])
    <div class="mt-3">{{ $orders->links() }}</div>
</section>
@endsection
