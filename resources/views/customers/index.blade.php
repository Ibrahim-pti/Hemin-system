@extends('layouts.app')
@section('title', 'کڕیاران')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ openOldDebtModal: false, selectedCustId: '', isNewCustomer: false, currency: 'IQD', debtStatus: 'debt' }">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکانی کردار --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-2xl shadow-md shadow-blue-500/20 shrink-0">
                👥
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">لیستی کڕیاران</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/80">
                        فرۆشتن و دارایی
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    بەڕێوەبردنی زانیاری کڕیاران، وەسڵەکانی دروستکردن و باڵانسی قەرزەکان
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- دوگمەی حیسابی پێشوو --}}
            <a href="{{ route('debts.old-debt.create') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white inline-flex items-center gap-1.5 transition-all shadow-sm">
                <span>📜</span>
                <span>حیسابی پێشوو</span>
            </a>

            <a href="{{ route('orders.create') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 inline-flex items-center gap-1.5 transition-all">
                <span>➕</span>
                <span>وەسڵی نوێ</span>
            </a>

            <a href="{{ route('customers.create') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white inline-flex items-center gap-1.5 transition-all shadow-sm">
                <span>+</span>
                <span>زیادکردنی کڕیار</span>
            </a>
        </div>
    </div>

    {{-- ٢. ٤ کارتی ئاماری سەرەکی --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1">کۆی کڕیاران</div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ fmt_num($totalCustomers) }}</div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span>💰</span>
                <span>کۆی فرۆشتن</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 font-mono">{{ fmt_money($totalSales) }}</div>
        </div>

        <div class="bg-rose-50/70 rounded-2xl p-4 border border-rose-200/80 shadow-xs">
            <div class="text-xs font-bold text-rose-800 mb-1 flex items-center gap-1.5">
                <span>⚠️</span>
                <span>کۆی قەرز لەسەر کڕیاران</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-600 font-mono">{{ fmt_money($totalDebt) }}</div>
        </div>

        <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200/80 shadow-xs">
            <div class="text-xs font-bold text-amber-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-amber-500"></span>
                <span>کڕیاری قەرزدار</span>
            </div>
            <div class="text-2xl font-black text-amber-700 font-mono">{{ fmt_num($debtorCount) }}</div>
        </div>
    </div>

    {{-- ٣. خشتەی کڕیاران --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        {{-- شریتی سەرەوەی خشتە و گەڕان --}}
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2">
                <span class="text-base">📋</span>
                <h3 class="font-black text-sm text-slate-800">تۆماری کڕیاران</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                    {{ $customers->total() }} کڕیار
                </span>
            </div>

            {{-- خانەی گەڕان --}}
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-medium text-right w-full sm:w-72"
                       placeholder=" 🔍 گەڕان بە ناو، مۆبایل، ناونیشان...">
                @if(request('q'))
                    <a href="{{ route('customers.index') }}"
                       class="px-2.5 py-2 text-xs font-bold text-slate-400 hover:text-slate-600 rounded-xl bg-slate-100">
                        ✕
                    </a>
                @endif
            </form>
        </div>

        {{-- خشتەی پڕۆفێشناڵ بە دابەشبوونی دروستی ستوونەکان --}}
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 w-12 text-center">#</th>
                        <th class="p-3.5">ناوی کڕیار</th>
                        <th class="p-3.5">ژمارەی مۆبایل</th>
                        <th class="p-3.5">ناونیشان / شوێن</th>
                        <th class="p-3.5 text-center">وەسڵەکان</th>
                        <th class="p-3.5 text-left">باڵانس / قەرز</th>
                        <th class="p-3.5 text-center w-32">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $index => $customer)
                        @php
                            $bal = $customer->balance();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- # --}}
                            <td class="p-3.5 text-center font-mono font-bold text-slate-400">
                                {{ $customers->firstItem() + $index }}
                            </td>

                            {{-- ناو --}}
                            <td class="p-3.5">
                                <a href="{{ route('customers.show', $customer) }}" class="font-black text-slate-900 text-xs hover:text-blue-600 transition-colors block">
                                    {{ $customer->name }}
                                </a>
                                @if ($customer->note)
                                    <span class="text-[11px] text-slate-400 font-normal block truncate max-w-xs mt-0.5">{{ $customer->note }}</span>
                                @endif
                            </td>

                            {{-- مۆبایل --}}
                            <td class="p-3.5 font-mono text-slate-700 font-bold" dir="ltr">
                                {{ $customer->phone ?: '—' }}
                            </td>

                            {{-- ناونیشان --}}
                            <td class="p-3.5 text-slate-600 font-medium">
                                {{ $customer->address ?: '—' }}
                            </td>

                            {{-- وەسڵەکان --}}
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold text-[11px] border border-slate-200/60">
                                    {{ $customer->orders_count ?? 0 }} وەسڵ
                                </span>
                            </td>

                            {{-- باڵانس --}}
                            <td class="p-3.5 text-left">
                                @if($bal > 0)
                                    <span class="px-2.5 py-0.5 rounded-md font-mono font-black text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ fmt_money($bal) }}
                                    </span>
                                @elseif($bal < 0)
                                    <span class="px-2.5 py-0.5 rounded-md font-mono font-black text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ fmt_money(abs($bal)) }} (زیادە)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-md font-mono font-bold text-xs text-slate-400 bg-slate-100">
                                        0 د.ع
                                    </span>
                                @endif
                            </td>

                            {{-- کردارەکان --}}
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('debts.old-debt.create', ['customer_id' => $customer->id]) }}"
                                       class="px-2 py-1 rounded-lg text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-all"
                                       title="تۆمارکردنی حیسابی پێشوو">
                                        📜
                                    </a>
                                    <a href="{{ route('customers.show', $customer) }}"
                                       class="px-2 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all"
                                       title="پڕۆفایل">
                                        👤
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}"
                                       class="px-2 py-1 rounded-lg text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-all"
                                       title="دەستکاری">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                          onsubmit="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کڕیارە؟')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-2 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer"
                                                title="سڕینەوە">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-400 font-medium">
                                <div class="text-3xl mb-2">👥</div>
                                <div>هیچ کڕیارێک نەدۆزرایەوە.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- مۆداڵی تۆمارکردنی حیسابی پێشوو (بەبێ دروستکردنی داواکاری لە کارگە) --}}
    <div x-show="openOldDebtModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="openOldDebtModal = false">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in-95 duration-150"
             @click.outside="openOldDebtModal = false">

            {{-- سەری مۆداڵ بە ڕەنگی دیار و ئایکۆنی ڕوونی لابردن --}}
            <div style="background: #b45309; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem;">
                    <span style="font-size: 1.25rem;">📜</span>
                    <span>تۆمارکردنی حیسابی پێشوو</span>
                </div>
                <button type="button" @click="openOldDebtModal = false"
                        title="داخستن"
                        style="background: rgba(255, 255, 255, 0.2); border: none; border-radius: 0.5rem; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #ffffff; cursor: pointer; line-height: 1;"
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.35)'"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                    ✕
                </button>
            </div>

            <form method="POST" action="{{ route('debts.old-debt') }}" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf

                {{-- هەڵبژاردنی کڕیار --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        کڕیار هەڵبژێرە <span class="text-rose-500">*</span>
                    </label>
                    <select name="customer_id" x-model="selectedCustId"
                            @change="isNewCustomer = ($event.target.value === '__NEW__')"
                            class="field w-full font-bold text-slate-800 rounded-xl">
                        <option value="">— کڕیارێک هەڵبژێرە —</option>
                        @foreach ($allCustomers as $cust)
                            <option value="{{ $cust->id }}">{{ $cust->name }} {{ $cust->phone ? "({$cust->phone})" : '' }}</option>
                        @endforeach
                        <option value="__NEW__">➕ کڕیاری نوێ بنووسە...</option>
                    </select>
                </div>

                {{-- ئەگەر کڕیاری نوێ هەڵبژێردرابێت --}}
                <div x-show="isNewCustomer" x-cloak class="space-y-3 bg-slate-50 border border-slate-200 rounded-xl p-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            ناوی کڕیاری نوێ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="new_customer_name" class="field w-full rounded-xl text-xs font-bold"
                               placeholder="ناوی تەواوی کڕیار...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            ژمارەی مۆبایل
                        </label>
                        <input type="text" name="new_customer_phone" class="field num w-full rounded-xl text-xs" dir="ltr"
                               placeholder="0750...">
                    </div>
                </div>

                {{-- دۆخی حیساب: قەرزە یان پارەدانی تەواو بووە --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        دۆخی حیساب / پارەدان <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all text-xs font-bold select-none"
                               :class="debtStatus === 'debt' ? 'border-rose-500 bg-rose-50 text-rose-700 shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="status" value="debt" x-model="debtStatus" class="hidden">
                            <span>⚠️</span>
                            <span>قەرزە (نەدراوە)</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition-all text-xs font-bold select-none"
                               :class="debtStatus === 'paid' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="status" value="paid" x-model="debtStatus" class="hidden">
                            <span>✓</span>
                            <span>پارەدانی تەواو بووە</span>
                        </label>
                    </div>
                </div>

                {{-- جۆری دراو و بڕی قەرز --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        بڕی پارە <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="number" step="any" min="0.01" name="amount" required
                                   class="field num w-full !py-2.5 !px-3 rounded-xl font-black text-lg text-center"
                                   :class="debtStatus === 'paid' ? 'text-emerald-700' : 'text-rose-600'"
                                   placeholder="0">
                        </div>
                        <div class="w-36">
                            <select name="currency" x-model="currency" class="field w-full !py-2.5 rounded-xl font-bold text-xs text-slate-800">
                                <option value="IQD">دیناری عێراقی (د.ع)</option>
                                <option value="USD">دۆلاری ئەمریکی ($)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- بەروار --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">بەروار</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}"
                               class="field num w-full !py-2 rounded-xl text-xs font-bold text-slate-700">
                    </div>

                    {{-- وێنەی وەسڵ / بەڵگە --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            📷 وێنەی وەسڵ / دەفتەر (ئارەزوومەندانە)
                        </label>
                        <input type="file" name="image" accept="image/*"
                               class="field w-full !py-1 text-xs rounded-xl file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        تێبینی / هۆکاری حیسابەکە
                    </label>
                    <textarea name="note" rows="2" class="field w-full rounded-xl text-xs"
                              placeholder="نموونە: حیساباتی کۆن لە دەفتەر، باڵانسی پێش سیستم..."></textarea>
                </div>

                {{-- دوگمەکانی ناردن و داخستن --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="openOldDebtModal = false"
                            class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 cursor-pointer">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-black rounded-xl bg-amber-600 hover:bg-amber-700 text-white shadow-sm inline-flex items-center gap-1.5 cursor-pointer">
                        <span>✓</span>
                        <span>تۆمارکردن لە حیسابی کڕیار</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
