@extends('layouts.menu')

@section('content')

{{-- ── ١. سەردێڕی بەخێرهاتن و کاتژمێری زیندوو و ڕێکەوت ── --}}
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs">
    <div class="flex items-center gap-3.5">
        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 border border-blue-100">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-bold text-slate-800">
                    بەخێربێیتەوە، {{ auth()->user()->name }}
                </h1>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                    {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                پوختەی گشتی دۆخی کارگە و ئامارە سەرەکییەکان
            </p>
        </div>
    </div>

    {{-- بەروار و کاتژمێری کوردی زیندوو --}}
    <div class="flex items-center gap-3 bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 self-start md:self-auto" dir="rtl">
        <div class="flex size-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-blue-600">
            <svg class="size-4.5 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="min-w-0 text-right">
            <div class="clock-time font-bold text-sm text-slate-800 tracking-wide" dir="ltr" style="text-align: right" x-text="clock.time">--:--:--</div>
            <div class="clock-date text-[11px] font-medium text-slate-500" x-text="clock.date">ئەمڕۆ</div>
        </div>
    </div>
</div>

{{-- ── ٢. تابلۆی کورتە-ئاماری خێرای سەرەوە (KPI Cards) ── --}}
@if (auth()->user()->canSeeMoney())
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
        {{-- فرۆشی ئەمڕۆ --}}
        <div class="card p-4 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">فرۆشی ئەمڕۆ</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    @include('partials.icon', ['name' => 'orders', 'class' => 'size-4'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold text-emerald-600">{{ fmt_money($todaySales) }}</div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>ئەم مانگە:</span>
                <span class="num font-semibold text-slate-700">{{ fmt_money($monthSales ?? 0) }}</span>
            </div>
        </div>

        {{-- وەسڵەکانی ئەمڕۆ / کراوە --}}
        <div class="card p-4 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">وەسڵەکانی ئەمڕۆ</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    @include('partials.icon', ['name' => 'orders', 'class' => 'size-4'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold text-slate-800">
                {{ fmt_num($todayOrders) }} <span class="text-xs font-normal text-slate-400">وەسڵ</span>
            </div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>لە بەرهەمهێناندا:</span>
                <span class="num font-semibold text-blue-600">{{ fmt_num($inProductionCount ?? 0) }} وەسڵ</span>
            </div>
        </div>

        {{-- داهاتی ئەمڕۆی قاسە --}}
        <div class="card p-4 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">داهاتی ئەمڕۆی قاسە</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                    @include('partials.icon', ['name' => 'cash', 'class' => 'size-4'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold text-cyan-700">{{ fmt_money($todayIn) }}</div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>خەرجی ئەمڕۆ:</span>
                <span class="num font-semibold text-rose-600">{{ fmt_money($todayOut ?? 0) }}</span>
            </div>
        </div>

        {{-- کۆی قەرزی کڕیاران --}}
        <div class="card p-4 hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">کۆی قەرزی کڕیاران</span>
                <span class="flex size-8 items-center justify-center rounded-lg {{ $receivables > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                    @include('partials.icon', ['name' => 'debts', 'class' => 'size-4'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold {{ $receivables > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                {{ fmt_money($receivables) }}
            </div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>قەرزی فرۆشیاران:</span>
                <span class="num font-semibold text-slate-700">{{ fmt_money($payables ?? 0) }}</span>
            </div>
        </div>
    </div>
@endif

{{-- ── ٣. دوگمە خێراکانی دەستپێکردن (Quick Actions Toolbar) ── --}}
<div class="mb-6 flex flex-wrap items-center gap-2.5">
    @if (auth()->user()->can('manage_orders'))
        <a wire:navigate href="{{ route('orders.create') }}" class="btn btn-primary !py-2 !px-4 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ وەسڵی نوێ</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_purchases'))
        <a wire:navigate href="{{ route('purchases.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-cyan-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 01-8 0"></path>
            </svg>
            <span>+ پسوولەی کڕین</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_payments'))
        <a wire:navigate href="{{ route('payments.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>+ تۆماری پارەدان</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_external_jobs'))
        <a wire:navigate href="{{ route('external-jobs.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
            </svg>
            <span>+ ئیشی خاریجی</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_stock'))
        <a wire:navigate href="{{ route('stock.create') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            <span>+ جوڵەی کۆگا</span>
        </a>
    @endif

    @if (auth()->user()->can('manage_employees'))
        <a wire:navigate href="{{ route('attendance.index') }}" class="btn btn-ghost !py-2 !px-3 text-xs text-slate-600 hover:text-blue-700 font-medium">
            <span>تۆماری ئامادەبوون &larr;</span>
        </a>
    @endif
</div>

{{-- ── ٤. بەشی چالاکییە زیندووەکان و خشتەکان (Live Dashboard Layout) ── --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- ستوونی ڕاست: دواین وەسڵەکان + دواین پارەدانەکان (Main 2 Cols) --}}
    <div class="space-y-6 lg:col-span-2">

        {{-- تابلۆی دواین وەسڵ و داواکارییەکان --}}
        <div class="card overflow-hidden">
            <div class="card-head flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-full bg-blue-600"></span>
                    <span class="font-bold text-slate-800">دواین وەسڵ و داواکارییەکان</span>
                </div>
                <div class="flex items-center gap-2">
                    @if (auth()->user()->can('manage_orders'))
                        <a wire:navigate href="{{ route('orders.create') }}" class="text-xs font-semibold text-blue-600 hover:underline">+ وەسڵی نوێ</a>
                        <span class="text-slate-300">|</span>
                    @endif
                    <a wire:navigate href="{{ route('orders.index') }}" class="text-xs text-slate-500 hover:text-slate-800 hover:underline">هەمووی &larr;</a>
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
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="num font-bold text-blue-600">#{{ $order->invoice_no }}</td>
                                <td class="font-medium text-slate-800">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="num whitespace-nowrap text-xs text-slate-500">{{ fmt_date($order->order_date) }}</td>
                                <td class="num font-bold text-slate-800">{{ fmt_money($order->total, $order->currency) }}</td>
                                <td>
                                    <span class="badge {{ match ($order->status) {
                                        'delivered' => 'badge-ok',
                                        'cancelled' => 'badge-danger',
                                        'in_production' => 'badge-warn',
                                        'ready' => 'badge-ok',
                                        default => 'badge-secondary',
                                    } }}">
                                        {{ $order->status_label }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap text-left text-xs">
                                    <a wire:navigate href="{{ route('orders.show', $order) }}" class="font-semibold text-blue-600 hover:underline">بینین</a>
                                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="mr-2 text-slate-400 hover:text-slate-700">چاپ</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-slate-400">
                                    هیچ وەسڵێک تۆمار نەکراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- تابلۆی دواین پارەدان و جوڵەی قاسەکان --}}
        @if (auth()->user()->canSeeMoney() && isset($recentPayments))
            <div class="card overflow-hidden">
                <div class="card-head flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-slate-800">دواین جوڵەی قاسە و پارەدانەکان</span>
                    </div>
                    <a wire:navigate href="{{ route('payments.index') }}" class="text-xs text-slate-500 hover:text-slate-800 hover:underline">هەموو پارەدانەکان &larr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>جۆر</th>
                                <th>بڕی پارە</th>
                                <th>قاسە</th>
                                <th>لایەن / تێبینی</th>
                                <th>بەروار</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentPayments as $p)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td>
                                        <span class="badge {{ $p->direction === 'in' ? 'badge-ok' : 'badge-danger' }}">
                                            {{ $p->direction === 'in' ? 'داهات (وەرگیراو)' : 'خەرجی (دراو)' }}
                                        </span>
                                    </td>
                                    <td class="num font-bold {{ $p->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $p->direction === 'in' ? '+' : '-' }}{{ fmt_money($p->amount, $p->currency) }}
                                    </td>
                                    <td class="text-xs text-slate-600">{{ $p->cashBox?->name ?? '—' }}</td>
                                    <td class="text-xs text-slate-700">
                                        {{ $p->party?->name ?? $p->notes ?? '—' }}
                                    </td>
                                    <td class="num whitespace-nowrap text-xs text-slate-400">{{ fmt_date($p->paid_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-slate-400">
                                        هیچ جوڵەیەکی پارە تۆمار نەکراوە.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    {{-- ستوونی چەپ: ویدیجتەکانی دۆخی کارگە و مەخزەن و ئامادەبوون (Side 1 Col) --}}
    <div class="space-y-6">

        {{-- ویدیجتی باڵانسی قاسەکان --}}
        @if (auth()->user()->canSeeMoney() && isset($cashBoxes))
            <div class="card p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-cyan-600"></span>
                        <span class="text-sm font-bold text-slate-800">باڵانسی قاسەکان</span>
                    </div>
                    <a wire:navigate href="{{ route('cash.index') }}" class="text-xs text-blue-600 hover:underline">قاسە &larr;</a>
                </div>
                <div class="mt-3 space-y-2.5">
                    @foreach ($cashBoxes as $box)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 border border-slate-100">
                            <span class="text-xs font-medium text-slate-600">{{ $box->name }}</span>
                            <span class="num font-bold text-slate-800">{{ fmt_money($box->balance(), $box->currency) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ویدیجتی مەخزەن و ئاگاداری کەمی کاڵا --}}
        <div class="card p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    @if ($lowStock->isNotEmpty())
                        <span class="size-2 animate-pulse rounded-full bg-rose-500"></span>
                        <span class="text-sm font-bold text-rose-600">ئاگاداری کەمی بابەت</span>
                    @else
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span class="text-sm font-bold text-slate-800">دۆخی کۆگا</span>
                    @endif
                </div>
                <a wire:navigate href="{{ route('items.index') }}" class="text-xs text-blue-600 hover:underline">کۆگا &larr;</a>
            </div>

            <div class="mt-3">
                @if ($lowStock->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach ($lowStock->take(4) as $item)
                            <div class="flex items-center justify-between rounded-xl bg-rose-50/60 border border-rose-100 p-2.5 text-xs">
                                <span class="font-medium text-slate-800">{{ $item->name }}</span>
                                <span class="num font-bold text-rose-600">
                                    {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                                    <span class="font-normal text-slate-400 text-[11px]">(کەمترین: {{ fmt_qty($item->min_qty) }})</span>
                                </span>
                            </div>
                        @endforeach

                        @if ($lowStock->count() > 4)
                            <div class="pt-1 text-center">
                                <a wire:navigate href="{{ route('items.index', ['low' => 1]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                                    + {{ $lowStock->count() - 4 }} بابەتی تریش لە سنووری کەمترینە
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50 p-3 text-xs text-emerald-800 border border-emerald-100">
                        <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                        </svg>
                        <span>هەموو کاڵاکان لە ئاستی پێویستدان و کەمی نییە.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ویدیجتی ئامادەبوونی کارمەندان --}}
        @if (auth()->user()->can('manage_employees') && isset($totalEmployees))
            <div class="card p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-violet-600"></span>
                        <span class="text-sm font-bold text-slate-800">ئامادەبوونی کارمەندان</span>
                    </div>
                    <a wire:navigate href="{{ route('attendance.index') }}" class="text-xs text-blue-600 hover:underline">هاتن و چوون &larr;</a>
                </div>
                <div class="mt-3">
                    <div class="flex items-center justify-between text-xs text-slate-600 mb-2">
                        <span>ئامادەبووان:</span>
                        <span class="num font-bold text-emerald-600">{{ $presentToday ?? 0 }} لە {{ $totalEmployees }}</span>
                    </div>
                    @php
                        $pct = $totalEmployees > 0 ? round((($presentToday ?? 0) / $totalEmployees) * 100) : 0;
                    @endphp
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                        <span>ڕێژەی ئامادەبوون</span>
                        <span class="num font-semibold text-slate-700">{{ $pct }}%</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- ویدیجتی ئیشی خاریجی کراوە --}}
        @if (auth()->user()->can('manage_external_jobs') && isset($activeJobs) && $activeJobs->isNotEmpty())
            <div class="card p-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span class="text-sm font-bold text-slate-800">ئیشی خاریجی کراوە ({{ $activeJobsCount }})</span>
                    </div>
                    <a wire:navigate href="{{ route('external-jobs.index') }}" class="text-xs text-blue-600 hover:underline">هەمووی &larr;</a>
                </div>
                <div class="mt-3 space-y-2">
                    @foreach ($activeJobs as $job)
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 text-xs border border-slate-100">
                            <div>
                                <div class="font-semibold text-slate-800">{{ $job->title }}</div>
                                <div class="text-[11px] text-slate-400">{{ $job->contractor_name ?? '—' }}</div>
                            </div>
                            <span class="num font-bold text-amber-600">{{ fmt_money($job->cost, $job->currency) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

</div>

@endsection
