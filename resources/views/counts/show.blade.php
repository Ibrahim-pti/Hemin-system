@extends('layouts.app')
@section('title', 'جەردی ' . $count->count_no)

@section('content')

{{-- سەرەوە --}}
<div class="card mb-4">
    <div class="card-body flex flex-wrap items-center gap-4 text-sm">
        <div>
            <span class="text-[--color-ink-soft]">کۆگا:</span>
            <span class="font-medium">{{ $count->warehouse->name }}</span>
        </div>
        <div>
            <span class="text-[--color-ink-soft]">بەروار:</span>
            <span class="num font-medium">{{ fmt_date($count->count_date) }}</span>
        </div>
        <div>
            <span class="badge {{ $count->status === 'posted' ? 'badge-ok' : 'badge-warn' }}">{{ $count->status_label }}</span>
        </div>

        @if ($count->status !== 'posted')
            <form method="POST" action="{{ route('counts.post', $count) }}" class="mr-auto"
                  onsubmit="return confirm('دوای پەسەندکردن، جیاوازییەکان دەچنە مەخزەنەوە و ناگۆڕدرێنەوە. بەردەوام بم؟')">
                @csrf
                <button class="btn btn-primary">پەسەندکردن و ڕاستکردنەوەی مەخزەن</button>
            </form>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('counts.update', $count) }}">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>ژماردن</span>
            @if ($count->status !== 'posted')
                <button class="btn btn-ghost !py-1">پاشەکەوتکردن</button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>کاڵا</th>
                        <th>کۆد</th>
                        <th class="num">ژمارەی سیستەم</th>
                        <th class="num">ژمێردراو</th>
                        <th class="num">جیاوازی</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($count->items as $line)
                        @php $diff = (float) $line->counted_qty - (float) $line->system_qty; @endphp
                        <tr>
                            <td>{{ $line->item->name }}</td>
                            <td class="num text-[--color-ink-soft]">{{ $line->item->code }}</td>
                            <td class="num">{{ fmt_qty($line->system_qty) }} {{ $line->item->unit?->name }}</td>
                            <td class="num">
                                @if ($count->status === 'posted')
                                    {{ fmt_qty($line->counted_qty) }}
                                @else
                                    <input type="number" step="0.001" name="counted[{{ $line->id }}]"
                                           value="{{ $line->counted_qty }}"
                                           class="field num w-28 !py-1" placeholder="—">
                                @endif
                            </td>
                            <td class="num font-medium
                                {{ $line->counted_qty === null ? 'text-[--color-ink-soft]'
                                   : (abs($diff) < 0.0005 ? '' : ($diff > 0 ? 'text-[--color-ok]' : 'text-[--color-danger]')) }}">
                                @if ($line->counted_qty === null)
                                    —
                                @else
                                    {{ $diff > 0 ? '+' : '' }}{{ fmt_qty($diff) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($count->status !== 'posted')
        <div class="mt-4 flex gap-2">
            <button class="btn btn-primary">پاشەکەوتکردن</button>
            <a href="{{ route('counts.index') }}" class="btn btn-ghost">گەڕانەوە</a>
            <button type="submit" form="delete-count" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم جەردە؟')">سڕینەوە</button>
        </div>
    @endif
</form>

@if ($count->status !== 'posted')
    <form id="delete-count" method="POST" action="{{ route('counts.destroy', $count) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
