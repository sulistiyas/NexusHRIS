@props(['variant' => 'default'])

@php
$classes = match($variant) {
    'super_admin', 'orange' => 'bg-orange-50 text-orange-700 border-orange-200/70',
    'hr_admin', 'sky', 'blue' => 'bg-sky-50 text-sky-700 border-sky-200/70',
    'manager', 'emerald', 'green' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
    'employee', 'slate' => 'bg-slate-100 text-slate-700 border-slate-200/70',
    'danger', 'rose', 'red' => 'bg-rose-50 text-rose-700 border-rose-200/70',
    default => 'bg-slate-50 text-slate-700 border-slate-200',
};
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider border ' . $classes]) }}>
    {{ $slot }}
</span>
