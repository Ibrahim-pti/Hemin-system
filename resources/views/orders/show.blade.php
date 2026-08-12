@extends('layouts.app')
@section('title', 'وەسڵی ژمارە ' . $order->invoice_no)

@section('actions')
    <a href="{{ route('orders.print', $order) }}" target="_blank" class="btn btn-ghost">چاپ</a>
    @if (! in_array($order->status, ['delivered', 'cancelled']))
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-ghost">دەستکاری</a>
    @endif
    <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $order->customer_id, 'order' => $order->id]) }}"
       class="btn btn-primary">حەقدی</a>
@endsection

@section('content')

<div class="grid gap-4 lg:grid-cols-4">

    {{-- دێڕەکان --}}
    <div class="card lg:col-span-3">
        <div class="card-head">ناوەڕۆک و قیاس</div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>ناوەڕۆک</th>
                        <th>قیاس</th>
                        <th class="num">ژمارە</th>
                        <th class="num">بڕ</th>
                        <th class="num">نرخ</th>
                        <th class="num">بڕی پارە</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $line)
                        <tr>
                            <td>
                                {{ $line->description }}
                                @if ($line->note)
                                    <span class="block text-xs text-[--color-ink-soft]">{{ $line->note }}</span>
                                @endif
                            </td>
                            <td class="num text-[--color-ink-soft]">{{ $line->measurement_label }}</td>
                            <td class="num">{{ fmt_qty($line->qty) }}</td>
                            <td class="num">{{ fmt_qty($line->computed_qty) }} {{ $line->mode_unit }}</td>
                            <td class="num">{{ fmt_money($line->unit_price, $order->currency) }}</td>
                            <td class="num font-medium">{{ fmt_money($line->line_total, $order->currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        {{-- کڕیار --}}
        <div class="card">
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">بەڕێز</span>
                    <a href="{{ route('customers.show', $order->customer) }}" class="text-[--color-brand-700]">
                        {{ $order->customer?->name }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">تەلەفۆن</span>
                    <span class="num" dir="ltr">{{ $order->customer->phone ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">ناونیشان</span>
                    <span>{{ $order->address_snapshot ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">بەروار</span>
                    <span class="num">{{ fmt_date($order->order_date) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">گەیاندن</span>
                    <span class="num">{{ fmt_date($order->delivery_date) }}</span>
                </div>
            </div>
        </div>

        {{-- کۆکان --}}
        <div class="card">
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">کۆی دێڕەکان</span>
                    <span class="num">{{ fmt_money($order->subtotal, $order->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">داشکاندن</span>
                    <span class="num">{{ fmt_money($order->discount_amount, $order->currency) }}</span>
                </div>
                <div class="flex justify-between border-t border-[--color-line] pt-2 text-base font-semibold">
                    <span>کۆی گشتی</span>
                    <span class="num">{{ fmt_money($order->total, $order->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">دراوە</span>
                    <span class="num text-[--color-ok]">{{ fmt_money($order->paidAmount()) }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>ماوە</span>
                    <span class="num {{ $order->remaining() > 0 ? 'text-[--color-danger]' : '' }}">
                        {{ fmt_money($order->remaining()) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- گۆڕینی دۆخ --}}
        <div class="card">
            <div class="card-head">دۆخی کار</div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.status', $order) }}" class="flex gap-2">
                    @csrf
                    <select name="status" class="field">
                        @foreach (\App\Models\Order::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary">گۆڕین</button>
                </form>
                <p class="mt-2 text-xs text-[--color-ink-soft]">
                    کاتێک دەبێتە «گەیەنراوە»، ئەو دێڕانەی کاڵای مەخزەنیان پێوە بەستراوە کەم دەکرێنەوە.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- حەقدییەکان --}}
<div class="card mt-4">
    <div class="card-head">حەقدییەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>ژمارە</th><th>بەروار</th><th>جۆر</th><th class="num">بڕ</th><th>تێبینی</th><th></th></tr></thead>
            <tbody>
                @forelse ($order->payments as $payment)
                    <tr>
                        <td class="num font-medium">{{ $payment->voucher_no }}</td>
                        <td class="num">{{ fmt_date($payment->paid_at) }}</td>
                        <td>{{ $payment->direction_label }}</td>
                        <td class="num">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                        <td class="text-[--color-ink-soft]">{{ $payment->note ?? '—' }}</td>
                        <td class="text-left">
                            <a href="{{ route('payments.print', $payment) }}" target="_blank"
                               class="text-sm text-[--color-brand-700]">چاپ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-sm text-[--color-ink-soft]">هێشتا هیچ حەقدییەک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ئیشی خاریجی --}}
@if ($order->externalJobs->isNotEmpty())
    <div class="card mt-4">
        <div class="card-head">ئیشی خاریجی ئەم وەسڵە</div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>ژمارە</th><th>ناونیشان</th><th>کرێکار</th><th class="num">تێچوو</th><th>دۆخ</th></tr></thead>
                <tbody>
                    @foreach ($order->externalJobs as $job)
                        <tr>
                            <td class="num">{{ $job->job_no }}</td>
                            <td>{{ $job->title }}</td>
                            <td>{{ $job->contractor_label }}</td>
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
