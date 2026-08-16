@extends('layouts.menu')

@section('content')

@php
    $tiles = [
        // ── ڕیزی ١: کۆگا و داتاکان (٦ دانە) ──
        ['c' => 'tile-blue',    'route' => 'items.index',         'label' => 'دۆخی کۆگا',       'icon' => 'items',         'can' => 'view_stock'],
        ['c' => 'tile-blue',    'route' => 'counts.index',        'label' => 'جەردی کۆگا',      'icon' => 'counts',        'can' => 'manage_stock_counts'],
        ['c' => 'tile-blue',    'route' => 'warehouses.index',    'label' => 'کۆگاکان',         'icon' => 'warehouses',    'can' => 'manage_items'],
        ['c' => 'tile-blue',    'route' => 'customers.index',     'label' => 'کڕیارەکان',       'icon' => 'customers',     'can' => 'manage_customers'],
        ['c' => 'tile-blue',    'route' => 'suppliers.index',     'label' => 'فرۆشیارەکان',     'icon' => 'suppliers',     'can' => 'manage_suppliers'],
        ['c' => 'tile-blue',    'route' => 'employees.index',     'label' => 'کارمەندان',       'icon' => 'employees',     'can' => 'manage_employees'],

        // ── ڕیزی ٢: کڕین، فرۆشتن و دارایی (٦ دانە) ──
        ['c' => 'tile-orange',  'route' => 'orders.index',        'label' => 'وەسڵ و داواکاری', 'icon' => 'orders',        'can' => 'manage_orders'],
        ['c' => 'tile-orange',  'route' => 'purchases.index',     'label' => 'پسوولەی کڕین',    'icon' => 'purchases',     'can' => 'manage_purchases'],
        ['c' => 'tile-orange',  'route' => 'external-jobs.index', 'label' => 'ئیشی خاریجی',     'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],
        ['c' => 'tile-red',     'route' => 'cash.index',          'label' => 'قاسە',            'icon' => 'cash',          'can' => 'manage_cash'],
        ['c' => 'tile-red',     'route' => 'payments.index',      'label' => 'حەقدی و پارەدان', 'icon' => 'payments',      'can' => 'manage_payments'],
        ['c' => 'tile-red',     'route' => 'debts.index',         'label' => 'قەرزەکان',        'icon' => 'debts',         'can' => 'manage_payments'],

        // ── ڕیزی ٣: کار، ڕاپۆرت و بەڕێوەبردن (٦ دانە) ──
        ['c' => 'tile-emerald', 'route' => 'attendance.index',    'label' => 'هاتن و چوون',     'icon' => 'attendance',    'can' => 'manage_employees'],
        ['c' => 'tile-emerald', 'route' => 'attendance.wages',    'label' => 'حەقدەستەکان',     'icon' => 'employees',     'can' => 'manage_employees'],
        ['c' => 'tile-maroon',  'route' => 'reports.show', 'params' => 'profit', 'label' => 'راپۆرتی قازانج', 'icon' => 'reports', 'can' => 'view_reports'],
        ['c' => 'tile-maroon',  'route' => 'reports.index',       'label' => 'هەموو راپۆرت',    'icon' => 'reports',       'can' => 'view_reports'],
        ['c' => 'tile-brown',   'route' => 'activity.index',      'label' => 'مێژووی کردارەکان', 'icon' => 'activity',      'can' => 'manage_settings'],
        ['c' => 'tile-brown',   'route' => 'settings.index',      'label' => 'ڕێکخستن و باکەپ', 'icon' => 'settings',      'can' => 'manage_settings'],
    ];

    $visible = collect($tiles)->filter(
        fn ($tile) => ! $tile['can'] || auth()->user()->can($tile['can'])
    );
@endphp

{{-- ── خانەکانی مێنیو — یەک خشتەی ڕێک و تەواو بەبێ بۆشایی ── --}}
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

{{-- ئاگاداری کەمی مەخزەن لە خوارەوە --}}
@if ($lowStock->isNotEmpty())
    <a href="{{ route('items.index', ['low' => 1]) }}"
       class="mt-3 flex items-center gap-2.5 rounded-[--radius-card] border border-[--color-warn]/30 bg-[--color-warn-soft] px-4 py-3 text-sm transition hover:bg-[--color-warn-soft]/80">
        <svg class="size-5 shrink-0 text-[--color-warn]" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.3 4l-8 13.5A1 1 0 003.2 19h17.6a1 1 0 00.9-1.5l-8-13.5a1 1 0 00-1.7 0z"/>
            <path d="M12 9v4M12 16h.01"/>
        </svg>
        <span class="text-[--color-ink]">
            <span class="num font-semibold text-[--color-warn]">{{ fmt_num($lowStock->count()) }}</span>
            کاڵا لە سنووری کەمترین کەمتر بوونەتەوە —
            <span class="text-[--color-ink-soft]">
                {{ $lowStock->take(4)->pluck('name')->implode('، ') }}{{ $lowStock->count() > 4 ? '...' : '' }}
            </span>
        </span>
        <span class="mr-auto font-medium text-[--color-brand-700]">بینین &larr;</span>
    </a>
@endif

@endsection
