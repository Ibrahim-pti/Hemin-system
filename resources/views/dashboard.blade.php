@extends('layouts.menu')

@section('content')

{{-- ── ١. تابلۆی کورتە-ئاماری خێرای سەرەوە ── --}}
@if (auth()->user()->canSeeMoney())
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip icon-chip-ok">
                @include('partials.icon', ['name' => 'orders', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-[--color-ink-soft]">فرۆشی ئەمڕۆ</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-[--color-ok]">{{ fmt_money($todaySales) }}</div>
            </div>
        </div>

        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip">
                @include('partials.icon', ['name' => 'orders', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-[--color-ink-soft]">وەسڵەکانی ئەمڕۆ / کراوە</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-[--color-ink]">
                    {{ fmt_num($todayOrders) }} <span class="text-xs font-normal text-[--color-ink-soft]">({{ fmt_num($openOrders) }} لە کاردا)</span>
                </div>
            </div>
        </div>

        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip">
                @include('partials.icon', ['name' => 'cash', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-[--color-ink-soft]">داهاتی ئەمڕۆی قاسە</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-[--color-brand-700]">{{ fmt_money($todayIn) }}</div>
            </div>
        </div>

        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip {{ $receivables > 0 ? 'icon-chip-warn' : '' }}">
                @include('partials.icon', ['name' => 'debts', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-[--color-ink-soft]">کۆی قەرزی کڕیاران</div>
                <div class="num mt-0.5 truncate text-lg font-bold {{ $receivables > 0 ? 'text-[--color-warn]' : 'text-[--color-ink]' }}">
                    {{ fmt_money($receivables) }}
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── ٢. خانەکانی مێنیوی خێرا (Launchpad) ── --}}
@php
    $tiles = [
        // ڕیزی ١: کۆگا و ماددە و فرۆشتن
        ['c' => 'tile-blue',    'route' => 'items.index', 'params' => ['type' => 'raw'],  'label' => 'مەوادی کۆگا',       'icon' => 'items',         'can' => 'view_stock'],
        ['c' => 'tile-blue',    'route' => 'items.index', 'params' => ['type' => 'sale'], 'label' => 'بابەتی فرۆشتن',     'icon' => 'orders',        'can' => 'view_stock'],
        ['c' => 'tile-blue',    'route' => 'counts.index',                                'label' => 'جەردی کۆگا',        'icon' => 'counts',        'can' => 'manage_stock_counts'],
        ['c' => 'tile-blue',    'route' => 'warehouses.index',                            'label' => 'کۆگاکان',           'icon' => 'warehouses',    'can' => 'manage_items'],
        ['c' => 'tile-blue',    'route' => 'customers.index',                             'label' => 'کڕیارەکان',         'icon' => 'customers',     'can' => 'manage_customers'],
        ['c' => 'tile-blue',    'route' => 'suppliers.index',                             'label' => 'فرۆشیارەکان',       'icon' => 'suppliers',     'can' => 'manage_suppliers'],

        // ڕیزی ٢: کڕین، فرۆشتن، کارمەندان و دارایی
        ['c' => 'tile-orange',  'route' => 'employees.index',                             'label' => 'کارمەندان',         'icon' => 'employees',     'can' => 'manage_employees'],
        ['c' => 'tile-orange',  'route' => 'orders.index',                                'label' => 'وەسڵ و داواکاری',   'icon' => 'orders',        'can' => 'manage_orders'],
        ['c' => 'tile-orange',  'route' => 'purchases.index',                             'label' => 'پسوولەی کڕین',      'icon' => 'purchases',     'can' => 'manage_purchases'],
        ['c' => 'tile-orange',  'route' => 'external-jobs.index',                         'label' => 'ئیشی خاریجی',       'icon' => 'external-jobs', 'can' => 'manage_external_jobs'],
        ['c' => 'tile-red',     'route' => 'cash.index',                                  'label' => 'قاسە',              'icon' => 'cash',          'can' => 'manage_cash'],
        ['c' => 'tile-red',     'route' => 'payments.index',                              'label' => 'حەقدی و پارەدان',   'icon' => 'payments',      'can' => 'manage_payments'],

        // ڕیزی ٣: کار، ڕاپۆرت و سیستەم
        ['c' => 'tile-emerald', 'route' => 'debts.index',                                 'label' => 'قەرزەکان',          'icon' => 'debts',         'can' => 'manage_payments'],
        ['c' => 'tile-emerald', 'route' => 'attendance.index',                            'label' => 'هاتن و چوون',       'icon' => 'attendance',    'can' => 'manage_employees'],
        ['c' => 'tile-emerald', 'route' => 'attendance.wages',                            'label' => 'حەقدەستەکان',       'icon' => 'employees',     'can' => 'manage_employees'],
        ['c' => 'tile-maroon',  'route' => 'reports.show', 'params' => 'profit',          'label' => 'راپۆرتی قازانج',   'icon' => 'reports',       'can' => 'view_reports'],
        ['c' => 'tile-brown',   'route' => 'activity.index',                              'label' => 'مێژووی کردارەکان',   'icon' => 'activity',      'can' => 'manage_settings'],
        ['c' => 'tile-brown',   'route' => 'settings.index',                              'label' => 'ڕێکخستن و باکەپ',   'icon' => 'settings',      'can' => 'manage_settings'],
    ];

    $visible = collect($tiles)->filter(
        fn ($tile) => ! $tile['can'] || auth()->user()->can($tile['can'])
    );
@endphp

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

{{-- ── ٣. بەشی چالاکییە زیندووەکان (Live Activity & Widgets) ── --}}
@if (auth()->user()->canSeeMoney())
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- لای ڕاست: دواین وەسڵ و داواکارییەکان --}}
        <div class="card lg:col-span-2">
            <div class="card-head flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-[--color-ok]"></span>
                    <span>دواین وەسڵ و داواکارییەکان</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('orders.create') }}" class="text-xs font-semibold text-[--color-brand-700] hover:underline">+ وەسڵی نوێ</a>
                    <span class="text-[--color-line-strong]">|</span>
                    <a href="{{ route('orders.index') }}" class="text-xs text-[--color-ink-soft] hover:underline">هەمووی &larr;</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ژمارە</th>
                            <th>کڕیار</th>
                            <th>بەروار</th>
                            <th class="num">کۆی گشتی</th>
                            <th>دۆخ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="num font-bold text-[--color-brand-700]">#{{ $order->invoice_no }}</td>
                                <td class="font-medium">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="num whitespace-nowrap text-xs text-[--color-ink-soft]">{{ fmt_date($order->order_date) }}</td>
                                <td class="num font-semibold">{{ fmt_money($order->total, $order->currency) }}</td>
                                <td>
                                    <span class="badge {{ match ($order->status) {
                                        'delivered' => 'badge-ok',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-warn',
                                    } }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap text-left text-xs">
                                    <a href="{{ route('orders.show', $order) }}" class="font-medium text-[--color-brand-700] hover:underline">بینین</a>
                                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="mr-2 text-[--color-ink-soft] hover:underline">چاپ</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">
                                    هیچ وەسڵێک تۆمار نەکراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- لای چەپ: پوختەی ئاگاداری و دۆخی کارگە --}}
        <div class="space-y-4">

            {{-- ویدیجتی مەخزەن و ئاگاداری کەمی کاڵا --}}
            <div class="card">
                <div class="card-head flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if ($lowStock->isNotEmpty())
                            <span class="size-2 animate-pulse rounded-full bg-[--color-warn]"></span>
                            <span class="text-sm font-semibold text-[--color-warn]">ئاگاداری کەمی بابەت</span>
                        @else
                            <span class="size-2 rounded-full bg-[--color-ok]"></span>
                            <span class="text-sm font-semibold">دۆخی مەخزەن</span>
                        @endif
                    </div>
                    <a href="{{ route('items.index') }}" class="text-xs text-[--color-ink-soft] hover:underline">بابەتەکان &larr;</a>
                </div>

                <div class="card-body">
                    @if ($lowStock->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach ($lowStock->take(4) as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-[--color-ink]">{{ $item->name }}</span>
                                    <span class="num font-bold text-[--color-danger]">
                                        {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                                        <span class="font-normal text-[--color-ink-soft]">(کەمترین: {{ fmt_qty($item->min_qty) }})</span>
                                    </span>
                                </div>
                            @endforeach

                            @if ($lowStock->count() > 4)
                                <div class="pt-1 text-center">
                                    <a href="{{ route('items.index', ['low' => 1]) }}" class="text-xs font-semibold text-[--color-brand-700]">
                                        + {{ $lowStock->count() - 4 }} بابەتی تریش لە سنووری کەمترینە
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-xs text-[--color-ok]">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                            </svg>
                            <span>هەموو بابەتەکان لە ئاستی پێویستدان و کەمی نییە.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ویدیجتی قاسەکان و ئامادەبوون --}}
            <div class="card">
                <div class="card-head flex items-center justify-between">
                    <span class="text-sm font-semibold">باڵانسی قاسەکان</span>
                    <a href="{{ route('cash.index') }}" class="text-xs text-[--color-ink-soft] hover:underline">تەواو &larr;</a>
                </div>
                <div class="card-body space-y-2.5">
                    @foreach ($cashBoxes as $box)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[--color-ink-soft]">{{ $box->name }}</span>
                            <span class="num font-bold text-[--color-ink]">{{ fmt_money($box->balance(), $box->currency) }}</span>
                        </div>
                    @endforeach

                    <div class="border-t border-[--color-line] pt-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[--color-ink-soft]">ئامادەبوونی کارمەندان</span>
                            <a href="{{ route('attendance.index') }}" class="num font-semibold text-[--color-brand-700]">
                                {{ $presentToday }} لە {{ $totalEmployees }} ئامادەن
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endif

@endsection
