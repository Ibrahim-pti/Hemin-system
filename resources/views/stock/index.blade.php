@extends('layouts.app')
@section('title', 'جوڵەی مەخزەن')

@section('actions')
    @can('manage_stock')
        <a href="{{ route('stock.create') }}" class="btn btn-primary">جوڵەی نوێ</a>
    @endcan
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label class="label">کاڵا</label>
            <select name="item" class="field">
                <option value="">هەموو</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}" @selected(request('item') == $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">کۆگا</label>
            <select name="warehouse" class="field">
                <option value="">هەموو</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">هۆکار</label>
            <select name="reason" class="field">
                <option value="">هەموو</option>
                @foreach (\App\Models\StockMovement::REASONS as $key => $label)
                    <option value="{{ $key }}" @selected(request('reason') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ request('from') }}" class="field num">
        </div>

        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="label">تا بەرواری</label>
                <input type="date" name="to" value="{{ request('to') }}" class="field num">
            </div>
            <button class="btn btn-primary">پاڵاوتن</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>بەروار</th>
                    <th>کاڵا</th>
                    <th>کۆگا</th>
                    <th>هۆکار</th>
                    <th class="num">بڕ</th>
                    <th>بەکارهێنەر</th>
                    <th>تێبینی</th>
                    @can('manage_stock')<th></th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td class="num whitespace-nowrap">{{ fmt_date($movement->moved_at) }}</td>
                        <td>
                            <a href="{{ route('items.show', $movement->item_id) }}" class="text-[--color-brand-700]">
                                {{ $movement->item?->name }}
                            </a>
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $movement->warehouse?->name }}</td>
                        <td>{{ $movement->reason_label }}</td>
                        <td class="num font-medium {{ $movement->direction === 'in' ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                            {{ $movement->direction === 'in' ? '+' : '−' }}{{ fmt_qty($movement->qty) }}
                            <span class="text-[--color-ink-soft]">{{ $movement->item->unit?->name }}</span>
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $movement->user?->name ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $movement->note ?? '—' }}</td>
                        @can('manage_stock')
                            <td class="text-left">
                                @unless ($movement->reference_type)
                                    <button type="submit" form="del-{{ $movement->id }}"
                                            class="text-sm text-[--color-danger]"
                                            onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم جوڵەیە؟')">سڕینەوە</button>
                                @endunless
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ جوڵەیەک نەدۆزرایەوە.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@can('manage_stock')
    @foreach ($movements as $movement)
        @unless ($movement->reference_type)
            <form id="del-{{ $movement->id }}" method="POST" action="{{ route('stock.destroy', $movement) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
        @endunless
    @endforeach
@endcan

<div class="mt-4">{{ $movements->links() }}</div>

@endsection
