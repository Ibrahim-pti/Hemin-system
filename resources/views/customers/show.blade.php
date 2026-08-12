@extends('layouts.app')
@section('title', $customer->name)

@section('actions')
    <a href="{{ route('orders.create', ['customer' => $customer->id]) }}" class="btn btn-primary">وەسڵی نوێ</a>
    <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $customer->id]) }}" class="btn btn-ghost">حەقدی</a>
    <a href="{{ route('customers.statement', $customer) }}" class="btn btn-ghost">کەشف حساب</a>
@endsection

@section('content')

@php $balance = $customer->balance(); @endphp

<div class="grid gap-4 lg:grid-cols-3">
    <div class="card">
        <div class="card-head">زانیاری</div>
        <div class="card-body space-y-2 text-sm">
            @foreach ([
                'تەلەفۆن' => $customer->phone ?? '—',
                'تەلەفۆنی دووەم' => $customer->phone2 ?? '—',
                'شوێن' => $customer->address ?? '—',
                'داشکاندن' => $customer->discount_percent > 0 ? fmt_num($customer->discount_percent, 2).'٪' : '—',
            ] as $label => $value)
                <div class="flex justify-between border-b border-[--color-line] pb-2 last:border-0">
                    <span class="text-[--color-ink-soft]">{{ $label }}</span>
                    <span class="num" dir="auto">{{ $value }}</span>
                </div>
            @endforeach

            @if ($customer->note)
                <p class="pt-2 text-[--color-ink-soft]">{{ $customer->note }}</p>
            @endif

            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost mt-2 w-full">دەستکاری</a>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-head">قەرزی ئێستا</div>
        <div class="card-body">
            <div class="num text-3xl font-semibold {{ $balance > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                {{ fmt_money($balance) }}
            </div>
            <p class="mt-1 text-sm text-[--color-ink-soft]">
                {{ $balance > 0 ? 'ئەم کڕیارە قەرزاری کارگەیە.' : ($balance < 0 ? 'کارگە قەرزاری ئەم کڕیارەیە.' : 'حساب پاکە.') }}
            </p>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head">وەسڵەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>ژمارە</th><th>بەروار</th><th class="num">کۆی گشتی</th><th class="num">ماوە</th><th>دۆخ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="num font-medium">{{ $order->invoice_no }}</td>
                        <td class="num">{{ fmt_date($order->order_date) }}</td>
                        <td class="num">{{ fmt_money($order->total, $order->currency) }}</td>
                        <td class="num {{ $order->remaining() > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($order->remaining()) }}
                        </td>
                        <td><span class="badge badge-warn">{{ $order->status_label }}</span></td>
                        <td class="text-left">
                            <a href="{{ route('orders.show', $order) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ وەسڵێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head">حەقدییەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>ژمارە</th><th>بەروار</th><th>جۆر</th><th class="num">بڕ</th><th></th></tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="num font-medium">{{ $payment->voucher_no }}</td>
                        <td class="num">{{ fmt_date($payment->paid_at) }}</td>
                        <td>{{ $payment->direction_label }}</td>
                        <td class="num">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                        <td class="text-left">
                            <a href="{{ route('payments.print', $payment) }}" target="_blank"
                               class="text-sm text-[--color-brand-700]">چاپ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ حەقدییەک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
