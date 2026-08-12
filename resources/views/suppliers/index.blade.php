@extends('layouts.app')
@section('title', 'فرۆشیارەکان')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">فرۆشیاری نوێ</a>
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body flex gap-3">
        <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناو یان تەلەفۆن...">
        <button class="btn btn-primary">گەڕان</button>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناو</th>
                    <th>تەلەفۆن</th>
                    <th>شوێن</th>
                    <th class="num">پسوولە</th>
                    <th class="num">باڵانس</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    @php $balance = $supplier->balance(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-[--color-brand-700]">
                                {{ $supplier->name }}
                            </a>
                        </td>
                        <td class="num" dir="ltr">{{ $supplier->phone ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $supplier->address ?? '—' }}</td>
                        <td class="num">{{ fmt_num($supplier->purchases_count) }}</td>
                        <td class="num font-medium {{ $balance > 0 ? 'text-[--color-danger]' : '' }}">
                            {{ fmt_money($balance) }}
                        </td>
                        <td class="text-left">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm text-[--color-brand-700]">دەستکاری</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشیارێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="mt-2 text-xs text-[--color-ink-soft]">باڵانسی ئەرێنی = کارگە قەرزاری ئەم فرۆشیارەیە.</p>

<div class="mt-4">{{ $suppliers->links() }}</div>

@endsection
