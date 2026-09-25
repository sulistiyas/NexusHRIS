@props(['type' => 'info'])

@php
$classes = match($type) {
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'error', 'danger' => 'bg-rose-50 border-rose-200 text-rose-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    default => 'bg-sky-50 border-sky-200 text-sky-800',
};
@endphp

<div {{ $attributes->merge(['class' => 'p-4 rounded-xl border text-xs sm:text-sm flex items-start gap-2.5 ' . $classes]) }}>
    {{ $slot }}
</div>
