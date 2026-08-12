@extends('layouts.app')
@section('title', $supplier->name)

@section('actions')
    <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn btn-primary">
        حەقدی (پارەدان)
    </a>
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost">دەستکاری</a>
@endsection

@section('content')

@php $balance = $supplier->balance(); @endphp

<div class="grid gap-4 lg:grid-cols-3">

    <div class="card">
        <div class="card-head">زانیاری</div>
        <div class="card-body space-y-2 text-sm">
            @foreach ([
                'تەلەفۆن' => $supplier->phone ?? '—',
                'تەلەفۆنی دووەم' => $supplier->phone2 ?? '—',
                'شوێن' => $supplier->address ?? '—',
                'باڵانسی سەرەتایی' => fmt_money($supplier->opening_balance, $supplier->opening_currency),
            ] as $label => $value)
                <div class="flex justify-between border-b border-[--color-line] pb-2 last:border-0">
                    <span class="text-[--color-ink-soft]">{{ $label }}</span>
                    <span class="num" dir="auto">{{ $value }}</span>
                </div>
            @endforeach

            @if ($supplier->note)
                <p class="pt-2 text-[--color-ink-soft]">{{ $supplier->note }}</p>
            @endif
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-head">باڵانسی ئێستا</div>
        <div class="card-body">
            <div class="num text-3xl font-semibold {{ $balance > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                {{ fmt_money($balance) }}
            </div>
            <p class="mt-1 text-sm text-[--color-ink-soft]">
                {{ $balance > 0 ? 'کارگە ئەم بڕەی قەرزارە.' : ($balance < 0 ? 'ئەم فرۆشیارە قەرزاری کارگەیە.' : 'حساب پاکە.') }}
            </p>
        </div>
    </div>
</div>

{{-- پسوولەکانی کڕین --}}
<div class="card mt-4">
    <div class="card-head">پسوولەکانی کڕین</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>ژمارە</th><th>بەروار</th><th class="num">کۆی گشتی</th><th>دۆخ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td class="num font-medium">{{ $purchase->invoice_no }}</td>
                        <td class="num">{{ fmt_date($purchase->purchase_date) }}</td>
                        <td class="num">{{ fmt_money($purchase->total, $purchase->currency) }}</td>
                        <td>
                            <span class="badge {{ $purchase->status === 'confirmed' ? 'badge-ok' : 'badge-warn' }}">
                                {{ \App\Models\Purchase::STATUSES[$purchase->status] }}
                            </span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ پسوولەیەک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- حەقدییەکان --}}
<div class="card mt-4">
    <div class="card-head">حەقدییەکان (پارەدان)</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>ژمارە</th><th>بەروار</th><th class="num">بڕ</th><th>تێبینی</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="num font-medium">{{ $payment->voucher_no }}</td>
                        <td class="num">{{ fmt_date($payment->paid_at) }}</td>
                        <td class="num">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                        <td class="text-[--color-ink-soft]">{{ $payment->note ?? '—' }}</td>
                        <td class="text-left">
                            <a href="{{ route('payments.print', $payment) }}" class="text-sm text-[--color-brand-700]">چاپ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ حەقدییەک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($jobs->isNotEmpty())
    <div class="card mt-4">
        <div class="card-head">ئیشی خاریجی</div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>ژمارە</th><th>ناونیشان</th><th class="num">تێچوو</th><th>دۆخ</th></tr></thead>
                <tbody>
                    @foreach ($jobs as $job)
                        <tr>
                            <td class="num">{{ $job->job_no }}</td>
                            <td>{{ $job->title }}</td>
                            <td class="num">{{ fmt_money($job->cost, $job->currency) }}</td>
                            <td>{{ $job->status_label }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
