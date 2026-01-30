@php
    $title = 'Settings';
@endphp

@include('merchant.layout', [
    'title' => $title,
    'slot' => view()->make('merchant.partials.settings-content', compact('tenant', 'settings')),
])
