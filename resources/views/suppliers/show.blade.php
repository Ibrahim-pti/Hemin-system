@extends('layouts.app')
@section('title', 'حیسابی فرۆشیار: ' . $supplier->name)

@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('purchases.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary !py-1.5 !px-3 text-xs gap-1">
            <span>+ پسوولەی کڕین</span>
        </a>
        <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100">
            <span>+ پارەدان (حەقدی)</span>
        </a>
        <button type="button" onclick="window.print()" class="btn btn-ghost !py-1.5 !px-3 text-xs text-slate-700 hover:bg-slate-100 no-print">
            چاپکردن
        </button>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs no-print">
            دەستکاری
        </a>
    </div>
@endsection

@section('content')

{{-- ١. کورتەی زانیاری و کارتەکانی ئامار --}}
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
    {{-- زانیاری فرۆشیار --}}
    <div class="card p-3.5 bg-white flex flex-col justify-center">
        <div class="text-xs text-[--color-ink-soft]">فرۆشیار</div>
        <div class="text-base font-bold text-slate-900 mt-0.5">{{ $supplier->name }}</div>
        <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
            <span class="num" dir="ltr">{{ $supplier->phone ?? 'بێ مۆبایل' }}</span>
            @if($supplier->address)
                <span>• {{ $supplier->address }}</span>
            @endif
        </div>
    </div>

    {{-- کۆی کڕینەکان --}}
    <div class="card p-3.5 bg-white">
        <div class="text-xs text-[--color-ink-soft]">کۆی گشتی کڕینەکان</div>
        <div class="text-lg font-bold text-slate-900 num mt-0.5">{{ fmt_money($totalPurchases) }}</div>
        <div class="text-xs text-slate-500 mt-1">{{ fmt_num($purchases->count()) }} پسوولەی کڕین</div>
    </div>

    {{-- کۆی پارەی دراو --}}
    <div class="card p-3.5 bg-white">
        <div class="text-xs text-[--color-ink-soft]">کۆی پارەی دراو (واصلکراو)</div>
        <div class="text-lg font-bold text-emerald-700 num mt-0.5">{{ fmt_money($totalPaid) }}</div>
        <div class="text-xs text-slate-500 mt-1">{{ fmt_num($payments->count()) }} جار پارەدان</div>
    </div>

    {{-- قەرزی ماوە --}}
    <div class="card p-3.5 bg-white {{ $currentBalance > 0 ? 'border-rose-200 bg-rose-50/20' : '' }}">
        <div class="text-xs text-[--color-ink-soft]">قەرزی ماوەی سەر کارگە</div>
        <div class="text-xl font-bold num mt-0.5 {{ $currentBalance > 0 ? 'text-[--color-danger]' : ($currentBalance < 0 ? 'text-[--color-brand-700]' : 'text-[--color-ok]') }}">
            {{ fmt_money($currentBalance) }}
        </div>
        <div class="text-xs mt-1 font-medium {{ $currentBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
            {{ $currentBalance > 0 ? 'کارگە ئەم بڕەی قەرزارە' : ($currentBalance < 0 ? 'ئەم فرۆشیارە قەرزاری کارگەیە' : 'حساب پاکە') }}
        </div>
    </div>
</div>

<div x-data="{ activeTab: 'statement' }" class="space-y-4">

    {{-- ناونیشانی تابەکان --}}
    <div class="flex items-center gap-1 border-b border-[--color-line] no-print">
        <button type="button" @click="activeTab = 'statement'"
                :class="activeTab === 'statement' ? 'border-[--color-brand-700] text-[--color-brand-700] font-bold border-b-2 bg-white' : 'text-slate-600 hover:text-slate-900'"
                class="px-4 py-2.5 text-sm transition-colors rounded-t-lg">
            دەفتەری حیسابات (کەشف حیساب)
        </button>

        <button type="button" @click="activeTab = 'purchases'"
                :class="activeTab === 'purchases' ? 'border-[--color-brand-700] text-[--color-brand-700] font-bold border-b-2 bg-white' : 'text-slate-600 hover:text-slate-900'"
                class="px-4 py-2.5 text-sm transition-colors rounded-t-lg">
            پسوولەکانی کڕین و کاڵاکان ({{ $purchases->count() }})
        </button>

        <button type="button" @click="activeTab = 'payments'"
                :class="activeTab === 'payments' ? 'border-[--color-brand-700] text-[--color-brand-700] font-bold border-b-2 bg-white' : 'text-slate-600 hover:text-slate-900'"
                class="px-4 py-2.5 text-sm transition-colors rounded-t-lg">
            پارەدانەکان (حەقدی) ({{ $payments->count() }})
        </button>

        @if($jobs->isNotEmpty())
            <button type="button" @click="activeTab = 'jobs'"
                    :class="activeTab === 'jobs' ? 'border-[--color-brand-700] text-[--color-brand-700] font-bold border-b-2 bg-white' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2.5 text-sm transition-colors rounded-t-lg">
                ئیشی دەرەکی ({{ $jobs->count() }})
            </button>
        @endif
    </div>

    {{-- ── ١. دەفتەری حیسابات (کەشف حیساب) ── --}}
    <div x-show="activeTab === 'statement'" class="card">
        <div class="card-head flex items-center justify-between">
            <span>کەشف حیسابی تەواوی مامەڵەکان</span>
            <span class="text-xs text-[--color-ink-soft]">ڕیزبەندی بەپێی بەروار لەگەڵ باڵانسی ماوە</span>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700">
                        <th class="text-right py-3 px-4">بەروار</th>
                        <th class="text-right py-3 px-4">جۆری مامەڵە</th>
                        <th class="text-right py-3 px-4">وردەکاری و کاڵاکان</th>
                        <th class="num text-right py-3 px-4 text-slate-900">کڕین / قەرز (+)</th>
                        <th class="num text-right py-3 px-4 text-emerald-700">پارەی دراو (-)</th>
                        <th class="num text-right py-3 px-4">باڵانسی ماوە</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($ledger as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- بەروار --}}
                            <td class="text-right py-3 px-4 num text-slate-700 whitespace-nowrap">
                                {{ fmt_date($item->date) }}
                            </td>

                            {{-- جۆری مامەڵە --}}
                            <td class="text-right py-3 px-4 whitespace-nowrap font-medium">
                                @if($item->reference)
                                    <a href="{{ $item->reference }}" class="text-[--color-brand-700] hover:underline">
                                        {{ $item->title }}
                                    </a>
                                @else
                                    <span class="text-slate-800">{{ $item->title }}</span>
                                @endif
                            </td>

                            {{-- وردەکاری --}}
                            <td class="text-right py-3 px-4 text-xs text-slate-600 max-w-md">
                                {{ $item->details }}
                            </td>

                            {{-- قەرز / لەسەر خۆمان --}}
                            <td class="text-right py-3 px-4 num font-semibold text-slate-900">
                                @if($item->amount_due > 0)
                                    {{ fmt_money($item->amount_due) }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- پارەی دراو --}}
                            <td class="text-right py-3 px-4 num font-semibold text-emerald-700">
                                @if($item->amount_paid > 0)
                                    {{ fmt_money($item->amount_paid) }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- باڵانسی ماوە --}}
                            <td class="text-right py-3 px-4 num font-bold {{ $item->running_balance > 0 ? 'text-[--color-danger]' : ($item->running_balance < 0 ? 'text-[--color-brand-700]' : 'text-[--color-ok]') }}">
                                {{ fmt_money($item->running_balance) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ مامەڵەیەک تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold border-t-2 border-slate-300 text-sm">
                        <td colspan="3" class="text-right py-3.5 px-4 font-bold text-slate-900">
                            کۆی گشتی باڵانس
                        </td>
                        <td class="num text-right py-3.5 px-4 text-slate-900 font-bold">
                            {{ fmt_money($totalPurchases) }}
                        </td>
                        <td class="num text-right py-3.5 px-4 text-emerald-700 font-bold">
                            {{ fmt_money($totalPaid) }}
                        </td>
                        <td class="num text-right py-3.5 px-4 font-bold {{ $currentBalance > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($currentBalance) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── ٢. پسوولەکانی کڕین لەگەڵ کاڵاکان ── --}}
    <div x-show="activeTab === 'purchases'" class="card">
        <div class="card-head flex items-center justify-between">
            <span>پسوولەکانی کڕین لەم فرۆشیارە</span>
            <a href="{{ route('purchases.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary !py-1 text-xs no-print">
                + پسوولەی نوێ
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700">
                        <th class="text-right py-3 px-4">ژمارەی پسوولە</th>
                        <th class="text-right py-3 px-4">بەروار</th>
                        <th class="text-right py-3 px-4">کۆگا</th>
                        <th class="text-right py-3 px-4">کاڵا کڕدراوەکان</th>
                        <th class="num text-right py-3 px-4">کۆی گشتی</th>
                        <th class="num text-right py-3 px-4">پارەی دراو</th>
                        <th class="num text-right py-3 px-4">ماوە (قەرز)</th>
                        <th class="text-left py-3 px-4 w-20 no-print"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($purchases as $purchase)
                        @php
                            $paid = $purchase->paidTotal();
                            $remaining = $purchase->remaining();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="text-right py-3 px-4 num font-medium">
                                <a href="{{ route('purchases.show', $purchase) }}" class="text-[--color-brand-700] hover:underline font-bold">
                                    {{ $purchase->invoice_no }}
                                </a>
                            </td>
                            <td class="text-right py-3 px-4 num text-slate-700">
                                {{ fmt_date($purchase->purchase_date) }}
                            </td>
                            <td class="text-right py-3 px-4 text-xs text-slate-600">
                                {{ $purchase->warehouse?->name ?? '—' }}
                            </td>
                            <td class="text-right py-3 px-4 text-xs text-slate-700">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($purchase->items as $pItem)
                                        <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded text-xs border border-slate-200">
                                            <strong>{{ $pItem->item?->name }}</strong>: {{ fmt_qty($pItem->qty) }} {{ $pItem->item?->unit?->name }} × {{ fmt_money($pItem->unit_cost, $purchase->currency) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-right py-3 px-4 num font-bold text-slate-900">
                                {{ fmt_money($purchase->total, $purchase->currency) }}
                            </td>
                            <td class="text-right py-3 px-4 num font-semibold text-emerald-700">
                                {{ fmt_money($paid) }}
                            </td>
                            <td class="text-right py-3 px-4 num font-bold {{ $remaining > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                                {{ fmt_money($remaining) }}
                            </td>
                            <td class="text-left py-3 px-4 no-print">
                                <a href="{{ route('purchases.show', $purchase) }}" class="text-xs text-[--color-brand-700] hover:underline">
                                    بینین
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ پسوولەیەکی کڕین نییە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── ٣. پارەدانەکان (حەقدی) ── --}}
    <div x-show="activeTab === 'payments'" class="card">
        <div class="card-head flex items-center justify-between">
            <span>پارەدانەکان بۆ ئەم فرۆشیارە</span>
            <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn btn-primary !py-1 text-xs no-print">
                + پارەدانی نوێ
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700">
                        <th class="text-right py-3 px-4">ژمارەی سەنەد</th>
                        <th class="text-right py-3 px-4">بەروار</th>
                        <th class="num text-right py-3 px-4">بڕی پارە</th>
                        <th class="text-right py-3 px-4">تێبینی</th>
                        <th class="text-right py-3 px-4">تۆمارکەر</th>
                        <th class="text-left py-3 px-4 w-20 no-print"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="text-right py-3 px-4 num font-medium text-slate-900">
                                {{ $payment->voucher_no }}
                            </td>
                            <td class="text-right py-3 px-4 num text-slate-700">
                                {{ fmt_date($payment->paid_at) }}
                            </td>
                            <td class="text-right py-3 px-4 num font-bold text-emerald-700">
                                {{ fmt_money($payment->amount, $payment->currency) }}
                            </td>
                            <td class="text-right py-3 px-4 text-xs text-slate-600">
                                {{ $payment->note ?? '—' }}
                            </td>
                            <td class="text-right py-3 px-4 text-xs text-slate-500">
                                {{ $payment->user?->name ?? '—' }}
                            </td>
                            <td class="text-left py-3 px-4 no-print">
                                <a href="{{ route('payments.print', $payment) }}" class="text-xs text-[--color-brand-700] hover:underline" target="_blank">
                                    چاپ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ پارەدانێک تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── ٤. ئیشی دەرەکی (External Jobs) ── --}}
    @if ($jobs->isNotEmpty())
        <div x-show="activeTab === 'jobs'" class="card">
            <div class="card-head">ئیشی خاریجی</div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-slate-50/80 text-xs text-slate-700">
                            <th class="text-right py-3 px-4">ژمارە</th>
                            <th class="text-right py-3 px-4">ناونیشان</th>
                            <th class="num text-right py-3 px-4">تێچوو</th>
                            <th class="text-right py-3 px-4">دۆخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach ($jobs as $job)
                            <tr>
                                <td class="text-right py-3 px-4 num font-medium">{{ $job->job_no }}</td>
                                <td class="text-right py-3 px-4">{{ $job->title }}</td>
                                <td class="text-right py-3 px-4 num font-bold text-slate-900">{{ fmt_money($job->cost, $job->currency) }}</td>
                                <td class="text-right py-3 px-4 text-xs">{{ $job->status_label }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

@endsection
