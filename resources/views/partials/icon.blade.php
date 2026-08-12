{{--
    ئایکۆنەکانی سیستەم — هەموویان SVGـی ناوخۆیین، بۆیە بێ ئینتەرنێت کاردەکەن.
    بەکارهێنان: @include('partials.icon', ['name' => 'orders', 'class' => 'size-5'])
--}}
@php
    $paths = [
        'dashboard' => '<path d="M4 13h6V4H4v9zm0 7h6v-5H4v5zm10 0h6v-9h-6v9zm0-16v5h6V4h-6z"/>',
        'items' => '<path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
        'stock' => '<path d="M7 20V8m0 0L4 11m3-3l3 3"/><path d="M17 4v12m0 0l3-3m-3 3l-3-3"/>',
        'counts' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3h6v3H9z"/><path d="M9 12l2 2 4-4"/>',
        'warehouses' => '<path d="M3 21V9l9-5 9 5v12"/><path d="M9 21v-7h6v7"/>',
        'suppliers' => '<path d="M3 16V7h11v9"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'purchases' => '<path d="M4 5h2l2 11h10l2-8H7"/><circle cx="10" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/>',
        'customers' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20a6 6 0 0112 0"/><path d="M16 5.5a3 3 0 010 5.5"/><path d="M18 20a5.5 5.5 0 00-2-4"/>',
        'orders' => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/><path d="M10 13h6M10 17h4"/>',
        'payments' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 12h.01M17 12h.01"/>',
        'cash' => '<path d="M4 7h13a3 3 0 013 3v7a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h10"/><circle cx="17" cy="13.5" r="1.2"/>',
        'debts' => '<path d="M12 3v18"/><path d="M7 8h7a3 3 0 010 6H8a3 3 0 000 6h9"/>',
        'employees' => '<circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2 20a6 6 0 0112 0"/><path d="M14.5 20a5 5 0 017.5-4"/>',
        'attendance' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'external-jobs' => '<path d="M14.5 3.5a5 5 0 00-6.4 6.4L3 15v6h6l5.1-5.1a5 5 0 006.4-6.4l-3.2 3.2-2.8-.7-.7-2.8 3.2-3.2z"/>',
        'reports' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'activity' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l4 2"/><path d="M3.5 9A9 9 0 015 6"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.6 1.6 0 00-2.7 1.1V21a2 2 0 11-4 0v-.1A1.6 1.6 0 007.9 19.4l-.1.1a2 2 0 11-2.8-2.8l.1-.1A1.6 1.6 0 004 15H3a2 2 0 110-4h.1A1.6 1.6 0 004.6 8.9l-.1-.1a2 2 0 112.8-2.8l.1.1A1.6 1.6 0 009 4.6V3a2 2 0 114 0v.1a1.6 1.6 0 002.7 1.1l.1-.1a2 2 0 112.8 2.8l-.1.1a1.6 1.6 0 001.1 2.7H21a2 2 0 110 4h-.1a1.6 1.6 0 00-1.5 1.3z"/>',
    ];
@endphp

<svg class="{{ $class ?? 'size-5' }}" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">
    {!! $paths[$name] ?? $paths['dashboard'] !!}
</svg>
