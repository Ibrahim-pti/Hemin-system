{{-- خانەی ئامار — ژمارەی گەورە + ئایکۆنێکی نەرم. رەنگ تەنها بۆ مانا. --}}
@php
    $tone = $tone ?? null;

    $valueClass = match ($tone) {
        'ok' => 'text-[--color-ok]',
        'warn' => 'text-[--color-warn]',
        'danger' => 'text-[--color-danger]',
        default => 'text-[--color-ink]',
    };

    $chipClass = match ($tone) {
        'ok' => 'icon-chip icon-chip-ok',
        'warn' => 'icon-chip icon-chip-warn',
        'danger' => 'icon-chip icon-chip-danger',
        default => 'icon-chip',
    };
@endphp

<div class="card flex items-center gap-3 px-4 py-3.5">
    @isset($icon)
        <span class="{{ $chipClass }}">
            @include('partials.icon', ['name' => $icon, 'class' => 'size-5'])
        </span>
    @endisset

    <div class="min-w-0">
        <div class="truncate text-xs text-[--color-ink-soft]">{{ $label }}</div>
        <div class="num mt-0.5 truncate text-xl font-semibold {{ $valueClass }}">{{ $value }}</div>
    </div>
</div>
