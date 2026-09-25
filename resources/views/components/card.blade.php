@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden']) }}>
    @if($title || $subtitle)
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                @if($title)
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif
    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
