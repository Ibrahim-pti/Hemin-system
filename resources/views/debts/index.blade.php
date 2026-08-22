@extends('layouts.app')
@section('title', 'قەرزەکان')

@section('content')
<div x-data="debtsPage(@js($customers))" class="space-y-5">

    {{-- ١. بەشی سەرەوە (سەردێڕ و دوگمەی زیادکردنی قەرزی کۆن) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center shadow-xs">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">قەرزەکان</h1>
        </div>

        <div>
            <button type="button" @click="openOldDebtModal = true"
                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-xs inline-flex items-center gap-2 transition-all">
                <span>➕</span>
                <span>قەرزی کۆن زیادبکە</span>
            </button>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکانی سەرەوە (٣ کارت وەک وێنەکە) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- کارتی ١: کۆی قەرزی ماوە --}}
        <div class="bg-white rounded-2xl p-5 border border-rose-200/80 shadow-xs relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight num">
                    {{ fmt_num($totalRemainingDebt) }}
                </div>
                <div class="text-xs sm:text-sm font-bold text-slate-500">کۆی قەرزی ماوە</div>
            </div>
            <div class="size-12 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٢: کۆی پارەی دراو --}}
        <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-xs relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight num">
                    {{ fmt_num($totalPaid) }}
                </div>
                <div class="text-xs sm:text-sm font-bold text-slate-500">کۆی پارەی دراو</div>
            </div>
            <div class="size-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٣: قەرزداری چالاک --}}
        <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-xs relative overflow-hidden flex items-center justify-between">
            <div class="space-y-1">
                <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight num">
                    {{ fmt_num($activeDebtorsCount) }}
                </div>
                <div class="text-xs sm:text-sm font-bold text-slate-500">قەرزداری چالاک</div>
            </div>
            <div class="size-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ٣. خشتەی سەرەکی قەرزدارەکان --}}
    <div class="card overflow-hidden">
        <div class="card-head flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 !py-3.5">
            <div class="flex items-center gap-2">
                <span class="text-base font-bold text-slate-800">👥 قەرزدارەکان</span>
            </div>

            {{-- خانەی گەڕان --}}
            <div class="relative w-full sm:w-72">
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="گەڕان..."
                       class="field !py-1.5 !pr-9 !pl-3 text-xs w-full bg-slate-50 focus:bg-white transition-all rounded-lg border-slate-200">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-600 text-xs">
                        <th class="w-12 text-center">#</th>
                        <th class="text-right">کڕیار</th>
                        <th class="text-center">تەلەفۆن</th>
                        <th class="text-center">ژمارەی قەرز</th>
                        <th class="num text-center">کۆی بڕ</th>
                        <th class="num text-center">دراو</th>
                        <th class="num text-center">قەرزی ماوە</th>
                        <th class="w-20 text-center">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <template x-for="(row, index) in filteredCustomers" :key="row.id">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- # --}}
                            <td class="text-center text-xs font-bold text-slate-400 num" x-text="index + 1"></td>

                            {{-- کڕیار --}}
                            <td>
                                <a :href="'/customers/' + row.id + '/statement'"
                                   class="inline-flex items-center gap-1.5 font-bold hover:underline"
                                   :class="row.remaining > 0 ? 'text-red-700' : 'text-slate-800'">
                                    <span class="inline-flex items-center justify-center size-5 rounded-full bg-red-100 text-red-700 text-xs shrink-0 font-normal">
                                        👤
                                    </span>
                                    <span x-text="row.name"></span>
                                </a>
                            </td>

                            {{-- تەلەفۆن --}}
                            <td class="text-center num text-slate-600 text-xs" dir="ltr" x-text="row.phone || '—'"></td>

                            {{-- ژمارەی قەرز --}}
                            <td class="text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-100/80 num"
                                          x-text="row.orders_count + ' قەرز'">
                                    </span>
                                    <template x-if="row.active_orders_count > 0">
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-100/80 num"
                                              x-text="row.active_orders_count + ' چالاک'">
                                        </span>
                                    </template>
                                </div>
                            </td>

                            {{-- کۆی بڕ --}}
                            <td class="text-center num font-bold text-slate-700" x-text="formatNumber(row.total_amount)"></td>

                            {{-- دراو --}}
                            <td class="text-center num font-bold text-emerald-600" x-text="formatNumber(row.total_paid)"></td>

                            {{-- قەرزی ماوە --}}
                            <td class="text-center">
                                <template x-if="row.remaining > 0.5">
                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-black bg-rose-50 text-rose-600 border border-rose-100 num"
                                          x-text="formatNumber(row.remaining)">
                                    </span>
                                </template>
                                <template x-if="row.remaining <= 0.5">
                                    <span class="inline-block px-3 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        تەواو دراوە
                                    </span>
                                </template>
                            </td>

                            {{-- کردار --}}
                            <td class="text-center">
                                <a :href="'/customers/' + row.id + '/statement'"
                                   class="inline-flex items-center justify-center size-8 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition-all shadow-2xs"
                                   title="بینینی کەشف حساب و پسوولەکان">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredCustomers.length === 0" x-cloak>
                        <td colspan="8" class="py-10 text-center text-sm text-slate-400">
                            هیچ قەرزدارێک نەدۆزرایەوە بەم گەڕانە.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ٤. مۆداڵی زیادکردنی قەرزی کۆن --}}
    <div x-show="openOldDebtModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="openOldDebtModal = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-slate-200 overflow-hidden transform transition-all"
             @click.outside="openOldDebtModal = false">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💳</span>
                    <h3 class="font-bold text-slate-800">زیادکردنی قەرزی کۆن (باڵانسی سەرەتایی)</h3>
                </div>
                <button type="button" @click="openOldDebtModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('debts.old-debt') }}" class="p-6 space-y-4">
                @csrf

                {{-- هەڵبژاردنی کڕیار یان کڕیاری نوێ --}}
                <div x-data="{ isNewCustomer: false }">
                    <label class="label">کڕیار <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-2 mb-2">
                        <select name="customer_id" class="field font-bold flex-1"
                                x-show="!isNewCustomer"
                                :required="!isNewCustomer">
                            <option value="">— کڕیارێک هەڵبژێرە —</option>
                            @foreach ($allCustomersList as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="isNewCustomer = !isNewCustomer"
                                class="btn btn-ghost !py-2 text-xs border border-slate-300 font-bold whitespace-nowrap">
                            <span x-text="isNewCustomer ? '👈 هەڵبژاردنی کڕیار لە لیست' : '➕ کڕیاری نوێ'"></span>
                        </button>
                    </div>

                    {{-- ئەگەر کڕیاری نوێ بێت --}}
                    <div x-show="isNewCustomer" x-cloak class="space-y-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                        <div>
                            <label class="label text-xs">ناوی کڕیاری نوێ <span class="text-rose-500">*</span></label>
                            <input type="text" name="new_customer_name" class="field text-sm" placeholder="ناوی تەواوی کڕیار..."
                                   :required="isNewCustomer">
                        </div>
                        <div>
                            <label class="label text-xs">ژمارەی مۆبایل</label>
                            <input type="text" name="new_customer_phone" class="field text-sm num" placeholder="0750...">
                        </div>
                    </div>
                </div>

                {{-- بڕی قەرز و دراو --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="amount">بڕی قەرز <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="0.01" id="amount" name="amount" class="field num font-bold" placeholder="0" required>
                    </div>
                    <div>
                        <label class="label" for="currency">دراو <span class="text-rose-500">*</span></label>
                        <select id="currency" name="currency" class="field font-bold">
                            <option value="IQD">دینار (IQD)</option>
                            <option value="USD">دۆلار ($ USD)</option>
                        </select>
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="label" for="note">تێبینی</label>
                    <input type="text" id="note" name="note" class="field text-sm" placeholder="هۆکاری قەرز، بەرواری کۆن، ...">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openOldDebtModal = false" class="btn btn-ghost">
                        پەشیمانبوونەوە
                    </button>
                    <button type="submit" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700">
                        تۆمارکردنی قەرز
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function debtsPage(initialCustomers) {
    return {
        customers: initialCustomers,
        searchQuery: '',
        openOldDebtModal: false,

        get filteredCustomers() {
            if (!this.searchQuery.trim()) {
                return this.customers;
            }
            const q = this.searchQuery.toLowerCase().trim();
            return this.customers.filter(c => {
                const name = (c.name || '').toLowerCase();
                const phone = (c.phone || '').toLowerCase();
                return name.includes(q) || phone.includes(q);
            });
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            return Number(val).toLocaleString('en-US');
        }
    }
}
</script>
@endsection
