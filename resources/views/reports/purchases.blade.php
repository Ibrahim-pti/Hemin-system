@extends('layouts.app')
@section('title', $title)

@section('content')

@include('reports._filter')

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی کڕین', 'value' => fmt_money($total), 'tone' => null])
    @include('partials.stat-tile', ['label' => 'ژمارەی پسوولە', 'value' => fmt_num($purchases->count()), 'tone' => null])
</div>

<div class="card">
    <div class="card-head">کڕین بەپێی فرۆشیار</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>فرۆشیار</th><th class="num">ژمارەی پسوولە</th><th class="num">کۆی کڕین</th></tr></thead>
            <tbody>
                @forelse ($bySupplier as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">{{ fmt_num($row['count']) }}</td>
                        <td class="num font-medium">{{ fmt_money($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-sm text-[--color-ink-soft]">هیچ کڕینێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head">پسوولەکان</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>ژمارە</th><th>بەروار</th><th>فرۆشیار</th><th class="num">کۆی گشتی</th><th class="num">ماوە</th></tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td class="num font-medium">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-[--color-brand-700]">
                                {{ $purchase->invoice_no }}
                            </a>
                        </td>
                        <td class="num whitespace-nowrap">{{ fmt_date($purchase->purchase_date) }}</td>
                        <td>{{ $purchase->supplier?->name }}</td>
                        <td class="num">{{ fmt_money($purchase->total_iqd) }}</td>
                        <td class="num {{ $purchase->remaining() > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($purchase->remaining()) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[--color-surface-soft] font-semibold">
                    <td colspan="3">کۆی گشتی</td>
                    <td class="num">{{ fmt_money($total) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
