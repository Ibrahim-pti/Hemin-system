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

@if (session('just_created'))
    <div x-data="{ open: true }" x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 text-center"
             @click.away="open = false"
             x-transition.scale>
            <div class="size-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-3xl font-bold">
                ✓
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-1">وەسڵی ژمارە {{ $order->invoice_no }} تۆمارکرا</h3>
            <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                ئایا دەتەوێت حەقدی (پێشەکی) چاپ بکەیت یان وەسڵ؟
            </p>

            <div class="space-y-2.5 mb-5">
                @if (session('payment_id'))
                    <a href="{{ route('payments.print', session('payment_id')) }}" target="_blank"
                       @click="open = false"
                       class="btn !py-3 !px-4 w-full flex items-center justify-center gap-2 font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-transform active:scale-95">
                        <span class="text-lg">🧾</span>
                        <span>چاپی حەقدی (پێشەکی)</span>
                    </a>
                @endif
                <a href="{{ route('orders.print', $order) }}" target="_blank"
                   @click="open = false"
                   class="btn !py-3 !px-4 w-full flex items-center justify-center gap-2 font-bold text-sm bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-transform active:scale-95">
                    <span class="text-lg">📄</span>
                    <span>چاپی وەسڵ</span>
                </a>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                <a href="{{ route('orders.create') }}" class="text-blue-600 font-bold hover:underline flex items-center gap-1">
                    <span>+</span>
                    <span>وەسڵی نوێ</span>
                </a>
                <button type="button" @click="open = false" class="text-slate-500 hover:text-slate-700 font-semibold px-2 py-1 rounded hover:bg-slate-100">
                    داخستن و مانەوە لێرە
                </button>
            </div>
        </div>
    </div>
@endif

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
                                <div class="flex items-center gap-3">
                                    @if ($line->imageUrl())
                                        <img src="{{ $line->imageUrl() }}"
                                             class="size-10 rounded-lg object-cover border border-slate-200 cursor-pointer hover:scale-125 transition-transform shrink-0"
                                             onclick="window.open(this.src, '_blank')"
                                             title="کرتە بکە بۆ بینینی تەواوی وێنە">
                                    @endif
                                    <div>
                                        <span class="font-medium">{{ $line->description }}</span>
                                        @if ($line->note)
                                            <span class="block text-xs text-[--color-ink-soft]">{{ $line->note }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="num text-[--color-ink-soft]">
                                @if ($line->has_meter)
                                    {{ fmt_qty($line->meter) }} مەتر
                                @else
                                    {{ $line->measurement_label }}
                                @endif
                            </td>
                            <td class="num">{{ fmt_qty($line->qty) }}</td>
                            <td class="num">
                                @if ($line->has_meter)
                                    {{ fmt_qty($line->meter) }} م
                                @else
                                    {{ fmt_qty($line->computed_qty) }} {{ $line->mode_unit }}
                                @endif
                            </td>
                            <td class="num">{{ fmt_money($line->has_meter ? $line->meter_price : $line->unit_price, $order->currency) }}</td>
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
                @if($order->delivery_date)
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">گەیاندن</span>
                    <span class="num">{{ fmt_date($order->delivery_date) }}</span>
                </div>
                @endif
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
