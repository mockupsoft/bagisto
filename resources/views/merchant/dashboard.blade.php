@php
    $title = 'Dashboard';
@endphp

@include('merchant.layout', [
    'title' => $title,
    'slot' => view()->make('merchant.partials.dashboard-content', compact('tenant', 'domains', 'verificationService')),
])
