@extends('layouts.app')
@section('title', 'بەشی فرۆشتن')

@section('actions')
    <a href="{{ route('orders.create') }}"
       class="btn btn-primary !py-2 !px-4 text-xs font-bold gap-1.5 shadow-sm bg-blue-600 hover:bg-blue-700">
        <span>+</span>
        <span>فرۆشتنی نوێ</span>
    </a>
@endsection

@section('content')

{{-- ١. کارتەکانی ئاماری سەرەوە (هاوشێوەی دیزاینی فرۆشتن) --}}
<div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    {{-- کۆی وەسڵەکان --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-blue-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-3xl font-black text-slate-800 num tracking-tight">{{ fmt_num($totalOrders) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">کۆی وەسڵەکان</div>
        </div>
        <div class="size-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
            📄
        </div>
    </div>

    {{-- کۆی فرۆشتن --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-emerald-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-slate-800 num tracking-tight">{{ fmt_money($totalSales) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">کۆی فرۆشتن (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            💵
        </div>
    </div>

    {{-- پارەی وەرگیراو --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-teal-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-teal-700 num tracking-tight">{{ fmt_money($totalReceived) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">پارەی وەرگیراو (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
            💼
        </div>
    </div>

    {{-- قەرزی ماوە --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-rose-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-rose-600 num tracking-tight">{{ fmt_money($totalDebt) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">قەرزی ماوە (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
            ⚠️
        </div>
    </div>
</div>

<div x-data="{ tab: '{{ $activeTab }}' }">
    {{-- ٢. سویچەری نێوان تابەکان --}}
    <div class="flex items-center gap-2 mb-4">
        <button @click="tab = 'customers'"
                :class="tab === 'customers' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>👥 کڕیارەکان</span>
            <span :class="tab === 'customers' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                  class="px-2 py-0.5 rounded-full text-2xs font-mono font-bold">{{ $totalCustomers }}</span>
        </button>

        <button @click="tab = 'orders'"
                :class="tab === 'orders' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
            <span>🧾 هەموو وەسڵەکان</span>
            <span :class="tab === 'orders' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                  class="px-2 py-0.5 rounded-full text-2xs font-mono font-bold">{{ $totalOrders }}</span>
        </button>
    </div>

    {{-- ٣. تابی یەکەم: کڕیارەکان و قەرزەکانیان (وەک وێنەکە) --}}
    <div x-show="tab === 'customers'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span>👥</span>
                <span>کڕیارەکان و قەرزەکانیان</span>
            </div>

            {{-- خانەی گەڕان --}}
            <form method="GET" class="w-full sm:w-72">
                <input type="hidden" name="tab" value="customers">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('tab') === 'customers' ? request('q') : '' }}"
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                           placeholder="گەڕان لە کڕیارەکان...">
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">کڕیار</th>
                        <th class="py-3 px-4 text-center">تەلەفۆن</th>
                        <th class="py-3 px-4 text-center">ژمارەی وەسڵ</th>
                        <th class="py-3 px-4 text-center">کۆی فرۆشتن</th>
                        <th class="py-3 px-4 text-center">دراو</th>
                        <th class="py-3 px-4 text-center">کۆی قەرز</th>
                        <th class="py-3 px-4 text-center">دوایین فرۆشتن</th>
                        <th class="py-3 px-4 text-center w-20">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($customers as $index => $customer)
                        @php
                            $bal = $customer->balance();
                            $custTotalSales = (float) $customer->orders->sum(fn($o) => $o->total_iqd);
                            $custTotalPaid = (float) $customer->payments()->where('direction', 'in')->sum('amount_iqd');
                            $lastOrder = $customer->orders->first();
                            $firstLetter = mb_substr($customer->name, 0, 1, 'UTF-8');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $customers->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $customer) }}" class="flex items-center gap-2.5 group">
                                    <span class="size-7 rounded-full bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        {{ $firstLetter }}
                                    </span>
                                    <span class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                        {{ $customer->name }}
                                    </span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600" dir="ltr">
                                @if ($customer->phone)
                                    <span>{{ $customer->phone }}</span> <span class="text-slate-400">📞</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100">
                                    {{ fmt_num($customer->orders_count) }} وەسڵ
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center num font-bold text-slate-800">
                                {{ fmt_money($custTotalSales) }}
                            </td>
                            <td class="py-3.5 px-4 text-center num font-semibold text-emerald-700">
                                <span class="inline-block size-1.5 rounded-full bg-emerald-500 ml-1"></span>
                                {{ fmt_money($custTotalPaid) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($bal <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>✓</span> <span>بێ قەرز</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 num">
                                        <span>{{ fmt_money($bal) }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-500">
                                {{ $lastOrder ? fmt_date($lastOrder->order_date) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($bal > 0)
                                        <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $customer->id]) }}"
                                           class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold inline-flex items-center gap-0.5 transition-colors"
                                           title="وەرگرتنی حەقدی">
                                            + حەقدی
                                        </a>
                                    @endif
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="size-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 inline-flex items-center justify-center transition-colors shadow-2xs"
                                       title="پڕۆفایلی کڕیار">
                                        👁️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400 text-sm font-medium">
                                هیچ کڕیارێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- ٤. تابی دووەم: هەموو وەسڵەکان --}}
    <div x-show="tab === 'orders'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span>🧾</span>
                <span>هەموو وەسڵەکان</span>
            </div>

            {{-- فۆرمی گەڕان و فلتەر --}}
            <form method="GET" class="w-full sm:w-72">
                <input type="hidden" name="tab" value="orders">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('tab') === 'orders' ? request('q') : '' }}"
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                           placeholder="ژمارەی وەسڵ یان کڕیار...">
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4 text-center">وەسڵ</th>
                        <th class="py-3 px-4">بەڕێز (کڕیار)</th>
                        <th class="py-3 px-4 text-center">بەروار</th>
                        <th class="py-3 px-4 text-center">کۆی گشتی</th>
                        <th class="py-3 px-4 text-center">دراوە</th>
                        <th class="py-3 px-4 text-center">ماوە (قەرز)</th>
                        <th class="py-3 px-4 text-center w-24">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($orders as $index => $order)
                        @php
                            $remaining = $order->remaining();
                            $paid = $order->paidAmount();
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer"
                            onclick="if (!event.target.closest('a')) window.open('{{ route('orders.print', $order) }}', '_blank')">
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $orders->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('orders.print', $order) }}" target="_blank"
                                   class="inline-flex items-center justify-center min-w-8 px-3 py-1 rounded-lg text-xs font-mono font-bold text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 shadow-2xs hover:shadow-xs transition-all cursor-pointer"
                                   title="کرتە بکە بۆ کردنەوە و چاپی وەسڵ">
                                    {{ $order->invoice_no }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($order->customer)
                                    <a href="{{ route('customers.show', $order->customer) }}" class="font-bold text-slate-800 hover:text-blue-600 transition-colors">
                                        {{ $order->customer->name }}
                                    </a>
                                    @if ($order->customer->phone)
                                        <span class="num block text-xs text-slate-400" dir="ltr">{{ $order->customer->phone }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600">
                                {{ fmt_date($order->order_date) }}
                            </td>
                            <td class="py-3.5 px-4 text-center num font-bold text-slate-900">
                                {{ fmt_money($order->total, $order->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-center num font-semibold text-emerald-700">
                                {{ fmt_money($paid, $order->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-center num font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $remaining > 0 ? fmt_money($remaining, $order->currency) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if ($remaining > 0)
                                        <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $order->customer_id, 'order' => $order->id]) }}"
                                           class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-0.5 transition-colors shadow-2xs"
                                           title="وەرگرتنی حەقدی بۆ ئەم وەسڵە">
                                            + حەقدی
                                        </a>
                                    @endif
                                    <a href="{{ route('orders.print', $order) }}" target="_blank"
                                       class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                        چاپ
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 text-sm font-medium">
                                هیچ وەسڵێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
