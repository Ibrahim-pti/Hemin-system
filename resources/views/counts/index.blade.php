@extends('layouts.app')
@section('title', 'جەردی کۆگا')

@section('content')

{{-- جەردی نوێ --}}
<form method="POST" action="{{ route('counts.store') }}" class="card mb-4">
    @csrf
    <div class="card-head">دەستپێکردنی جەردێکی نوێ</div>
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="label">کۆگا</label>
            <select name="warehouse_id" class="field" required>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected($warehouse->is_default)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label">بەروار</label>
            <input type="date" name="count_date" class="field num" required value="{{ now()->toDateString() }}">
        </div>

        <div>
            <label class="label">تێبینی</label>
            <input name="note" class="field" placeholder="ئارەزوومەندانە">
        </div>

        <div class="flex items-end">
            <button class="btn btn-primary w-full">دەستپێکردن</button>
        </div>
    </div>
    <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft]">
        کاتی دەستپێکردن، ژمارەی ئێستای هەموو کاڵاکان وەک «ژمارەی سیستەم» تۆمار دەکرێت.
    </div>
</form>

{{-- جەردەکانی پێشوو --}}
<div class="card">
    <div class="card-head">جەردەکانی پێشوو</div>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="text-right">بەروار</th>
                    <th class="text-right">کۆگا</th>
                    <th class="text-right">تێبینی</th>
                    <th class="text-right">دۆخ</th>
                    <th class="text-right">بەکارهێنەر</th>
                    <th class="text-left w-24"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($counts as $count)
                    <tr>
                        <td class="text-right num font-medium">{{ fmt_date($count->count_date) }}</td>
                        <td class="text-right">{{ $count->warehouse?->name }}</td>
                        <td class="text-right text-[--color-ink-soft] text-xs">{{ $count->note ?? '—' }}</td>
                        <td class="text-right">
                            <span class="badge {{ $count->status === 'posted' ? 'badge-ok' : 'badge-warn' }}">
                                {{ $count->status_label }}
                            </span>
                        </td>
                        <td class="text-right text-[--color-ink-soft]">{{ $count->user?->name ?? '—' }}</td>
                        <td class="text-left">
                            <a href="{{ route('counts.show', $count) }}" class="text-sm font-medium text-[--color-brand-700] hover:underline">کردنەوە</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">هێشتا هیچ جەردێک نییە.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $counts->links() }}</div>

@endsection
