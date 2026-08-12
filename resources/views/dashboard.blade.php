@extends('layouts.app')
@section('title', 'داشبۆرد')

@section('content')

{{-- رەنگ تەنها بۆ مانای دۆخە (قەرز = سوور، ئاگاداری = پرتەقاڵی)، نەک بۆ جوانی. --}}
@can('view_reports')
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @include('partials.stat-tile', [
            'label' => 'فرۆشتنی ئەمڕۆ', 'value' => fmt_money($todaySales), 'tone' => null, 'icon' => 'orders',
        ])
        @include('partials.stat-tile', [
            'label' => 'وەرگیراوی ئەمڕۆ', 'value' => fmt_money($todayIn), 'tone' => 'ok', 'icon' => 'payments',
        ])
        @include('partials.stat-tile', [
            'label' => 'قەرزی کڕیاران', 'value' => fmt_money($receivables),
            'tone' => $receivables > 0 ? 'danger' : null, 'icon' => 'debts',
        ])
        @include('partials.stat-tile', [
            'label' => 'قەرزی کارگە', 'value' => fmt_money($payables),
            'tone' => $payables > 0 ? 'warn' : null, 'icon' => 'suppliers',
        ])
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">

        {{-- قاسەکان --}}
        <div class="card">
            <div class="card-head">قاسە</div>
            <div class="card-body space-y-3">
                @foreach ($cashBoxes as $box)
                    <div class="flex items-center justify-between border-b border-[--color-line] pb-3 last:border-0 last:pb-0">
                        <span class="text-sm">{{ $box->name }}</span>
                        <span class="num font-semibold">{{ fmt_money($box->balance(), $box->currency) }}</span>
                    </div>
                @endforeach

                <div class="flex items-center justify-between pt-1 text-sm text-[--color-ink-soft]">
                    <span>خەرجی ئەمڕۆ</span>
                    <span class="num">{{ fmt_money($todayOut) }}</span>
                </div>

                <a href="{{ route('cash.index') }}" class="btn btn-ghost w-full">حیسابی ڕۆژانە</a>
            </div>
        </div>

        {{-- کاری کراوە --}}
        <div class="card">
            <div class="card-head">کاری بەردەوام</div>
            <div class="card-body space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span>داواکاری کراوە</span>
                    <span class="num font-semibold">{{ fmt_num($openOrders) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>وەسڵی ئەمڕۆ</span>
                    <span class="num font-semibold">{{ fmt_num($todayOrders) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>کارمەندی ئامادە</span>
                    <span class="num font-semibold">{{ fmt_num($presentToday) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>جوڵەی مەخزەنی ئەمڕۆ</span>
                    <span class="num font-semibold">{{ fmt_num($todayMovements) }}</span>
                </div>
            </div>
        </div>

        {{-- دوایین وەسڵەکان --}}
        <div class="card">
            <div class="card-head">دوایین وەسڵەکان</div>
            <div class="card-body !p-0">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('orders.show', $order) }}"
                       class="flex items-center justify-between border-b border-[--color-line] px-4 py-3 last:border-0 hover:bg-[--color-canvas]">
                        <div class="min-w-0">
                            <div class="truncate text-sm">{{ $order->customer->name }}</div>
                            <div class="num text-xs text-[--color-ink-soft]">ژ. {{ $order->invoice_no }}</div>
                        </div>
                        <span class="num text-sm font-medium">{{ fmt_money($order->total_iqd) }}</span>
                    </a>
                @empty
                    <p class="p-4 text-sm text-[--color-ink-soft]">هێشتا هیچ وەسڵێک نییە.</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    {{-- ڕووی بەرپرسی کۆگا — بێ هیچ نرخێک --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        @include('partials.stat-tile', [
            'label' => 'کۆی کاڵا', 'value' => fmt_num($itemsCount), 'tone' => null, 'icon' => 'items',
        ])
        @include('partials.stat-tile', [
            'label' => 'جوڵەی ئەمڕۆ', 'value' => fmt_num($todayMovements), 'tone' => null, 'icon' => 'stock',
        ])
        @include('partials.stat-tile', [
            'label' => 'کاڵای کەم', 'value' => fmt_num($lowStock->count()),
            'tone' => $lowStock->count() ? 'warn' : null, 'icon' => 'counts',
        ])
    </div>
@endcan

{{-- ئاگاداری کەمی مەخزەن — بۆ هەردوو ڕۆڵ --}}
<div class="card mt-4">
    <div class="card-head flex items-center justify-between">
        <span>ئاگاداری کەمی مەخزەن</span>
        @if ($lowStock->isNotEmpty())
            <span class="badge badge-warn">{{ fmt_num($lowStock->count()) }} کاڵا</span>
        @endif
    </div>

    @if ($lowStock->isEmpty())
        <div class="card-body text-sm text-[--color-ink-soft]">
            هیچ کاڵایەک لە سنووری ئاگاداری کەمتر نەبووەتەوە.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>کاڵا</th>
                        <th>کۆد</th>
                        <th class="num">ماوە</th>
                        <th class="num">سنووری کەمترین</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStock as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="num text-[--color-ink-soft]">{{ $item->code }}</td>
                            <td class="num font-medium text-[--color-warn]">
                                {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                            </td>
                            <td class="num text-[--color-ink-soft]">{{ fmt_qty($item->min_qty) }}</td>
                            <td class="text-left">
                                <a href="{{ route('items.show', $item) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
