@extends('layouts.menu')

@section('content')

<div class="space-y-5">

    {{-- ── ١. تابلۆی کورتە-ئامارە سەرەکییەکان (Top KPI Summary Cards) ── --}}
    @if (auth()->user()->canSeeMoney())
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
            {{-- فرۆشی ئەمڕۆ --}}
            <div class="card p-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">فرۆشی ئەمڕۆ</span>
                    <span class="flex size-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        @include('partials.icon', ['name' => 'orders', 'class' => 'size-4'])
                    </span>
                </div>
                <div class="num mt-1.5 text-lg font-bold text-emerald-600">{{ fmt_money($todaySales) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">
                    ئەم مانگە: <span class="num font-medium text-slate-600">{{ fmt_money($monthSales ?? 0) }}</span>
                </div>
            </div>

            {{-- وەسڵەکانی ئەمڕۆ / کراوە --}}
            <div class="card p-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">وەسڵەکانی ئەمڕۆ</span>
                    <span class="flex size-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        @include('partials.icon', ['name' => 'orders', 'class' => 'size-4'])
                    </span>
                </div>
                <div class="num mt-1.5 text-lg font-bold text-slate-800">
                    {{ fmt_num($todayOrders) }} <span class="text-xs font-normal text-slate-400">وەسڵ</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    لە کاردا: <span class="num font-medium text-blue-600">{{ fmt_num($inProductionCount ?? 0) }}</span> وەسڵ
                </div>
            </div>

            {{-- داهاتی ئەمڕۆی قاسە --}}
            <div class="card p-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">داهاتی ئەمڕۆی قاسە</span>
                    <span class="flex size-7 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                        @include('partials.icon', ['name' => 'cash', 'class' => 'size-4'])
                    </span>
                </div>
                <div class="num mt-1.5 text-lg font-bold text-cyan-700">{{ fmt_money($todayIn) }}</div>
                <div class="mt-1 text-[11px] text-slate-400">
                    خەرجی ئەمڕۆ: <span class="num font-medium text-rose-600">{{ fmt_money($todayOut ?? 0) }}</span>
                </div>
            </div>

            {{-- کۆی قەرزی کڕیاران --}}
            <div class="card p-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500">کۆی قەرزی کڕیاران</span>
                    <span class="flex size-7 items-center justify-center rounded-lg {{ $receivables > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                        @include('partials.icon', ['name' => 'debts', 'class' => 'size-4'])
                    </span>
                </div>
                <div class="num mt-1.5 text-lg font-bold {{ $receivables > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                    {{ fmt_money($receivables) }}
                </div>
                <div class="mt-1 text-[11px] text-slate-400">
                    قەرزی فرۆشیاران: <span class="num font-medium text-slate-600">{{ fmt_money($payables ?? 0) }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ── ٢. دوگمە خێراکانی دەستپێکردن (Quick Actions) ── --}}
    <div class="flex flex-wrap items-center gap-2">
        @if (auth()->user()->can('manage_orders'))
            <a wire:navigate href="{{ route('orders.create') }}" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-semibold flex items-center gap-1.5">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>وەسڵی نوێ</span>
            </a>
        @endif

        @if (auth()->user()->can('manage_purchases'))
            <a wire:navigate href="{{ route('purchases.create') }}" class="btn btn-secondary !py-1.5 !px-3 text-xs font-semibold flex items-center gap-1.5">
                <svg class="size-3.5 text-cyan-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 01-8 0"></path>
                </svg>
                <span>پسوولەی کڕین</span>
            </a>
        @endif

        @if (auth()->user()->can('manage_payments'))
            <a wire:navigate href="{{ route('payments.create') }}" class="btn btn-secondary !py-1.5 !px-3 text-xs font-semibold flex items-center gap-1.5">
                <svg class="size-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>تۆماری پارەدان</span>
            </a>
        @endif

        @if (auth()->user()->can('manage_external_jobs'))
            <a wire:navigate href="{{ route('external-jobs.create') }}" class="btn btn-secondary !py-1.5 !px-3 text-xs font-semibold flex items-center gap-1.5">
                <svg class="size-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                </svg>
                <span>ئیشی خاریجی</span>
            </a>
        @endif

        @if (auth()->user()->can('manage_stock'))
            <a wire:navigate href="{{ route('stock.create') }}" class="btn btn-secondary !py-1.5 !px-3 text-xs font-semibold flex items-center gap-1.5">
                <svg class="size-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
                <span>جوڵەی کۆگا</span>
            </a>
        @endif

        @if (auth()->user()->can('manage_employees'))
            <a wire:navigate href="{{ route('attendance.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs text-slate-600 hover:text-blue-700 font-medium">
                <span>تۆماری ئامادەبوون &larr;</span>
            </a>
        @endif
    </div>

    {{-- ── ٣. بەشی سەرەکی و پەڕەکان (Balanced Clean 2-Column Grid) ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- لای چەپ / ستوونی لاوەکی لە شاشەی پاندا (4 لە 12) --}}
        <div class="lg:col-span-4 space-y-4">

            {{-- ١. ئاگاداری کەمی مەواد لە کۆگا --}}
            <div class="card p-4">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        @if ($lowStock->isNotEmpty())
                            <span class="size-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span class="text-xs font-bold text-rose-600">ئاگاداری کەمی مەواد</span>
                        @else
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-800">دۆخی مەخزەن</span>
                        @endif
                    </div>
                    <a wire:navigate href="{{ route('items.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">کۆگا &larr;</a>
                </div>

                <div class="mt-3">
                    @if ($lowStock->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach ($lowStock->take(4) as $item)
                                <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-semibold text-slate-800">{{ $item->name }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            کەمترین: <span class="num font-medium text-slate-600">{{ fmt_qty($item->min_qty) }}</span> {{ $item->unit?->name }}
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <div class="num font-bold text-rose-600">
                                            {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                                        </div>
                                        <span class="inline-block mt-0.5 rounded px-1.5 py-0.2 text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">
                                            کەمە
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($lowStock->count() > 4)
                            <div class="pt-2.5 mt-2 border-t border-slate-100 text-center">
                                <a wire:navigate href="{{ route('items.index', ['low' => 1]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                                    + {{ $lowStock->count() - 4 }} بابەتی تریش کەمە
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-xs text-emerald-700 bg-emerald-50/70 p-2.5 rounded-lg border border-emerald-100 flex items-center gap-2">
                            <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                            </svg>
                            <span>هەموو مەوادەکان لە ئاستی پێویستدان.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ٢. باڵانسی قاسەکان --}}
            @if (auth()->user()->canSeeMoney() && isset($cashBoxes))
                <div class="card p-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-cyan-600"></span>
                            <span class="text-xs font-bold text-slate-800">باڵانسی قاسەکان</span>
                        </div>
                        <a wire:navigate href="{{ route('cash.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">قاسە &larr;</a>
                    </div>
                    <div class="mt-3 divide-y divide-slate-100 text-xs">
                        @foreach ($cashBoxes as $box)
                            <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between">
                                <span class="font-medium text-slate-600">{{ $box->name }}</span>
                                <span class="num font-bold text-slate-800">{{ fmt_money($box->balance(), $box->currency) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ٣. ئامادەبوونی ئەمڕۆی کارمەندان (Attendance Summary) --}}
            @if (auth()->user()->can('manage_employees') && isset($totalEmployees))
                <div class="card p-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-violet-600"></span>
                            <span class="text-xs font-bold text-slate-800">ئامادەبوونی ئەمڕۆ</span>
                        </div>
                        <a wire:navigate href="{{ route('attendance.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">تۆمار &larr;</a>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
                            <span>ئامادەبووانی ئەمڕۆ:</span>
                            <span class="num font-bold text-emerald-600">{{ $presentToday ?? 0 }} لە {{ $totalEmployees }}</span>
                        </div>
                        @php
                            $pct = $totalEmployees > 0 ? round((($presentToday ?? 0) / $totalEmployees) * 100) : 0;
                        @endphp
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>

                        {{-- لیستی خێرای کاتی هاتن و چوون --}}
                        @if (isset($todayAttendances) && $todayAttendances->isNotEmpty())
                            <div class="mt-3 pt-2.5 border-t border-slate-100 space-y-1.5 text-[11px]">
                                @foreach ($todayAttendances->take(3) as $att)
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-slate-700">{{ $att->employee?->name }}</span>
                                        <span class="num font-medium text-emerald-600">
                                            {{ $att->check_in ? $att->check_in : 'ئامادەیە' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ٤. ئیشی خاریجی کراوە --}}
            @if (auth()->user()->can('manage_external_jobs') && isset($activeJobs) && $activeJobs->isNotEmpty())
                <div class="card p-4">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold text-slate-800">ئیشی خاریجی ({{ $activeJobsCount }})</span>
                        </div>
                        <a wire:navigate href="{{ route('external-jobs.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">هەمووی &larr;</a>
                    </div>
                    <div class="mt-3 divide-y divide-slate-100 text-xs">
                        @foreach ($activeJobs->take(3) as $job)
                            <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between">
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

        {{-- لای ڕاست / ستوونی سەرەکی خشتەکان (8 لە 12) --}}
        <div class="lg:col-span-8 space-y-4">

            {{-- خشتەی دواین وەسڵ و داواکارییەکان --}}
            <div class="card overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-blue-600"></span>
                        <span class="font-bold text-slate-800 text-xs sm:text-sm">دواین وەسڵ و داواکارییەکان</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if (auth()->user()->can('manage_orders'))
                            <a wire:navigate href="{{ route('orders.create') }}" class="font-semibold text-blue-600 hover:underline">+ وەسڵی نوێ</a>
                            <span class="text-slate-300">|</span>
                        @endif
                        <a wire:navigate href="{{ route('orders.index') }}" class="text-slate-500 hover:text-slate-800 hover:underline">هەمووی &larr;</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-slate-50/70 text-slate-600 text-xs border-b border-slate-100">
                                <th class="py-2.5 px-3 text-right font-semibold">ژمارە</th>
                                <th class="py-2.5 px-3 text-right font-semibold">کڕیار</th>
                                <th class="py-2.5 px-3 text-right font-semibold">بەروار</th>
                                <th class="py-2.5 px-3 text-left num font-semibold">کۆی گشتی</th>
                                <th class="py-2.5 px-3 text-center font-semibold">دۆخ</th>
                                <th class="py-2.5 px-3 text-left font-semibold">کردار</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse ($recentOrders as $order)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-2.5 px-3 num font-bold text-blue-600">#{{ $order->invoice_no }}</td>
                                    <td class="py-2.5 px-3 font-medium text-slate-800">{{ $order->customer?->name ?? '—' }}</td>
                                    <td class="py-2.5 px-3 num text-slate-500">{{ fmt_date($order->order_date) }}</td>
                                    <td class="py-2.5 px-3 num font-bold text-slate-800">{{ fmt_money($order->total, $order->currency) }}</td>
                                    <td class="py-2.5 px-3 text-center">
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
                                    <td class="py-2.5 px-3 text-left whitespace-nowrap">
                                        <a wire:navigate href="{{ route('orders.show', $order) }}" class="font-semibold text-blue-600 hover:underline">بینین</a>
                                        <a href="{{ route('orders.print', $order) }}" target="_blank" class="mr-2 text-slate-400 hover:text-slate-700">چاپ</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400">
                                        هیچ وەسڵێک تۆمار نەکراوە.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- خشتەی دواین جوڵەی قاسە و پارەدانەکان --}}
            @if (auth()->user()->canSeeMoney() && isset($recentPayments))
                <div class="card overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">دواین جوڵەی قاسە و پارەدانەکان</span>
                        </div>
                        <a wire:navigate href="{{ route('payments.index') }}" class="text-xs text-slate-500 hover:text-slate-800 hover:underline">هەمووی &larr;</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-slate-50/70 text-slate-600 text-xs border-b border-slate-100">
                                    <th class="py-2.5 px-3 text-right font-semibold">جۆر</th>
                                    <th class="py-2.5 px-3 text-left num font-semibold">بڕی پارە</th>
                                    <th class="py-2.5 px-3 text-right font-semibold">قاسە</th>
                                    <th class="py-2.5 px-3 text-right font-semibold">لایەن / تێبینی</th>
                                    <th class="py-2.5 px-3 text-right font-semibold">بەروار</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @forelse ($recentPayments as $p)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-2.5 px-3">
                                            <span class="badge {{ $p->direction === 'in' ? 'badge-ok' : 'badge-danger' }}">
                                                {{ $p->direction === 'in' ? 'داهات (وەرگیراو)' : 'خەرجی (دراو)' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 num font-bold {{ $p->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $p->direction === 'in' ? '+' : '-' }}{{ fmt_money($p->amount, $p->currency) }}
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-600">{{ $p->cashBox?->name ?? '—' }}</td>
                                        <td class="py-2.5 px-3 text-slate-700 font-medium">
                                            {{ $p->party?->name ?? $p->notes ?? '—' }}
                                        </td>
                                        <td class="py-2.5 px-3 num text-slate-400">{{ fmt_date($p->paid_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-6 text-center text-slate-400">
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

    </div>

</div>

@endsection
