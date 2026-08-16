@extends('layouts.app')
@section('title', 'جەردی ' . $count->count_no)

@section('content')

{{-- سەرەوەی جەرد --}}
<div class="card mb-4">
    <div class="card-body flex flex-wrap items-center justify-between gap-4 text-sm">
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <span class="text-[--color-ink-soft]">کۆگا:</span>
                <span class="font-medium text-slate-800">{{ $count->warehouse?->name }}</span>
            </div>
            <div>
                <span class="text-[--color-ink-soft]">بەروار:</span>
                <span class="num font-medium">{{ fmt_date($count->count_date) }}</span>
            </div>
            <div>
                <span class="badge {{ $count->status === 'posted' ? 'badge-ok' : 'badge-warn' }}">{{ $count->status_label }}</span>
            </div>
            @if($count->note)
                <div>
                    <span class="text-[--color-ink-soft]">تێبینی:</span>
                    <span class="text-slate-700">{{ $count->note }}</span>
                </div>
            @endif
        </div>

        @if ($count->status !== 'posted')
            <form method="POST" action="{{ route('counts.post', $count) }}"
                  onsubmit="return confirm('دوای پەسەندکردن، جیاوازییەکان وەک جوڵەی مەخزەن تۆمار دەکرێن و ناگۆڕدرێنەوە. دڵنیایت؟')">
                @csrf
                <button class="btn btn-primary !py-1.5 !px-3 text-xs">پەسەندکردن و ڕاستکردنەوەی مەخزەن</button>
            </form>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('counts.update', $count) }}">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>لیستی کاڵاکان بۆ جەردکردن</span>
            @if ($count->status !== 'posted')
                <button type="submit" class="btn btn-ghost !py-1 text-xs">پاشەکەوتکردن</button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table" id="count-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>کاڵا</th>
                        <th>کۆد</th>
                        <th class="num">ژمارەی سیستەم</th>
                        <th class="num w-36">ژمێردراو</th>
                        <th class="num w-32">جیاوازی</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($count->items as $idx => $line)
                        @php $diff = (float) $line->counted_qty - (float) $line->system_qty; @endphp
                        <tr data-sys="{{ (float) $line->system_qty }}">
                            <td class="text-center text-xs text-[--color-ink-soft] num">{{ $idx + 1 }}</td>
                            <td class="font-medium text-slate-800">{{ $line->item?->name }}</td>
                            <td class="num text-[--color-ink-soft] text-xs">{{ $line->item?->code ?? '—' }}</td>
                            <td class="num font-semibold text-slate-700">
                                {{ fmt_qty($line->system_qty) }} <span class="text-xs text-[--color-ink-soft] font-normal">{{ $line->item?->unit?->name }}</span>
                            </td>
                            <td class="num">
                                @if ($count->status === 'posted')
                                    <span class="font-semibold">{{ fmt_qty($line->counted_qty) }}</span>
                                @else
                                    <input type="number" step="any" name="counted[{{ $line->id }}]"
                                           value="{{ $line->counted_qty }}"
                                           class="field num w-28 !py-1 text-left counted-input"
                                           placeholder="—">
                                @endif
                            </td>
                            <td class="num font-medium diff-cell
                                {{ $line->counted_qty === null ? 'text-[--color-ink-soft]'
                                   : (abs($diff) < 0.0005 ? 'text-slate-600' : ($diff > 0 ? 'text-[--color-ok]' : 'text-[--color-danger]')) }}">
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
        <div class="mt-4 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">پاشەکەوتکردن</button>
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

    <script>
        document.querySelectorAll('.counted-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const tr = this.closest('tr');
                const sys = parseFloat(tr.dataset.sys) || 0;
                const diffCell = tr.querySelector('.diff-cell');
                const val = this.value.trim();

                if (val === '') {
                    diffCell.textContent = '—';
                    diffCell.className = 'num font-medium diff-cell text-[--color-ink-soft]';
                    return;
                }

                const cnt = parseFloat(val) || 0;
                const diff = cnt - sys;

                if (Math.abs(diff) < 0.0005) {
                    diffCell.textContent = '0';
                    diffCell.className = 'num font-medium diff-cell text-slate-600';
                } else if (diff > 0) {
                    diffCell.textContent = '+' + (Math.round(diff * 1000) / 1000);
                    diffCell.className = 'num font-medium diff-cell text-[--color-ok]';
                } else {
                    diffCell.textContent = (Math.round(diff * 1000) / 1000).toString();
                    diffCell.className = 'num font-medium diff-cell text-[--color-danger]';
                }
            });
        });
    </script>
@endif

@endsection
