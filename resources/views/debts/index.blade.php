@extends('layouts.app')
@section('title', 'قەرزەکان')

@section('content')
<div x-data="debtsPage(@js($customers))" style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. بەشی سەرەوە: سەردێڕ و دوگمەی زیادکردنی قەرزی کۆن --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        {{-- لای ڕاست: ناونیشان و ئایکۆن --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">قەرزەکان</h1>
        </div>

        {{-- لای چەپ: دوگمەی قەرزی کۆن --}}
        <div>
            <button type="button" @click="openOldDebtModal = true"
                    style="background: #4f46e5; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); transition: all 0.2s;">
                <span style="font-size: 1rem; font-weight: bold;">+</span>
                <span>قەرزی کۆن زیادبکە</span>
            </button>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکانی سەرەوە (٣ کارت لە تەنیشت یەک وەک وێنەکە) --}}
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">

        {{-- کارتی ١ (دەستە ڕاست): کۆی قەرزی ماوە --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #fecdd3; border-right: 4px solid #f43f5e; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalRemainingDebt) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی قەرزی ماوە
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #ffe4e6; color: #f43f5e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٢ (ناوەڕاست): کۆی پارەی دراو --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #a7f3d0; border-right: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalPaid) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی پارەی دراو
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #d1fae5; color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="16 9 10 15 8 13"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٣ (دەستە چەپ): قەرزداری چالاک --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #fde68a; border-right: 4px solid #f59e0b; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($activeDebtorsCount) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    قەرزداری چالاک
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #fef3c7; color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ٣. کارتی سەرەکی خشتەی قەرزدارەکان --}}
    <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">

        {{-- سەرپەڕەی خشتە: ناونیشان و گەڕان --}}
        <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f8fafc; flex-wrap: wrap; gap: 1rem;">
            {{-- دەستە ڕاست --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem; color: #1e293b;">
                <svg style="width: 1.25rem; height: 1.25rem; color: #475569;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>قەرزدارەکان</span>
            </div>

            {{-- دەستە چەپ: گەڕان --}}
            <div style="position: relative; width: 16rem; max-width: 100%;">
                <input type="text"
                       x-model="searchQuery"
                       placeholder="گەڕان..."
                       style="width: 100%; padding: 0.5rem 2.25rem 0.5rem 1rem; font-size: 0.8rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; color: #1e293b; outline: none; transition: border-color 0.15s;"
                       onfocus="this.style.borderColor='#6366f1'"
                       onblur="this.style.borderColor='#e2e8f0'">
                <div style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; display: flex; align-items: center;">
                    <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- خشتەی سەرەکی --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                        <th style="padding: 0.75rem 1rem; width: 3.5rem; text-align: center;">#</th>
                        <th style="padding: 0.75rem 1.25rem; text-align: right;">کڕیار</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">تەلەفۆن</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">ژمارەی قەرز</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">کۆی بڕ</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">دراو</th>
                        <th style="padding: 0.75rem 1.25rem; text-align: center;">قەرزی ماوە</th>
                        <th style="padding: 0.75rem 1rem; width: 4.5rem; text-align: center;">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in filteredCustomers" :key="row.id">
                        <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fbfcfd'"
                            onmouseout="this.style.background='transparent'">

                            {{-- # --}}
                            <td class="num" style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;" x-text="index + 1"></td>

                            {{-- کڕیار بە ناوی سوور و ئایکۆنی ڕەساسی/سوور --}}
                            <td style="padding: 1rem 1.25rem; text-align: right;">
                                <a :href="'/customers/' + row.id + '/statement'"
                                   style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; color: #dc2626; font-weight: 700; font-size: 0.9rem;">
                                    <svg style="width: 1.1rem; height: 1.1rem; color: #dc2626; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span x-text="row.name"></span>
                                </a>
                            </td>

                            {{-- تەلەفۆن --}}
                            <td class="num" dir="ltr" style="padding: 1rem; text-align: center; color: #334155; font-size: 0.85rem; font-weight: 500;" x-text="row.phone || '—'"></td>

                            {{-- ژمارەی قەرز: باجی شین + باجی سووری چالاک وەک وێنەکە --}}
                            <td style="padding: 1rem; text-align: center;">
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center;">
                                    {{-- باجی شین --}}
                                    <span class="num" style="background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 0.375rem; font-size: 0.72rem; font-weight: 700; display: inline-block; white-space: nowrap;"
                                          x-text="row.orders_count + ' قەرز'">
                                    </span>
                                    {{-- باجی سوور ئەگەر وەسڵی نەدراوی هەبێت --}}
                                    <template x-if="row.active_orders_count > 0">
                                        <span class="num" style="background: #ffe4e6; color: #e11d48; padding: 0.2rem 0.55rem; border-radius: 0.375rem; font-size: 0.72rem; font-weight: 700; display: inline-block; white-space: nowrap;"
                                              x-text="row.active_orders_count + ' چالاک'">
                                        </span>
                                    </template>
                                </div>
                            </td>

                            {{-- کۆی بڕ --}}
                            <td class="num" style="padding: 1rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.88rem;" x-text="formatNumber(row.total_amount)"></td>

                            {{-- دراو --}}
                            <td class="num" style="padding: 1rem; text-align: center; font-weight: 700; font-size: 0.88rem;"
                                :style="{ color: row.total_paid > 0 ? '#10b981' : '#64748b' }"
                                x-text="formatNumber(row.total_paid)">
                            </td>

                            {{-- قەرزی ماوە: باجی سووری پان یان سەوزی تەواو دراوە --}}
                            <td style="padding: 1rem 1.25rem; text-align: center;">
                                <template x-if="row.remaining > 0.5">
                                    <span class="num" style="background: #fee2e2; color: #dc2626; font-weight: 800; padding: 0.25rem 0.85rem; border-radius: 0.375rem; font-size: 0.78rem; display: inline-block;"
                                          x-text="formatNumber(row.remaining)">
                                    </span>
                                </template>
                                <template x-if="row.remaining <= 0.5">
                                    <span style="background: #dcfce7; color: #16a34a; font-weight: 800; padding: 0.25rem 0.85rem; border-radius: 0.375rem; font-size: 0.75rem; display: inline-block;">
                                        تەواو دراوە
                                    </span>
                                </template>
                            </td>

                            {{-- کردار: ئایکۆنی چاوی زەرد/پڕتەقاڵی لەناو چوارگۆشەی خڕ --}}
                            <td style="padding: 1rem; text-align: center;">
                                <a :href="'/customers/' + row.id + '/statement'"
                                   style="width: 2.1rem; height: 2.1rem; border-radius: 0.5rem; background: #fef3c7; color: #d97706; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.15s;"
                                   onmouseover="this.style.background='#fde68a'"
                                   onmouseout="this.style.background='#fef3c7'"
                                   title="کەشف حساب">
                                    <svg style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredCustomers.length === 0" x-cloak>
                        <td colspan="8" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.875rem;">
                            هیچ کڕیارێک یان قەرزدارێک نەدۆزرایەوە.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    {{-- ٤. مۆداڵی زیادکردنی قەرزی کۆن --}}
    <div x-show="openOldDebtModal"
         x-cloak
         style="position: fixed; inset: 0; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
         @keydown.escape.window="openOldDebtModal = false">
        <div style="background: #ffffff; border-radius: 1rem; width: 100%; max-width: 32rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden;"
             @click.outside="openOldDebtModal = false">

            <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: #1e293b;">
                    <span style="color: #4f46e5;">💳</span>
                    <span>زیادکردنی قەرزی کۆن (باڵانسی سەرەتایی)</span>
                </div>
                <button type="button" @click="openOldDebtModal = false" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('debts.old-debt') }}" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                {{-- کڕیار --}}
                <div x-data="{ isNewCustomer: false }">
                    <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">کڕیار <span style="color: #ef4444;">*</span></label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <select name="customer_id" class="field" style="font-weight: 700; flex: 1;"
                                x-show="!isNewCustomer"
                                :required="!isNewCustomer">
                            <option value="">— کڕیارێک هەڵبژێرە —</option>
                            @foreach ($allCustomersList as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="isNewCustomer = !isNewCustomer"
                                style="padding: 0.4rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #ffffff; font-size: 0.75rem; font-weight: 700; color: #475569; cursor: pointer; white-space: nowrap;">
                            <span x-text="isNewCustomer ? '👈 هەڵبژاردنی کڕیار' : '➕ کڕیاری نوێ'"></span>
                        </button>
                    </div>

                    {{-- کڕیاری نوێ --}}
                    <div x-show="isNewCustomer" x-cloak style="padding: 0.75rem; background: #f0fdf4; border-radius: 0.5rem; border: 1px solid #bbf7d0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: #166534; display: block; margin-bottom: 0.25rem;">ناوی کڕیار <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="new_customer_name" class="field" style="font-size: 0.8rem;" placeholder="ناوی تەواو..." :required="isNewCustomer">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: #166534; display: block; margin-bottom: 0.25rem;">ژمارەی مۆبایل</label>
                            <input type="text" name="new_customer_phone" class="field num" style="font-size: 0.8rem;" placeholder="0750...">
                        </div>
                    </div>
                </div>

                {{-- بڕی پارە و دراو --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">بڕی قەرز <span style="color: #ef4444;">*</span></label>
                        <input type="number" step="any" min="0.01" name="amount" class="field num font-bold" placeholder="0" required>
                    </div>
                    <div>
                        <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">دراو <span style="color: #ef4444;">*</span></label>
                        <select name="currency" class="field" style="font-weight: 700;">
                            <option value="IQD">دینار (IQD)</option>
                            <option value="USD">دۆلار ($ USD)</option>
                        </select>
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">تێبینی</label>
                    <input type="text" name="note" class="field" style="font-size: 0.85rem;" placeholder="تێبینی گشتی قەرز...">
                </div>

                {{-- دوگمەکان --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <button type="button" @click="openOldDebtModal = false"
                            style="padding: 0.5rem 1rem; border-radius: 0.5rem; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer;">
                        داخستن
                    </button>
                    <button type="submit"
                            style="padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer;">
                        تۆمارکردن
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
