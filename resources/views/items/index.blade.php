@extends('layouts.app')
@section('title', 'کاڵا و مەواد')

@section('actions')
    @can('manage_items')
        <a href="{{ route('items.create') }}" class="btn btn-primary">کاڵای نوێ</a>
    @endcan
@endsection

@section('content')

{{-- پاڵاوتن --}}
<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="label">گەڕان</label>
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناو یان کۆد...">
        </div>

        <div>
            <label class="label">جۆر</label>
            <select name="category" class="field">
                <option value="">هەموو</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">کۆگا</label>
            <select name="warehouse" class="field">
                <option value="">هەموو کۆگاکان</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse') == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <label class="flex flex-1 items-center gap-2 text-sm">
                <input type="checkbox" name="low" value="1" @checked(request('low'))
                       class="size-4 rounded border-[--color-line-strong]">
                تەنها کەم
            </label>
            <button class="btn btn-primary">پاڵاوتن</button>
        </div>
    </div>
</form>

{{-- خشتە --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>کاڵا</th>
                    <th>کۆد</th>
                    <th>جۆر</th>
                    <th class="num">لە مەخزەن</th>
                    <th class="num">کەمترین</th>
                    @can('view_reports')
                        <th class="num">نرخی کڕین</th>
                    @endcan
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('items.show', $item) }}" class="font-medium text-[--color-brand-700]">
                                {{ $item->name }}
                            </a>
                            @unless ($item->is_active)
                                <span class="badge badge-warn mr-1">ناچالاک</span>
                            @endunless
                        </td>
                        <td class="num text-[--color-ink-soft]">{{ $item->code }}</td>
                        <td class="text-[--color-ink-soft]">{{ $item->category?->name ?? '—' }}</td>
                        <td class="num font-medium {{ $item->is_low ? 'text-[--color-warn]' : '' }}">
                            {{ fmt_qty($item->stock_qty) }} {{ $item->unit?->name }}
                        </td>
                        <td class="num text-[--color-ink-soft]">{{ fmt_qty($item->min_qty) }}</td>
                        @can('view_reports')
                            <td class="num">{{ $item->last_cost ? fmt_money($item->last_cost, $item->cost_currency) : '—' }}</td>
                        @endcan
                        <td class="text-left whitespace-nowrap">
                            @can('manage_items')
                                <a href="{{ route('items.edit', $item) }}" class="text-sm text-[--color-brand-700]">دەستکاری</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">
                            هیچ کاڵایەک نەدۆزرایەوە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $items->links() }}</div>

@endsection
