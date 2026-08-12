{{-- خانەی ئامار — ناونیشانی بێدەنگ + ژمارەی گەورە. رەنگ تەنها بۆ مانا. --}}
@php
    $toneClass = match ($tone ?? null) {
        'ok' => 'text-[--color-ok]',
        'warn' => 'text-[--color-warn]',
        'danger' => 'text-[--color-danger]',
        default => 'text-[--color-ink]',
    };
@endphp

<div class="card px-4 py-3">
    <div class="text-xs text-[--color-ink-soft]">{{ $label }}</div>
    <div class="num mt-1 text-xl font-semibold {{ $toneClass }}">{{ $value }}</div>
</div>
