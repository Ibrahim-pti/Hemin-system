@extends('layouts.app')
@section('title', 'فرۆشیارەکان')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">فرۆشیاری نوێ</a>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }">

    {{-- ١. کارتەکانی کورتە-ئامار بە ئایکۆنە فەرمییەکانی سیستم --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">
        {{-- فرۆشیارەکان --}}
        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip bg-blue-50 text-blue-600 shrink-0">
                @include('partials.icon', ['name' => 'suppliers', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-slate-500">کۆی فرۆشیارەکان</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-slate-800">{{ fmt_num($suppliers->total()) }}</div>
            </div>
        </div>

        {{-- کۆی کڕینەکان --}}
        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip bg-indigo-50 text-indigo-600 shrink-0">
                @include('partials.icon', ['name' => 'purchases', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-slate-500">کۆی گشتی کڕینەکان</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-slate-800">{{ fmt_money($totalPurchases) }}</div>
            </div>
        </div>

        {{-- کۆی پارەی دراو --}}
        <div class="card flex items-center gap-3.5 px-4 py-3.5">
            <span class="icon-chip icon-chip-ok shrink-0">
                @include('partials.icon', ['name' => 'payments', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-slate-500">کۆی پارەی دراو</div>
                <div class="num mt-0.5 truncate text-lg font-bold text-emerald-600">{{ fmt_money($totalPaid) }}</div>
            </div>
        </div>

        {{-- قەرزی ماوەی سەر کارگە --}}
        <div class="card flex items-center gap-3.5 px-4 py-3.5 {{ $totalDebt > 0 ? 'border-rose-200 bg-rose-50/20' : '' }}">
            <span class="icon-chip {{ $totalDebt > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-600' }} shrink-0">
                @include('partials.icon', ['name' => 'debts', 'class' => 'size-5'])
            </span>
            <div class="min-w-0">
                <div class="truncate text-xs font-medium text-slate-500">کۆی قەرزی سەر کارگە</div>
                <div class="num mt-0.5 truncate text-lg font-bold {{ $totalDebt > 0 ? 'text-[--color-danger]' : 'text-slate-800' }}">
                    {{ fmt_money($totalDebt) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. فۆرمی گەڕان بە دیزاینی ستاندارد --}}
    <form method="GET" class="card mb-4">
        <div class="card-body flex gap-3">
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناو، مۆبایل یان شوێن...">
            <button class="btn btn-primary">گەڕان</button>
            @if(request('q'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">پاککردنەوە</a>
            @endif
        </div>
    </form>

    {{-- ٣. خشتەی فرۆشیارەکان --}}
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>ناوی فرۆشیار</th>
                        <th>مۆبایل</th>
                        <th>شوێن</th>
                        <th class="num">کۆی کڕین</th>
                        <th class="num">کۆی دراو</th>
                        <th class="num">قەرزی ماوە</th>
                        <th style="width: 120px;" class="text-center">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $idx => $supplier)
                        @php
                            $purchasesTotal = $supplier->totalPurchases();
                            $paidTotal = $supplier->totalPaid();
                            $balance = $supplier->balance();
                        @endphp
                        <tr>
                            {{-- # --}}
                            <td class="text-center text-xs text-[--color-ink-soft]">
                                {{ $suppliers->firstItem() + $idx }}
                            </td>

                            {{-- ناوی فرۆشیار --}}
                            <td>
                                <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-[--color-brand-700] hover:underline">
                                    {{ $supplier->name }}
                                </a>
                            </td>

                            {{-- مۆبایل --}}
                            <td class="num" dir="ltr">{{ $supplier->phone ?? '—' }}</td>

                            {{-- شوێن --}}
                            <td class="text-[--color-ink-soft]">{{ $supplier->address ?? '—' }}</td>

                            {{-- کۆی کڕینەکان --}}
                            <td class="num font-medium">{{ fmt_money($purchasesTotal) }}</td>

                            {{-- کۆی پارەی دراو --}}
                            <td class="num font-medium">{{ fmt_money($paidTotal) }}</td>

                            {{-- قەرزی ماوە --}}
                            <td class="num font-bold {{ $balance > 0 ? 'text-[--color-danger]' : '' }}">
                                {{ fmt_money($balance) }}
                            </td>

                            {{-- کردارەکان --}}
                            <td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    {{-- بینینی کەشف حیساب --}}
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-blue-600 hover:bg-blue-50 border border-slate-200 transition-colors"
                                       title="کەشف حیساب">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    {{-- دەستکاری --}}
                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-slate-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                       title="دەستکاری">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>

                                    {{-- سڕینەوە --}}
                                    <button type="button"
                                            @click="deleteUrl = '{{ route('suppliers.destroy', $supplier) }}'; showDeleteModal = true"
                                            class="inline-flex items-center justify-center size-8 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition-colors"
                                            title="سڕینەوە">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشیارێک نییە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($suppliers->hasPages())
        <div class="mt-4">{{ $suppliers->links() }}</div>
    @endif

    {{-- مۆداڵی سڕینەوە لە تەواوی ناوەڕاست --}}
    <template x-teleport="body">
        <div x-show="showDeleteModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px);"
             @keydown.escape.window="showDeleteModal = false">
            
            <div class="bg-white rounded-2xl border border-slate-200 p-5 max-w-xs w-full text-center space-y-4 shadow-xl"
                 @click.away="showDeleteModal = false">
                
                <h3 class="text-sm font-bold text-slate-800 pt-1">ئایا دڵنیایت لە سڕینەوە؟</h3>

                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click="showDeleteModal = false" class="btn btn-ghost !py-2 text-xs font-medium">
                        پاشگەزبوونەوە
                    </button>
                    <form :action="deleteUrl" method="POST" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full !py-2 text-xs font-medium">
                            سڕینەوە
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>

</div>

@endsection
