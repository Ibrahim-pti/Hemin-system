@extends('layouts.app')
@section('title', 'پسوولەی کڕین ' . $purchase->invoice_no)

@section('actions')
    @if ($purchase->status === 'draft')
        <button type="submit" form="confirm-purchase" class="btn btn-primary">پەسەندکردن</button>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-ghost">دەستکاری</a>
    @else
        <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $purchase->supplier_id, 'purchase' => $purchase->id]) }}"
           class="btn btn-primary">حەقدی</a>
    @endif
@endsection

@section('content')

<div class="grid gap-4 lg:grid-cols-4">
    <div class="card lg:col-span-3">
        <div class="card-head flex items-center justify-between">
            <span>کاڵاکان</span>
            <span class="badge {{ $purchase->status === 'confirmed' ? 'badge-ok' : 'badge-warn' }}">
                {{ \App\Models\Purchase::STATUSES[$purchase->status] }}
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>کاڵا</th>
                        <th class="num">بڕ</th>
                        <th class="num">نرخی یەکە</th>
                        <th class="num">کۆ</th>
                        <th>تێبینی</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $line)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if ($line->imageUrl())
                                        <img src="{{ $line->imageUrl() }}"
                                             class="size-8 rounded-lg object-cover border border-slate-200 cursor-pointer hover:scale-110 transition-transform"
                                             onclick="window.open(this.src, '_blank')"
                                             title="کرتە بکە بۆ بینینی تەواوی وێنە">
                                    @endif
                                    <span class="font-medium">{{ $line->item?->name }}</span>
                                </div>
                            </td>
                            <td class="num">{{ fmt_qty($line->qty) }} {{ $line->item?->unit?->name }}</td>
                            <td class="num">{{ fmt_money($line->unit_price, $purchase->currency) }}</td>
                            <td class="num font-medium">{{ fmt_money($line->line_total, $purchase->currency) }}</td>
                            <td class="text-[--color-ink-soft]">{{ $line->note ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">فرۆشیار</span>
                    <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="text-[--color-brand-700]">
                        {{ $purchase->supplier?->name }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">بەروار</span>
                    <span class="num">{{ fmt_date($purchase->purchase_date) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">کۆگا</span>
                    <span>{{ $purchase->warehouse?->name }}</span>
                </div>
                @if ($purchase->currency === 'USD')
                    <div class="flex justify-between">
                        <span class="text-[--color-ink-soft]">نرخی دۆلار</span>
                        <span class="num">{{ fmt_num($purchase->exchange_rate) }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">کۆی دێڕەکان</span>
                    <span class="num">{{ fmt_money($purchase->subtotal, $purchase->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">داشکاندن</span>
                    <span class="num">{{ fmt_money($purchase->discount_amount, $purchase->currency) }}</span>
                </div>
                <div class="flex justify-between border-t border-[--color-line] pt-2 font-semibold">
                    <span>کۆی گشتی</span>
                    <span class="num">{{ fmt_money($purchase->total, $purchase->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">دراوە</span>
                    <span class="num text-[--color-ok]">{{ fmt_money($purchase->paidTotal()) }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>ماوە</span>
                    <span class="num {{ $purchase->remaining() > 0 ? 'text-[--color-danger]' : '' }}">
                        {{ fmt_money($purchase->remaining()) }}
                    </span>
                </div>
            </div>
        </div>

        @if ($purchase->status === 'confirmed')
            <form method="POST" action="{{ route('purchases.unconfirm', $purchase) }}"
                  onsubmit="return confirm('جوڵەکانی مەخزەن دەسڕدرێنەوە. بەردەوام بم؟')">
                @csrf
                <button class="btn btn-ghost w-full !text-[--color-danger]">هەڵوەشاندنەوەی پەسەندکردن</button>
            </form>
        @endif
    </div>
</div>

{{-- حەقدییەکان --}}
@if ($purchase->payments->isNotEmpty())
    <div class="card mt-4">
        <div class="card-head">حەقدییەکان</div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>ژمارە</th><th>بەروار</th><th class="num">بڕ</th><th></th></tr></thead>
                <tbody>
                    @foreach ($purchase->payments as $payment)
                        <tr>
                            <td class="num">{{ $payment->voucher_no }}</td>
                            <td class="num">{{ fmt_date($payment->paid_at) }}</td>
                            <td class="num">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                            <td class="text-left">
                                <a href="{{ route('payments.print', $payment) }}" class="text-sm text-[--color-brand-700]">چاپ</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($purchase->status === 'draft')
    <form id="confirm-purchase" method="POST" action="{{ route('purchases.confirm', $purchase) }}" class="hidden">
        @csrf
    </form>
@endif

@endsection
