@extends('layouts.app')
@section('title', 'قەرزەکان')

@section('content')

<div class="mb-4 grid grid-cols-2 gap-3">
    @include('partials.stat-tile', ['label' => 'کڕیاران قەرزارن', 'value' => fmt_money($totalReceivable), 'tone' => 'danger'])
    @include('partials.stat-tile', ['label' => 'کارگە قەرزارە', 'value' => fmt_money($totalPayable), 'tone' => 'warn'])
</div>

{{-- وەسڵە نەدراوەکان --}}
<div class="card">
    <div class="card-head">وەسڵە نەدراوەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ژمارە</th><th>بەروار</th><th>بەڕێز</th><th>تەلەفۆن</th>
                    <th class="num">کۆی گشتی</th><th class="num">ماوە</th><th class="num">ڕۆژ</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($openOrders as $row)
                    @php
                        $order = $row['order'];
                        $days = (int) $order->order_date->diffInDays(now());
                    @endphp
                    <tr>
                        <td class="num font-medium">{{ $order->invoice_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($order->order_date) }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td class="num" dir="ltr">{{ $order->customer->phone ?? '—' }}</td>
                        <td class="num">{{ fmt_money($order->total, $order->currency) }}</td>
                        <td class="num font-medium text-[--color-danger]">{{ fmt_money($row['remaining']) }}</td>
                        <td class="num">
                            <span class="badge {{ $days > 60 ? 'badge-danger' : ($days > 30 ? 'badge-warn' : 'badge-ok') }}">
                                {{ fmt_num($days) }}
                            </span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $order->customer_id, 'order' => $order->id]) }}"
                               class="text-sm text-[--color-brand-700]">حەقدی</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ وەسڵێکی نەدراو نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- کڕیاران --}}
<div class="card mt-4">
    <div class="card-head">باڵانسی کڕیاران</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>ناو</th><th>تەلەفۆن</th><th>شوێن</th><th class="num">باڵانس</th><th></th></tr></thead>
            <tbody>
                @forelse ($customers as $row)
                    @php $customer = $row['model']; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="font-medium text-[--color-brand-700]">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td class="num" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $customer->address ?? '—' }}</td>
                        <td class="num font-medium {{ $row['balance'] > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($row['balance']) }}
                        </td>
                        <td class="text-left">
                            <a href="{{ route('customers.statement', $customer) }}" class="text-sm text-[--color-brand-700]">کەشف حساب</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ قەرزێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- فرۆشیاران --}}
<div class="card mt-4">
    <div class="card-head">باڵانسی فرۆشیاران</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>ناو</th><th>تەلەفۆن</th><th class="num">باڵانس</th><th></th></tr></thead>
            <tbody>
                @forelse ($suppliers as $row)
                    @php $supplier = $row['model']; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-[--color-brand-700]">
                                {{ $supplier->name }}
                            </a>
                        </td>
                        <td class="num" dir="ltr">{{ $supplier->phone ?? '—' }}</td>
                        <td class="num font-medium {{ $row['balance'] > 0 ? 'text-[--color-warn]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($row['balance']) }}
                        </td>
                        <td class="text-left">
                            <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}"
                               class="text-sm text-[--color-brand-700]">حەقدی</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ قەرزێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
