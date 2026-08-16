@extends('layouts.app')
@section('title', 'فرۆشیارەکان')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary gap-1.5 shadow-sm">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>فرۆشیاری نوێ</span>
    </a>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }">

    {{-- ١. کارتەکانی ئامار --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
        {{-- کۆی فرۆشیارەکان --}}
        <div class="card p-3.5 bg-white border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="size-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <div class="text-xs text-[--color-ink-soft]">کۆی فرۆشیارەکان</div>
                <div class="text-base font-bold text-slate-900 num mt-0.5">{{ fmt_num($suppliers->total()) }}</div>
            </div>
        </div>

        {{-- کۆی کڕینەکان --}}
        <div class="card p-3.5 bg-white border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="size-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                    <path d="M3 6h18"></path>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div>
                <div class="text-xs text-[--color-ink-soft]">کۆی گشتی کڕینەکان</div>
                <div class="text-base font-bold text-slate-900 num mt-0.5">{{ fmt_money($totalPurchases) }}</div>
            </div>
        </div>

        {{-- کۆی پارەی دراو --}}
        <div class="card p-3.5 bg-white border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="size-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                    <line x1="2" x2="22" y1="10" y2="10"></line>
                </svg>
            </div>
            <div>
                <div class="text-xs text-[--color-ink-soft]">کۆی پارەی دراو</div>
                <div class="text-base font-bold text-emerald-700 num mt-0.5">{{ fmt_money($totalPaid) }}</div>
            </div>
        </div>

        {{-- قەرزی ماوەی سەر کارگە --}}
        <div class="card p-3.5 bg-white border border-slate-200/80 shadow-sm flex items-center gap-3 {{ $totalDebt > 0 ? 'bg-rose-50/20 border-rose-200' : '' }}">
            <div class="size-10 rounded-xl {{ $totalDebt > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center shrink-0">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div>
                <div class="text-xs text-[--color-ink-soft]">کۆی قەرزی سەر کارگە</div>
                <div class="text-base font-bold num mt-0.5 {{ $totalDebt > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                    {{ fmt_money($totalDebt) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. فلتەر و گەڕان --}}
    <form method="GET" class="card mb-4">
        <div class="card-body flex gap-3 p-3">
            <div class="relative flex-1">
                <input type="search" name="q" value="{{ request('q') }}" class="field w-full pr-9 pl-3 text-sm" placeholder="گەڕان بەپێی ناوی فرۆشیار یان ژمارەی مۆبایل...">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>
            <button class="btn btn-primary shrink-0 text-xs px-5">گەڕان</button>
            @if(request('q'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost shrink-0 text-xs text-slate-500">پاککردنەوە</a>
            @endif
        </div>
    </form>

    {{-- ٣. خشتەی فرۆشیارەکان --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full" style="direction: rtl;">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-[--color-line] text-slate-700 text-xs">
                        <th style="width: 48px; text-align: center; padding: 12px 10px;">#</th>
                        <th style="text-align: right; padding: 12px 16px;">ناوی فرۆشیار</th>
                        <th style="width: 140px; text-align: right; padding: 12px 16px;">مۆبایل</th>
                        <th style="width: 140px; text-align: right; padding: 12px 16px;">شوێن</th>
                        <th style="width: 160px; text-align: right; padding: 12px 16px;">کۆی کڕینەکان</th>
                        <th style="width: 160px; text-align: right; padding: 12px 16px;">کۆی پارەی دراو</th>
                        <th style="width: 160px; text-align: right; padding: 12px 16px;">قەرزی ماوە</th>
                        <th style="width: 130px; text-center; padding: 12px 16px;">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($suppliers as $idx => $supplier)
                        @php
                            $purchasesTotal = $supplier->totalPurchases();
                            $paidTotal = $supplier->totalPaid();
                            $balance = $supplier->balance();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- # --}}
                            <td style="text-align: center; padding: 12px 10px; color: var(--color-ink-soft); font-size: 12px;">
                                {{ $suppliers->firstItem() + $idx }}
                            </td>

                            {{-- ناوی فرۆشیار --}}
                            <td style="text-align: right; padding: 12px 16px;">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="font-bold text-slate-900 hover:text-[--color-brand-700] hover:underline">
                                    {{ $supplier->name }}
                                </a>
                            </td>

                            {{-- مۆبایل --}}
                            <td style="text-align: right; padding: 12px 16px;" class="num text-slate-600 text-xs" dir="ltr">
                                {{ $supplier->phone ?? '—' }}
                            </td>

                            {{-- شوێن --}}
                            <td style="text-align: right; padding: 12px 16px;" class="text-[--color-ink-soft] text-xs">
                                {{ $supplier->address ?? '—' }}
                            </td>

                            {{-- کۆی کڕینەکان --}}
                            <td style="text-align: right; padding: 12px 16px;" class="num font-semibold text-slate-900">
                                {{ fmt_money($purchasesTotal) }}
                            </td>

                            {{-- کۆی پارەی دراو --}}
                            <td style="text-align: right; padding: 12px 16px;" class="num font-semibold text-emerald-700">
                                {{ fmt_money($paidTotal) }}
                            </td>

                            {{-- قەرزی ماوە --}}
                            <td style="text-align: right; padding: 12px 16px;" class="num font-bold {{ $balance > 0 ? 'text-[--color-danger]' : ($balance < 0 ? 'text-[--color-brand-700]' : 'text-[--color-ok]') }}">
                                {{ fmt_money($balance) }}
                            </td>

                            {{-- کردارەکان بە ئایکۆن --}}
                            <td style="text-align: center; padding: 12px 16px;">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    {{-- بینینی کەشف حیساب --}}
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-blue-600 hover:bg-blue-50 border border-slate-200 transition-colors"
                                       title="کەشف حیساب و وردەکاری">
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

                                    {{-- سڕینەوە بە مۆداڵی سادە --}}
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
                            <td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشیارێک نەدۆزرایەوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- پەیجینەیشن --}}
    <div class="mt-4 flex items-center justify-between">
        <div class="text-xs text-[--color-ink-soft]">
            نیشاندانی {{ $suppliers->firstItem() ?? 0 }} تا {{ $suppliers->lastItem() ?? 0 }} لە کۆی {{ $suppliers->total() }} فرۆشیار
        </div>
        <div>
            {{ $suppliers->links() }}
        </div>
    </div>

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
