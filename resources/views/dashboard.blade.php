@extends('layouts.menu')

@section('content')

{{-- ── ١. سەردێڕی سەرەکی: بەخێرهاتن و کاتژمێری زیندووی کوردی ── --}}
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-none">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- ڕاست: لۆگۆ و ناوی بەکارهێنەر --}}
        <div class="flex items-center gap-3.5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 border border-blue-100 font-bold text-lg">
                هـ
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-bold text-slate-800">
                        بەخێربێیتەوە، {{ auth()->user()->name }}
                    </h1>
                    <span class="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                        {{ auth()->user()->isAdmin() ? 'بەڕێوەبەر' : 'بەرپرسی کۆگا' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    سیستەمی بەڕێوەبردنی کارگەی ئاسنگەری هێمن — داشبۆردی گشتی
                </p>
            </div>
        </div>

        {{-- چەپ: سەعات و بەرواری زیندوو --}}
        <div x-data="{
            time: '',
            date: '',
            init() {
                const tick = () => {
                    const now = new Date();
                    const days = ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'];
                    const months = ['١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '١٠', '١١', '١٢'];
                    
                    let h = now.getHours();
                    const m = String(now.getMinutes()).padStart(2, '0');
                    const s = String(now.getSeconds()).padStart(2, '0');
                    const ampm = h >= 12 ? 'پ.ن (PM)' : 'ب.ن (AM)';
                    h = h % 12 || 12;
                    const hStr = String(h).padStart(2, '0');

                    this.time = `${hStr}:${m}:${s} ${ampm}`;
                    this.date = `${days[now.getDay()]} · ${now.getFullYear()}/${months[now.getMonth()]}/${now.getDate()}`;
                };
                tick();
                setInterval(tick, 1000);
            }
        }" class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 self-start md:self-auto">
            <div class="flex size-9 items-center justify-center rounded-lg bg-white border border-slate-200 text-blue-600">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="text-right">
                <div class="font-bold text-sm text-slate-800 tracking-wide font-mono" dir="ltr" style="text-align: right" x-text="time">--:--:--</div>
                <div class="text-[11px] font-medium text-slate-500" x-text="date">--</div>
            </div>
        </div>
    </div>
</div>

{{-- ── ٢. تابلۆی کورتە-ئاماری خێرای سەرەوە (KPI Cards) ── --}}
@if (auth()->user()->canSeeMoney())
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5">
        {{-- فرۆشی ئەمڕۆ --}}
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">فرۆشی ئەمڕۆ</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    @include('partials.icon', ['name' => 'orders', 'class' => 'size-4.5'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold text-emerald-600">{{ fmt_money($todaySales) }}</div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>فرۆشی ئەم مانگە:</span>
                <span class="num font-semibold text-slate-700">{{ fmt_money($monthSales ?? 0) }}</span>
            </div>
        </div>

        {{-- وەسڵەکانی ئەمڕۆ / کراوە --}}
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">وەسڵەکانی ئەمڕۆ</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    @include('partials.icon', ['name' => 'orders', 'class' => 'size-4.5'])
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
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">داهاتی ئەمڕۆی قاسە</span>
                <span class="flex size-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                    @include('partials.icon', ['name' => 'cash', 'class' => 'size-4.5'])
                </span>
            </div>
            <div class="num mt-2 text-xl font-bold text-cyan-700">{{ fmt_money($todayIn) }}</div>
            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                <span>خەرجی ئەمڕۆ:</span>
                <span class="num font-semibold text-rose-600">{{ fmt_money($todayOut ?? 0) }}</span>
            </div>
        </div>

        {{-- کۆی قەرزی کڕیاران --}}
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">کۆی قەرزی کڕیاران</span>
                <span class="flex size-8 items-center justify-center rounded-lg {{ $receivables > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                    @include('partials.icon', ['name' => 'debts', 'class' => 'size-4.5'])
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
        <a wire:navigate href="{{ route('attendance.index') }}" class="btn btn-secondary !py-2 !px-3.5 text-xs font-semibold flex items-center gap-2">
            <svg class="size-4 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
            <span>+ تۆماری ئامادەبوون</span>
        </a>
    @endif
</div>

