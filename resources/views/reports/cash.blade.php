@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی داهات', 'value' => fmt_money($totalIn), 'tone' => 'ok'])
    @include('partials.stat-tile', ['label' => 'کۆی خەرجی', 'value' => fmt_money($totalOut), 'tone' => 'danger'])
    @include('partials.stat-tile', ['label' => 'جیاوازی', 'value' => fmt_money($totalIn - $totalOut), 'tone' => null])
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <div class="card">
        <div class="card-head">باڵانسی ئێستای قاسەکان</div>
        <div class="card-body space-y-3 text-sm">
            @foreach ($boxes as $box)
                <div class="flex justify-between border-b border-[--color-line] pb-2 last:border-0">
                    <span>{{ $box->name }}</span>
                    <span class="num font-semibold">{{ fmt_money($box->balance(), $box->currency) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-head">داهات و خەرجی بەپێی بابەت</div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>بابەت</th><th class="num">داهات</th><th class="num">خەرجی</th><th class="num">جیاوازی</th></tr></thead>
                <tbody>
                    @forelse ($byCategory as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="num text-[--color-ok]">{{ $row['in'] > 0 ? fmt_money($row['in']) : '—' }}</td>
                            <td class="num text-[--color-danger]">{{ $row['out'] > 0 ? fmt_money($row['out']) : '—' }}</td>
                            <td class="num font-medium">{{ fmt_money($row['in'] - $row['out']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-sm text-[--color-ink-soft]">لەم ماوەیەدا جوڵە نییە.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-[--color-canvas] font-semibold">
                        <td>کۆی گشتی</td>
                        <td class="num">{{ fmt_money($totalIn) }}</td>
                        <td class="num">{{ fmt_money($totalOut) }}</td>
                        <td class="num">{{ fmt_money($totalIn - $totalOut) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
