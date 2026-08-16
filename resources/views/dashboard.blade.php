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

{{-- ── ٢. دوگمە خێراکانی دەستپێکردن (Quick Actions) ── --}}
<div class="mb-6 flex flex-wrap items-center gap-2.5">
    @if (auth()->user()->can('manage_orders'))
        <a href="{{ route('orders.create') }}" class="btn btn-primary !py-2 !px-4 shadow-sm text-xs font-semibold flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ وەسڵی نوێ</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_purchases'))
        <a href="{{ route('purchases.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-cyan-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 01-8 0"></path>
            </svg>
            <span>+ پسوولەی کڕین</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_payments'))
        <a href="{{ route('payments.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>+ تۆماری پارەدان</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_external_jobs'))
        <a href="{{ route('external-jobs.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>+ ئیشی خاریجی</span>
        </a>
    @endif

    @if (auth()->user()->can('view_stock'))
        <a href="{{ route('items.index') }}" class="btn btn-ghost !py-2 !px-3 text-xs text-slate-500 hover:text-blue-700 font-medium">
            <span>کۆگا و مەخزەن &larr;</span>
        </a>
    @endif
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
