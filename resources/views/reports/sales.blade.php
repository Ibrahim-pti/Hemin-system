@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی فرۆشتن', 'value' => fmt_money($total), 'tone' => null])
    @include('partials.stat-tile', ['label' => 'وەرگیراو', 'value' => fmt_money($paid), 'tone' => 'ok'])
    @include('partials.stat-tile', ['label' => 'ماوە', 'value' => fmt_money($total - $paid), 'tone' => 'danger'])
    @include('partials.stat-tile', ['label' => 'ژمارەی وەسڵ', 'value' => fmt_num($orders->count()), 'tone' => null])
</div>

<div class="card">
    <div class="card-head">فرۆشتن بەپێی کڕیار</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>کڕیار</th><th class="num">ژمارەی وەسڵ</th><th class="num">کۆی فرۆشتن</th></tr></thead>
            <tbody>
                @forelse ($byCustomer as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">{{ fmt_num($row['count']) }}</td>
                        <td class="num font-medium">{{ fmt_money($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشتنێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head">وەسڵەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>ژمارە</th><th>بەروار</th><th>بەڕێز</th><th class="num">کۆی گشتی</th><th class="num">ماوە</th><th>دۆخ</th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td class="num font-medium">
                            <a href="{{ route('orders.show', $order) }}" class="text-[--color-brand-700]">{{ $order->invoice_no }}</a>
                        </td>
                        <td class="num whitespace-nowrap">{{ fmt_date($order->order_date) }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td class="num">{{ fmt_money($order->total_iqd) }}</td>
                        <td class="num {{ $order->remaining() > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($order->remaining()) }}
                        </td>
                        <td>{{ $order->status_label }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[--color-canvas] font-semibold">
                    <td colspan="3">کۆی گشتی</td>
                    <td class="num">{{ fmt_money($total) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
