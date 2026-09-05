@extends('layouts.app')
@section('title', 'پڕۆفایلی ' . $customer->name)

@section('actions')
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('customers.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
            <span>&larr;</span>
            <span>گەڕانەوە</span>
        </a>
        <a href="{{ route('debts.old-debt.create', ['customer_id' => $customer->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-amber-600 hover:bg-amber-700 text-white shadow-sm">
            <span>📜</span>
            <span>+ حیسابی پێشوو</span>
        </a>
        <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $customer->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">
            <span>+</span>
            <span>حەقدی</span>
        </a>
        <a href="{{ route('customers.statement', $customer) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold gap-1 border border-slate-200 hover:bg-slate-100 text-slate-700">
            <span>📄</span>
            <span>کەشف حساب</span>
        </a>
    </div>
@endsection

@section('content')

<div x-data="{ openOldDebtModal: false, currency: '{{ $customer->opening_currency ?: 'IQD' }}', debtStatus: 'debt' }"
     @open-old-debt-modal.window="openOldDebtModal = true">

{{-- ١. هێرۆی سەرەوەی پڕۆفایل (Profile Hero Card) --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6 mb-6">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        {{-- لایەن و ناوی کڕیار --}}
        <div class="flex items-center gap-4">
            <div class="size-16 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 flex items-center justify-center text-3xl font-black text-slate-600 shadow-inner shrink-0">
                👤
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900">{{ $customer->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-rose-600 bg-rose-50 border border-rose-100">
                        C-{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 mt-2 text-xs font-medium text-slate-600">
                    <span class="flex items-center gap-1">
                        <span class="text-slate-400">📱 مۆبایل:</span>
                        <span class="num font-bold text-slate-800" dir="ltr">{{ $customer->phone ?: '—' }}</span>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="flex items-center gap-1">
                        <span class="text-slate-400">📍 ناونیشان:</span>
                        <span class="font-bold text-slate-800">{{ $customer->address ?: '—' }}</span>
                    </span>
                    @if ($customer->note)
                        <span class="text-slate-300">|</span>
                        <span class="flex items-center gap-1">
                            <span class="text-slate-400">📝 تێبینی:</span>
                            <span class="text-slate-700">{{ $customer->note }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- دوگمەی دەستکاری خێرا و حیسابی پێشوو --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('debts.old-debt.create', ['customer_id' => $customer->id]) }}"
               class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-amber-500 hover:bg-amber-600 text-white shadow-xs">
                <span>📜</span>
                <span>حیسابی پێشوو</span>
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-slate-200 hover:bg-slate-100 text-slate-700">
                ✏️ دەستکاری زانیاری
            </a>
        </div>
    </div>

    {{-- کارتەکانی ئاماری تایبەت بەم کڕیارە --}}
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mt-6 pt-6 border-t border-slate-100">
        {{-- حیسابی پێشوو --}}
        <div class="bg-amber-50/70 rounded-xl p-4 border border-amber-200/80 border-r-4 border-r-amber-500 text-center">
            <div class="text-2xl font-black text-amber-900 num">
                {{ fmt_num((float) $customer->opening_balance) }} {{ $customer->opening_currency === 'USD' ? '$' : 'د.ع' }}
            </div>
            <div class="text-xs font-bold text-amber-800 mt-1 flex items-center justify-center gap-1">
                <span>📜</span>
                <span>حیسابی پێشوو</span>
            </div>
        </div>

        {{-- فرۆشتن --}}
        <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 border-r-blue-500 text-center">
            <div class="text-2xl font-black text-slate-900 num">{{ fmt_num($ordersCount) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">فرۆشتن (وەسڵەکان)</div>
        </div>

        {{-- کۆی کڕینەکان --}}
        <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 border-r-emerald-500 text-center">
            <div class="text-2xl font-black text-slate-900 num">{{ fmt_money($totalBought) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">کۆی کڕینەکان</div>
        </div>

        {{-- قەرز --}}
        <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 {{ $balance > 0 ? 'border-r-rose-500 bg-rose-50/20' : 'border-r-emerald-500' }} text-center">
            <div class="text-2xl font-black num {{ $balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ fmt_money($balance) }}
            </div>
            <div class="text-xs font-bold text-slate-500 mt-1">
                {{ $balance > 0 ? 'کۆی گشتی قەرز' : 'حساب پاکە' }}
            </div>
        </div>
    </div>
</div>

{{-- ٢. خشتەی فرۆشتنەکان (Sales / Invoices Table) --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <span>🛒</span>
            <span>فرۆشتنەکان (ئەو شتانەی بردوویەتی)</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full text-right">
            <thead>
                <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                    <th class="py-3 px-4 w-12 text-center">#</th>
                    <th class="py-3 px-4 text-center">وەسڵ</th>
                    <th class="py-3 px-4">ناوەڕۆک / شتەکان</th>
                    <th class="py-3 px-4 text-center">بەروار و کات</th>
                    <th class="py-3 px-4 text-center">کۆی نرخ</th>
                    <th class="py-3 px-4 text-center">دراو</th>
                    <th class="py-3 px-4 text-center">ماوە</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($orders as $index => $order)
                    @php
                        $remaining = $order->remaining();
                        $paid = $order->paidAmount();
                        $itemsSummary = $order->items->pluck('description')->filter()->join('، ');
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer"
                        onclick="if (!event.target.closest('a')) window.open('{{ route('orders.print', $order) }}', '_blank')">
                        <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                            {{ $index + 1 }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('orders.print', $order) }}" target="_blank"
                               class="inline-flex items-center justify-center min-w-8 px-3 py-1 rounded-lg text-xs font-mono font-bold text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200 shadow-2xs hover:shadow-xs transition-all cursor-pointer"
                               title="کرتە بکە بۆ کردنەوە و چاپی وەسڵ">
                                {{ $order->invoice_no }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('orders.print', $order) }}" target="_blank" class="group block cursor-pointer">
                                <div class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">{{ $itemsSummary ?: 'وەسڵی فرۆشتن' }}</div>
                                @if ($order->items->count() > 0)
                                    <div class="text-xs text-slate-400 mt-0.5 font-normal">
                                        {{ $order->items->count() }} بەندە / شت
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-center num text-xs text-slate-600">
                            {{ fmt_date($order->order_date) }}
                        </td>
                        <td class="py-3.5 px-4 text-center num font-bold text-slate-900">
                            {{ fmt_money($order->total, $order->currency) }}
                        </td>
                        <td class="py-3.5 px-4 text-center num font-semibold text-emerald-700">
                            {{ fmt_money($paid, $order->currency) }}
                        </td>
                        <td class="py-3.5 px-4 text-center num font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $remaining > 0 ? fmt_money($remaining, $order->currency) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400 text-sm font-medium">
                            هیچ وەسڵێکی فرۆشتن بۆ ئەم کڕیارە تۆمار نەکراوە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ٣. لیستی حیسابی پێشوو (Old Debts / Prior Accounts Table) --}}
@if ($oldDebts->count() > 0)
<div class="bg-white rounded-2xl shadow-xs border border-amber-200/80 overflow-hidden mb-6">
    <div class="p-4 border-b border-amber-100 flex items-center justify-between bg-amber-50/40">
        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <span>📜</span>
            <span>حیسابی پێشوو</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 font-mono">
                {{ $oldDebts->count() }} تۆمار
            </span>
        </div>
        <a href="{{ route('debts.old-debt.create', ['customer_id' => $customer->id]) }}"
           class="btn !py-1 !px-3 text-xs font-bold gap-1 bg-amber-600 hover:bg-amber-700 text-white shadow-xs">
            <span>+</span>
            <span>زیادکردن</span>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full text-right text-xs">
            <thead>
                <tr class="text-slate-500 border-b border-slate-100 bg-slate-50/60 font-bold">
                    <th class="py-3 px-4 w-12 text-center">#</th>
                    <th class="py-3 px-4 text-center">بەروار</th>
                    <th class="py-3 px-4 text-center">بڕی پارە</th>
                    <th class="py-3 px-4 text-center">دۆخی پارەدان</th>
                    <th class="py-3 px-4 text-center">وێنەی بەڵگە / وەسڵ</th>
                    <th class="py-3 px-4">تێبینی</th>
                    <th class="py-3 px-4 text-center w-24">کردار</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($oldDebts as $index => $debt)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 text-center font-mono font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 text-center num text-slate-600 font-bold">{{ fmt_date($debt->date) }}</td>
                        <td class="py-3 px-4 text-center num font-black text-sm {{ $debt->status === 'paid' ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ fmt_money($debt->amount, $debt->currency) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if ($debt->status === 'paid')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    ✓ پارەدانی تەواو بووە
                                </span>
                            @elseif ($debt->status === 'partial')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    ماوە: {{ fmt_money($debt->remaining(), $debt->currency) }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    ⚠️ قەرز (نەدراوە)
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if ($debt->image)
                                <a href="{{ asset('storage/' . $debt->image) }}" target="_blank" class="inline-block group" title="کلیک بکە بۆ بینینی وێنەی گەورە">
                                    <img src="{{ asset('storage/' . $debt->image) }}" alt="بەڵگە" class="size-10 rounded-lg object-cover border border-slate-200 shadow-xs group-hover:scale-110 transition-all">
                                </a>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">
                            {{ $debt->note ?: '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('debts.old-debt.destroy', $debt) }}"
                                  onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم حیسابە؟')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 cursor-pointer" title="سڕینەوە">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- مۆداڵی تۆمارکردنی حیسابی پێشوو --}}
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
                <span>تۆمارکردنی حیسابی پێشوو بۆ ({{ $customer->name }})</span>
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
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

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

            {{-- دوگمەکانی کردار --}}
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
