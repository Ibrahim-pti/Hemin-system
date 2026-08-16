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
                    <th class="text-center w-28">کردار</th>
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
                        <td class="text-center">
                            <div class="inline-flex items-center justify-center gap-1.5">
                                {{-- دوگمەی کردنەوە بە ئایکۆن --}}
                                <a href="{{ route('counts.show', $count) }}"
                                   class="inline-flex items-center justify-center size-8 rounded-lg text-blue-600 hover:bg-blue-50 border border-slate-200 transition-colors"
                                   title="کردنەوەی جەرد">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>

                                {{-- دوگمەی سڕینەوە بە ئایکۆن --}}
                                @if ($count->status !== 'posted')
                                    <form method="POST" action="{{ route('counts.destroy', $count) }}"
                                          onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم جەردە؟')"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center size-8 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition-colors"
                                                title="سڕینەوە">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
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

{{-- پەیجینەیشن --}}
<div class="mt-4">{{ $counts->links() }}</div>

@endsection
