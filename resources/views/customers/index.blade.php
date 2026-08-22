@extends('layouts.app')
@section('title', 'کڕیاران')

@section('actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary !py-2 !px-4 text-xs font-bold gap-1.5 shadow-sm bg-blue-600 hover:bg-blue-700">
        <span>+</span>
        <span>زیادکردنی کڕیار</span>
    </a>
@endsection

@section('content')

{{-- ٤ کارتە سەرەکییەکەی ئامار (هاوشێوەی دیزاینە نوێیەکە) --}}
<div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    {{-- کۆی کڕیاران --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-blue-500 text-center relative overflow-hidden">
        <div class="text-3xl font-black text-slate-800 num tracking-tight">{{ fmt_num($totalCustomers) }}</div>
        <div class="text-xs font-bold text-slate-500 mt-1">کۆی کڕیاران</div>
    </div>

    {{-- کۆی فرۆشتن --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-emerald-500 text-center relative overflow-hidden">
        <div class="text-2xl lg:text-3xl font-black text-slate-800 num tracking-tight">{{ fmt_money($totalSales) }}</div>
        <div class="text-xs font-bold text-slate-500 mt-1">کۆی فرۆشتن</div>
    </div>

    {{-- کۆی قەرز --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-rose-500 text-center relative overflow-hidden">
        <div class="text-2xl lg:text-3xl font-black text-rose-600 num tracking-tight">{{ fmt_money($totalDebt) }}</div>
        <div class="text-xs font-bold text-slate-500 mt-1">کۆی قەرز</div>
    </div>

    {{-- کڕیاری قەرزدار --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-amber-400 text-center relative overflow-hidden">
        <div class="text-3xl font-black text-amber-500 num tracking-tight">{{ fmt_num($debtorCount) }}</div>
        <div class="text-xs font-bold text-slate-500 mt-1">کڕیاری قەرزدار</div>
    </div>
</div>

{{-- کارتی لیستی کڕیاران --}}
<div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <span>📋</span>
            <span>لیستی کڕیاران</span>
        </div>

        {{-- خانەی گەڕان --}}
        <form method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="search" name="q" value="{{ request('q') }}"
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                       placeholder="گەڕان...">
                <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full text-right">
            <thead>
                <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                    <th class="py-3 px-4 w-12 text-center">#</th>
                    <th class="py-3 px-4 text-center">ژمارەی کڕیار</th>
                    <th class="py-3 px-4">ناو</th>
                    <th class="py-3 px-4 text-center">ژ. مۆبایل</th>
                    <th class="py-3 px-4 text-center w-36">کردار</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($customers as $index => $customer)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                            {{ $customers->firstItem() + $index }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-rose-500 bg-rose-50/60 border border-rose-100">
                                C-{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-800 hover:text-blue-600 transition-colors">
                                {{ $customer->name }}
                            </a>
                            @if ($customer->note)
                                <span class="block text-xs text-slate-400 font-normal">{{ Str::limit($customer->note, 30) }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center num font-medium text-slate-600" dir="ltr">
                            {{ $customer->phone ?: '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- پڕۆفایل --}}
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="size-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-colors shadow-2xs"
                                   title="پڕۆفایلی کڕیار">
                                    👤
                                </a>
                                {{-- دەستکاری --}}
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="size-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-colors shadow-2xs"
                                   title="دەستکاری">
                                    ✏️
                                </a>
                                {{-- سڕینەوە --}}
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                      onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم کڕیارە؟')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="size-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors shadow-2xs"
                                            title="سڕینەوە">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400 text-sm font-medium">
                            هیچ کڕیارێک نەدۆزرایەوە.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@endsection
