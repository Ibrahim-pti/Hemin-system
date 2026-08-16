@extends('layouts.app')
@section('title', 'فرۆشیارەکان')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">فرۆشیاری نوێ</a>
@endsection

@section('content')

{{-- فلتەر و گەڕان --}}
<form method="GET" class="card mb-4">
    <div class="card-body flex gap-3">
        <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="گەڕان بەپێی ناوی فرۆشیار یان ژمارەی مۆبایل...">
        <button class="btn btn-primary shrink-0">گەڕان</button>
    </div>
</form>

{{-- خشتەی فرۆشیارەکان --}}
<div class="card">
    <div class="card-head flex items-center justify-between">
        <span>لیستی فرۆشیارەکان</span>
        <span class="text-xs text-[--color-ink-soft]">کۆی گشتی: {{ $suppliers->total() }} فرۆشیار</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="text-right">ناوی فرۆشیار</th>
                    <th class="text-right">مۆبایل</th>
                    <th class="text-right">شوێن</th>
                    <th class="num text-right">کۆی کڕینەکان</th>
                    <th class="num text-right">کۆی پارەی دراو</th>
                    <th class="num text-right">قەرزی ماوە</th>
                    <th class="text-left w-24">کردار</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    @php
                        $purchasesTotal = $supplier->totalPurchases();
                        $paidTotal = $supplier->totalPaid();
                        $balance = $supplier->balance();
                    @endphp
                    <tr>
                        <td class="text-right font-medium">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-[--color-brand-700] hover:underline">
                                {{ $supplier->name }}
                            </a>
                        </td>
                        <td class="text-right num text-slate-700" dir="ltr">{{ $supplier->phone ?? '—' }}</td>
                        <td class="text-right text-[--color-ink-soft] text-xs">{{ $supplier->address ?? '—' }}</td>
                        <td class="text-right num font-semibold text-slate-800">
                            {{ fmt_money($purchasesTotal) }}
                        </td>
                        <td class="text-right num font-semibold text-slate-600">
                            {{ fmt_money($paidTotal) }}
                        </td>
                        <td class="text-right num font-bold {{ $balance > 0 ? 'text-[--color-danger]' : ($balance < 0 ? 'text-[--color-brand-700]' : 'text-[--color-ok]') }}">
                            {{ fmt_money($balance) }}
                        </td>
                        <td class="text-left">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="text-xs font-semibold text-[--color-brand-700] hover:underline">
                                    کەشف حیساب
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="text-xs text-[--color-ink-soft] hover:text-slate-800">
                                    دەستکاری
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشیارێک نەدۆزرایەوە.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="mt-2 text-xs text-[--color-ink-soft]">قەرزی ماوە: ڕەنگی سوور واتە کارگە قەرزاری ئەم فرۆشیارەیە.</p>

<div class="mt-4">{{ $suppliers->links() }}</div>

@endsection
