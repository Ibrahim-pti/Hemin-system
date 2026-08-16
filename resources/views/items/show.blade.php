@extends('layouts.app')
@section('title', $item->name)

@section('actions')
    @can('manage_stock')
        <a href="{{ route('stock.create', ['item' => $item->id]) }}" class="btn btn-primary">جوڵەی نوێ</a>
    @endcan
@endsection

@section('content')

<div class="grid gap-4 lg:grid-cols-3">

    {{-- زانیاری --}}
    <div class="card lg:col-span-1">
        <div class="card-head">زانیاری بابەت</div>
        <div class="card-body space-y-3 text-sm">
            @foreach ([
                'کۆد' => $item->code,
                'جۆر' => $item->category?->name ?? '—',
                'یەکە' => $item->unit?->name ?? '—',
                'سنووری ئاگاداری' => fmt_qty($item->min_qty),
            ] as $label => $value)
                <div class="flex justify-between border-b border-[--color-line] pb-2 last:border-0">
                    <span class="text-[--color-ink-soft]">{{ $label }}</span>
                    <span class="num">{{ $value }}</span>
                </div>
            @endforeach

            @can('view_reports')
                <div class="flex justify-between border-b border-[--color-line] pb-2">
                    <span class="text-[--color-ink-soft]">دوایین نرخی کڕین</span>
                    <span class="num">{{ $item->last_cost ? fmt_money($item->last_cost, $item->cost_currency) : '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">نرخی فرۆشتن</span>
                    <span class="num">{{ $item->sale_price ? fmt_money($item->sale_price, $item->cost_currency) : '—' }}</span>
                </div>
            @endcan

            @if ($item->note)
                <p class="border-t border-[--color-line] pt-3 text-[--color-ink-soft]">{{ $item->note }}</p>
            @endif
        </div>
    </div>

    {{-- باڵانس بەپێی کۆگا --}}
    <div class="card lg:col-span-2">
        <div class="card-head flex items-center justify-between">
            <span>باڵانسی ئێستا</span>
            <span class="num text-lg font-semibold {{ $item->is_low ? 'text-[--color-warn]' : '' }}">
                {{ fmt_qty($item->stockQty()) }} {{ $item->unit?->name }}
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>کۆگا</th>
                        <th class="num">بڕ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($warehouses as $warehouse)
                        <tr>
                            <td>{{ $warehouse->name }}</td>
                            <td class="num font-medium">
                                {{ fmt_qty($item->stockQty($warehouse->id)) }} {{ $item->unit?->name }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- مێژووی جوڵە --}}
<div class="card mt-4">
    <div class="card-head">مێژووی جوڵە</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>بەروار</th>
                    <th>هۆکار</th>
                    <th>کۆگا</th>
                    <th class="num">بڕ</th>
                    <th>بەکارهێنەر</th>
                    <th>تێبینی</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="num whitespace-nowrap">{{ fmt_date($movement->moved_at) }}</td>
                        <td>{{ $movement->reason_label }}</td>
                        <td class="text-[--color-ink-soft]">{{ $movement->warehouse?->name }}</td>
                        <td class="num font-medium {{ $movement->direction === 'in' ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                            {{ $movement->direction === 'in' ? '+' : '−' }}{{ fmt_qty($movement->qty) }}
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $movement->user?->name ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $movement->note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">
                            هێشتا هیچ جوڵەیەک نییە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $movements->links() }}</div>

@endsection
