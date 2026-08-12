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

{{-- ── کورتەی ڕۆژ ── --}}
@can('view_reports')
    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @include('partials.stat-tile', [
            'label' => 'فرۆشتنی ئەمڕۆ', 'value' => fmt_money($todaySales), 'tone' => null, 'icon' => 'orders',
        ])
        @include('partials.stat-tile', [
            'label' => 'وەرگیراوی ئەمڕۆ', 'value' => fmt_money($todayIn), 'tone' => 'ok', 'icon' => 'payments',
        ])
        @include('partials.stat-tile', [
            'label' => 'قەرزی کڕیاران', 'value' => fmt_money($receivables),
            'tone' => $receivables > 0 ? 'danger' : null, 'icon' => 'debts',
        ])
        @include('partials.stat-tile', [
            'label' => 'قەرزی کارگە', 'value' => fmt_money($payables),
            'tone' => $payables > 0 ? 'warn' : null, 'icon' => 'suppliers',
        ])
    </div>

    <div class="mt-3 grid gap-3 lg:grid-cols-3">
        {{-- قاسەکان --}}
        <div class="card">
            <div class="card-head">قاسە</div>
            <div class="card-body space-y-2.5 text-sm">
                @foreach ($cashBoxes as $box)
                    <div class="flex items-center justify-between border-b border-[--color-line] pb-2.5 last:border-0 last:pb-0">
                        <span>{{ $box->name }}</span>
                        <span class="num font-semibold">{{ fmt_money($box->balance(), $box->currency) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- کاری بەردەوام --}}
        <div class="card">
            <div class="card-head">کاری بەردەوام</div>
            <div class="card-body space-y-2.5 text-sm">
                @foreach ([
                    'داواکاری کراوە' => fmt_num($openOrders),
                    'وەسڵی ئەمڕۆ' => fmt_num($todayOrders),
                    'کارمەندی ئامادە' => fmt_num($presentToday),
                    'جوڵەی مەخزەنی ئەمڕۆ' => fmt_num($todayMovements),
                ] as $label => $value)
                    <div class="flex items-center justify-between">
                        <span class="text-[--color-ink-soft]">{{ $label }}</span>
                        <span class="num font-semibold">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- دوایین وەسڵەکان --}}
        <div class="card">
            <div class="card-head">دوایین وەسڵەکان</div>
            <div class="card-body !p-0">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('orders.show', $order) }}"
                       class="flex items-center justify-between border-b border-[--color-line] px-4 py-2.5 last:border-0 hover:bg-[--color-surface-soft]">
                        <div class="min-w-0">
                            <div class="truncate text-sm">{{ $order->customer?->name }}</div>
                            <div class="num text-xs text-[--color-ink-soft]">ژ. {{ $order->invoice_no }}</div>
                        </div>
                        <span class="num text-sm font-medium">{{ fmt_money($order->total_iqd) }}</span>
                    </a>
                @empty
                    <p class="p-4 text-sm text-[--color-ink-soft]">هێشتا هیچ وەسڵێک نییە.</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-3">
        @include('partials.stat-tile', [
            'label' => 'کۆی کاڵا', 'value' => fmt_num($itemsCount), 'tone' => null, 'icon' => 'items',
        ])
        @include('partials.stat-tile', [
            'label' => 'جوڵەی ئەمڕۆ', 'value' => fmt_num($todayMovements), 'tone' => null, 'icon' => 'stock',
        ])
        @include('partials.stat-tile', [
            'label' => 'کاڵای کەم', 'value' => fmt_num($lowStock->count()),
            'tone' => $lowStock->count() ? 'warn' : null, 'icon' => 'counts',
        ])
    </div>
@endcan

{{-- ── ئاگاداری کەمی مەخزەن ── --}}
<div class="card mt-3">
    <div class="card-head flex items-center justify-between">
        <span>ئاگاداری کەمی مەخزەن</span>
        @if ($lowStock->isNotEmpty())
            <span class="badge badge-warn">{{ fmt_num($lowStock->count()) }} کاڵا</span>
        @endif
    </div>

    @if ($lowStock->isEmpty())
        <div class="card-body text-sm text-[--color-ink-soft]">
            هیچ کاڵایەک لە سنووری ئاگاداری کەمتر نەبووەتەوە.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>کاڵا</th><th>کۆد</th>
                        <th class="num">ماوە</th><th class="num">سنووری کەمترین</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStock as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="num text-[--color-ink-soft]">{{ $item->code }}</td>
                            <td class="num font-medium text-[--color-warn]">
                                {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                            </td>
                            <td class="num text-[--color-ink-soft]">{{ fmt_qty($item->min_qty) }}</td>
                            <td class="text-left">
                                <a href="{{ route('items.show', $item) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
