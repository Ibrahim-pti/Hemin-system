@extends('layouts.app')
@section('title', $title)

@section('content')

<div class="mb-4 flex flex-wrap gap-2 no-print">
    <button onclick="window.print()" class="btn btn-ghost">چاپ</button>
    <a href="{{ route('reports.index') }}" class="btn btn-ghost">هەموو راپۆرتەکان</a>
</div>

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'ژمارەی کاڵا', 'value' => fmt_num($items->count()), 'tone' => null])
    @include('partials.stat-tile', ['label' => 'نرخی کۆگا', 'value' => fmt_money($stockValue), 'tone' => null])
    @include('partials.stat-tile', [
        'label' => 'کاڵای کەم',
        'value' => fmt_num($lowCount),
        'tone' => $lowCount ? 'warn' : null,
    ])
</div>

<div class="card">
    <div class="card-head">باڵانسی مەخزەن</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>کاڵا</th><th>کۆد</th><th>جۆر</th>
                    <th class="num">بڕ</th><th class="num">کەمترین</th>
                    <th class="num">نرخی یەکە</th><th class="num">نرخی گشتی</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $cost = (float) $item->last_cost;
                        if ($item->cost_currency === 'USD') {
                            $cost *= \App\Models\ExchangeRate::current();
                        }
                    @endphp
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="num text-[--color-ink-soft]">{{ $item->code }}</td>
                        <td class="text-[--color-ink-soft]">{{ $item->category?->name ?? '—' }}</td>
                        <td class="num font-medium {{ $item->is_low ? 'text-[--color-warn]' : '' }}">
                            {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                        </td>
                        <td class="num text-[--color-ink-soft]">{{ fmt_qty($item->min_qty) }}</td>
                        <td class="num">{{ $cost > 0 ? fmt_money($cost) : '—' }}</td>
                        <td class="num font-medium">{{ fmt_money($item->stock_qty * $cost) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[--color-canvas] font-semibold">
                    <td colspan="6">کۆی نرخی کۆگا</td>
                    <td class="num">{{ fmt_money($stockValue) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
