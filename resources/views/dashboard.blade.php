@extends('layouts.menu')

@section('content')

@php
    // مێنیوی سەرەکی — هەموو خانەکان لە یەک لیستدان و لە یەک خشتەدا دەڕژێن،
    // بۆیە هیچ بۆشاییەک لە نێوان گرووپەکاندا نامێنێتەوە.
    // ڕەنگ گرووپەکان جیا دەکاتەوە: شین=داتا، پرتەقاڵی=کڕین/فرۆشتن،
    // سوور=پارە، قاوەیی تۆخ=راپۆرت، قاوەیی=سیستەم.
    // ٣٠ خانە = ٥ ڕیزی تەواوی ٦ خانەیی.
    $tiles = [
        ['c' => 'tile-blue',   'route' => 'items.index',      'label' => 'کاڵا و مەواد',     'icon' => 'items',      'can' => 'view_stock'],
        ['c' => 'tile-blue',   'route' => 'items.create',     'label' => 'کاڵای نوێ',        'icon' => 'items',      'can' => 'manage_items'],
        ['c' => 'tile-blue',   'route' => 'stock.index',      'label' => 'جوڵەی مەخزەن',     'icon' => 'stock',      'can' => 'view_stock'],
        ['c' => 'tile-blue',   'route' => 'stock.create',     'label' => 'زیاد / کەمکردن',   'icon' => 'stock',      'can' => 'manage_stock'],
        ['c' => 'tile-blue',   'route' => 'counts.index',     'label' => 'جەردی کۆگا',       'icon' => 'counts',     'can' => 'manage_stock_counts'],
        ['c' => 'tile-blue',   'route' => 'warehouses.index', 'label' => 'کۆگاکان',          'icon' => 'warehouses', 'can' => 'manage_items'],
        ['c' => 'tile-blue',   'route' => 'customers.index',  'label' => 'کڕیارەکان',        'icon' => 'customers',  'can' => 'manage_customers'],
        ['c' => 'tile-blue',   'route' => 'customers.create', 'label' => 'کڕیاری نوێ',       'icon' => 'customers',  'can' => 'manage_customers'],
        ['c' => 'tile-blue',   'route' => 'suppliers.index',  'label' => 'فرۆشیارەکان',      'icon' => 'suppliers',  'can' => 'manage_suppliers'],
        ['c' => 'tile-blue',   'route' => 'employees.index',  'label' => 'کارمەندان',        'icon' => 'employees',  'can' => 'manage_employees'],

        ['c' => 'tile-orange', 'route' => 'orders.create',    'label' => 'وەسڵی نوێ',        'icon' => 'orders',        'can' => 'manage_orders'],
        ['c' => 'tile-orange', 'route' => 'orders.index',     'label' => 'وەسڵ و داواکاری',  'icon' => 'orders',        'can' => 'manage_orders'],
        ['c' => 'tile-orange', 'route' => 'purchases.create', 'label' => 'کڕینی نوێ',        'icon' => 'purchases',     'can' => 'manage_purchases'],
        ['c' => 'tile-orange', 'route' => 'purchases.index',  'label' => 'پسوولەی کڕین',     'icon' => 'purchases',     'can' => 'manage_purchases'],
        ['c' => 'tile-orange', 'route' => 'external-jobs.index', 'label' => 'ئیشی خاریجی',   'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],

        ['c' => 'tile-red', 'route' => 'payments.create', 'params' => ['type' => 'in'],  'label' => 'وەرگرتنی پارە', 'icon' => 'payments', 'can' => 'manage_payments'],
        ['c' => 'tile-red', 'route' => 'payments.create', 'params' => ['type' => 'out'], 'label' => 'دانی پارە',     'icon' => 'cash',     'can' => 'manage_payments'],
        ['c' => 'tile-red', 'route' => 'payments.index',   'label' => 'حەقدییەکان',       'icon' => 'payments', 'can' => 'manage_payments'],
        ['c' => 'tile-red', 'route' => 'cash.index',       'label' => 'قاسە',             'icon' => 'cash',     'can' => 'manage_cash'],
        ['c' => 'tile-red', 'route' => 'debts.index',      'label' => 'قەرزەکان',         'icon' => 'debts',    'can' => 'manage_payments'],

        ['c' => 'tile-maroon', 'route' => 'reports.show', 'params' => 'sales',     'label' => 'راپۆرتی فرۆشتن', 'icon' => 'orders',    'can' => 'view_reports'],
        ['c' => 'tile-maroon', 'route' => 'reports.show', 'params' => 'purchases', 'label' => 'راپۆرتی کڕین',   'icon' => 'purchases', 'can' => 'view_reports'],
        ['c' => 'tile-maroon', 'route' => 'reports.show', 'params' => 'profit',    'label' => 'قازانج',         'icon' => 'reports',   'can' => 'view_reports'],
        ['c' => 'tile-maroon', 'route' => 'reports.show', 'params' => 'stock',     'label' => 'راپۆرتی مەخزەن', 'icon' => 'items',     'can' => 'view_reports'],
        ['c' => 'tile-maroon', 'route' => 'reports.show', 'params' => 'cash',      'label' => 'راپۆرتی قاسە',   'icon' => 'cash',      'can' => 'view_reports'],
        ['c' => 'tile-maroon', 'route' => 'reports.index',                         'label' => 'هەموو راپۆرت',   'icon' => 'reports',   'can' => 'view_reports'],

        ['c' => 'tile-brown', 'route' => 'attendance.index', 'label' => 'هاتن و چوون',      'icon' => 'attendance', 'can' => 'manage_employees'],
        ['c' => 'tile-brown', 'route' => 'attendance.wages', 'label' => 'حەقدەستەکان',      'icon' => 'employees',  'can' => 'manage_employees'],
        ['c' => 'tile-brown', 'route' => 'activity.index',   'label' => 'مێژووی کردارەکان', 'icon' => 'activity',   'can' => 'manage_settings'],
        ['c' => 'tile-brown', 'route' => 'settings.index',   'label' => 'ڕێکخستن و باکەپ',  'icon' => 'settings',   'can' => 'manage_settings'],
    ];

    $visible = collect($tiles)->filter(
        fn ($tile) => ! $tile['can'] || auth()->user()->can($tile['can'])
    );
@endphp

{{-- ── خانەکانی مێنیو — یەک خشتەی بەردەوام ── --}}
<div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
    @foreach ($visible as $tile)
        <a href="{{ isset($tile['params']) ? route($tile['route'], $tile['params']) : route($tile['route']) }}"
           class="tile {{ $tile['c'] }}">
            <span class="tile-icon">
                @include('partials.icon', ['name' => $tile['icon'], 'class' => 'size-6'])
            </span>
            <span class="tile-label">{{ $tile['label'] }}</span>
        </a>
    @endforeach
</div>

{{-- ئاگاداری کەمی مەخزەن — تەنها یەک دێڕ، لە خوارەوەی کارتەکان.
     نابێت بەتەواوی لابدرێت، چونکە داواکراوە سیستەم ئاگادار بکاتەوە. --}}
@if ($lowStock->isNotEmpty())
    <a href="{{ route('items.index', ['low' => 1]) }}"
       class="mt-3 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-warn]/30 bg-[--color-warn-soft] px-4 py-3 text-sm">
        <svg class="size-5 shrink-0 text-[--color-warn]" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.3 4l-8 13.5A1 1 0 003.2 19h17.6a1 1 0 00.9-1.5l-8-13.5a1 1 0 00-1.7 0z"/>
            <path d="M12 9v4M12 16h.01"/>
        </svg>
        <span class="text-[--color-ink]">
            <span class="num font-semibold text-[--color-warn]">{{ fmt_num($lowStock->count()) }}</span>
            کاڵا لە سنووری کەمترین کەمتر بوونەتەوە —
            <span class="text-[--color-ink-soft]">
                {{ $lowStock->take(3)->pluck('name')->implode('، ') }}{{ $lowStock->count() > 3 ? '...' : '' }}
            </span>
        </span>
        <span class="mr-auto text-sm font-medium text-[--color-brand-700]">بینین</span>
    </a>
@endif

@endsection