{{-- ── ٤. بەشی چالاکییەکان و خشتە سەرەکییەکان (Main Grid) ── --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- ستوونی سەرەکی (2 Cols) --}}
    <div class="space-y-6 lg:col-span-2">

        {{-- تابلۆی دواین وەسڵ و داواکارییەکان --}}
        <div class="card overflow-hidden">
            <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-blue-600"></span>
                    <span class="font-bold text-slate-800 text-sm">دواین وەسڵ و داواکارییەکان</span>
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
                        <tr class="bg-slate-50 text-slate-600 text-xs border-b border-slate-200">
                            <th class="p-3 text-right">ژمارە</th>
                            <th class="p-3 text-right">کڕیار</th>
                            <th class="p-3 text-right">بەروار</th>
                            <th class="p-3 text-left num">کۆی گشتی</th>
                            <th class="p-3 text-center">دۆخ</th>
                            <th class="p-3 text-left">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="p-3 num font-bold text-blue-600">#{{ $order->invoice_no }}</td>
                                <td class="p-3 font-medium text-slate-800">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="p-3 num text-slate-500">{{ fmt_date($order->order_date) }}</td>
                                <td class="p-3 num font-bold text-slate-800">{{ fmt_money($order->total, $order->currency) }}</td>
                                <td class="p-3 text-center">
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
                                <td class="p-3 text-left whitespace-nowrap">
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

        {{-- تابلۆی هاتن و چوونی کارمەندان (Check-in & Check-out) --}}
        @if (auth()->user()->can('manage_employees'))
            <div class="card overflow-hidden">
                <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-violet-600"></span>
                        <span class="font-bold text-slate-800 text-sm">هاتن و چوونی کارمەندانی ئەمڕۆ (Check-In / Check-Out)</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <a wire:navigate href="{{ route('attendance.index') }}" class="font-semibold text-blue-600 hover:underline">+ تۆمارکردنی کاتژمێر</a>
                        <span class="text-slate-300">|</span>
                        <a wire:navigate href="{{ route('attendance.index') }}" class="text-slate-500 hover:text-slate-800 hover:underline">هەموو کارمەندان &larr;</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs border-b border-slate-200">
                                <th class="p-3 text-right">کارمەند</th>
                                <th class="p-3 text-right">کاتی هاتن (Check-In)</th>
                                <th class="p-3 text-right">کاتی ڕۆیشتن (Check-Out)</th>
                                <th class="p-3 text-center">کاتژمێر</th>
                                <th class="p-3 text-center">دۆخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse ($todayAttendances ?? [] as $att)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-3 font-semibold text-slate-800">{{ $att->employee?->name ?? '—' }}</td>
                                    <td class="p-3 num font-medium text-emerald-600">
                                        {{ $att->check_in ? $att->check_in : '—' }}
                                    </td>
                                    <td class="p-3 num font-medium text-slate-600">
                                        {{ $att->check_out ? $att->check_out : ($att->status === 'present' ? 'لە کاردایە' : '—') }}
                                    </td>
                                    <td class="p-3 text-center num font-bold text-slate-800">
                                        {{ $att->hours > 0 ? $att->hours . ' ک' : '—' }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="badge {{ match($att->status) {
                                            'present' => 'badge-ok',
                                            'absent' => 'badge-danger',
                                            'leave' => 'badge-warn',
                                            default => 'badge-secondary'
                                        } }}">
                                            {{ $att->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">
                                        تۆماری ئامادەبوونی ئەمڕۆ دەستی پێنەکردووە. <a wire:navigate href="{{ route('attendance.index') }}" class="text-blue-600 font-semibold hover:underline">تۆمارکردنی هاتن و چوون</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- تابلۆی دواین جوڵەی قاسە و پارەدانەکان --}}
        @if (auth()->user()->canSeeMoney() && isset($recentPayments))
            <div class="card overflow-hidden">
                <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-slate-800 text-sm">دواین جوڵەی قاسە و پارەدانەکان</span>
                    </div>
                    <a wire:navigate href="{{ route('payments.index') }}" class="text-xs text-slate-500 hover:text-slate-800 hover:underline">هەمووی &larr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs border-b border-slate-200">
                                <th class="p-3 text-right">جۆر</th>
                                <th class="p-3 text-left num">بڕی پارە</th>
                                <th class="p-3 text-right">قاسە</th>
                                <th class="p-3 text-right">لایەن / تێبینی</th>
                                <th class="p-3 text-right">بەروار</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse ($recentPayments as $p)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-3">
                                        <span class="badge {{ $p->direction === 'in' ? 'badge-ok' : 'badge-danger' }}">
                                            {{ $p->direction === 'in' ? 'داهات (وەرگیراو)' : 'خەرجی (دراو)' }}
                                        </span>
                                    </td>
                                    <td class="p-3 num font-bold {{ $p->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $p->direction === 'in' ? '+' : '-' }}{{ fmt_money($p->amount, $p->currency) }}
                                    </td>
                                    <td class="p-3 text-slate-600">{{ $p->cashBox?->name ?? '—' }}</td>
                                    <td class="p-3 text-slate-700 font-medium">
                                        {{ $p->party?->name ?? $p->notes ?? '—' }}
                                    </td>
                                    <td class="p-3 num text-slate-400">{{ fmt_date($p->paid_at) }}</td>
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

    {{-- ستوونی تەنیشت (Side 1 Col) --}}
    <div class="space-y-6">

        {{-- ویدیجتی مەوادی کەمبوو لە کۆگا (Low Stock Items) --}}
        <div class="card overflow-hidden">
            <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    @if ($lowStock->isNotEmpty())
                        <span class="size-2 animate-pulse rounded-full bg-rose-500"></span>
                        <span class="font-bold text-rose-600 text-sm">ئاگاداری کەمی مەواد لە کۆگا</span>
                    @else
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-slate-800 text-sm">دۆخی مەخزەن و مەواد</span>
                    @endif
                </div>
                <a wire:navigate href="{{ route('items.index') }}" class="text-xs text-blue-600 hover:underline">کۆگا &larr;</a>
            </div>

            <div class="p-4">
                @if ($lowStock->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($lowStock->take(5) as $item)
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 last:border-0 last:pb-0 text-xs">
                                <div>
                                    <div class="font-bold text-slate-800">{{ $item->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        سنووری ئاگاداری: <span class="num text-slate-600 font-semibold">{{ fmt_qty($item->min_qty) }}</span> {{ $item->unit?->name }}
                                    </div>
                                </div>
                                <div class="text-left">
                                    <div class="num font-bold text-rose-600 text-sm">
                                        {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                                    </div>
                                    <span class="inline-block mt-0.5 rounded px-1.5 py-0.2 text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">
                                        کەمە
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        @if ($lowStock->count() > 5)
                            <div class="pt-2 text-center border-t border-slate-100">
                                <a wire:navigate href="{{ route('items.index', ['low' => 1]) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                    + {{ $lowStock->count() - 5 }} بابەتی تریش لە سنووری کەمییە
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2.5 rounded-lg bg-emerald-50 p-3 text-xs text-emerald-800 border border-emerald-100">
                        <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/>
                        </svg>
                        <span>سەرجەم مەوادەکان لە ئاستی پێویستدان.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ویدیجتی باڵانسی قاسەکان --}}
        @if (auth()->user()->canSeeMoney() && isset($cashBoxes))
            <div class="card overflow-hidden">
                <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-cyan-600"></span>
                        <span class="font-bold text-slate-800 text-sm">باڵانسی قاسەکان</span>
                    </div>
                    <a wire:navigate href="{{ route('cash.index') }}" class="text-xs text-blue-600 hover:underline">قاسە &larr;</a>
                </div>
                <div class="p-4 space-y-3">
                    @foreach ($cashBoxes as $box)
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 last:border-0 last:pb-0 text-xs">
                            <span class="font-medium text-slate-700">{{ $box->name }}</span>
                            <span class="num font-bold text-slate-900 text-sm">{{ fmt_money($box->balance(), $box->currency) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ویدیجتی ئیشی خاریجی کراوە --}}
        @if (auth()->user()->can('manage_external_jobs') && isset($activeJobs) && $activeJobs->isNotEmpty())
            <div class="card overflow-hidden">
                <div class="card-head flex items-center justify-between px-4 py-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span class="font-bold text-slate-800 text-sm">ئیشی خاریجی چالاک ({{ $activeJobsCount }})</span>
                    </div>
                    <a wire:navigate href="{{ route('external-jobs.index') }}" class="text-xs text-blue-600 hover:underline">هەمووی &larr;</a>
                </div>
                <div class="p-4 space-y-3">
                    @foreach ($activeJobs as $job)
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 last:border-0 last:pb-0 text-xs">
                            <div>
                                <div class="font-bold text-slate-800">{{ $job->title }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $job->contractor_name ?? '—' }}</div>
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
