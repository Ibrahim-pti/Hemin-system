@extends('layouts.app')
@section('title', 'کڕیارەکان و وەسڵ')

@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('orders.create') }}" class="btn btn-primary !py-1.5 !px-3 text-xs gap-1 shadow-sm">
            <span>+ وەسڵی نوێ</span>
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100">
            <span>+ کڕیاری نوێ</span>
        </a>
    </div>
@endsection

@section('content')

{{-- کارتەکانی ئاماری خێرا --}}
<div class="grid gap-3 sm:grid-cols-3 mb-4">
    <div class="card p-3.5 bg-white">
        <div class="text-xs text-[--color-ink-soft]">کۆی گشتی کڕیاران</div>
        <div class="text-xl font-bold text-slate-900 num mt-0.5">{{ fmt_num($totalCustomers) }}</div>
        <div class="text-xs text-slate-500 mt-1">کڕیاری تۆمارکراو</div>
    </div>

    <div class="card p-3.5 bg-white">
        <div class="text-xs text-[--color-ink-soft]">کۆی وەسڵ و داواکارییەکان</div>
        <div class="text-xl font-bold text-slate-900 num mt-0.5">{{ fmt_num($totalOrders) }}</div>
        <div class="text-xs text-slate-500 mt-1">
            <a href="{{ route('orders.index') }}" class="text-[--color-brand-700] hover:underline font-semibold">بینینی هەموو وەسڵەکان &larr;</a>
        </div>
    </div>

    <div class="card p-3.5 bg-white {{ $totalDebt > 0 ? 'border-rose-200 bg-rose-50/20' : '' }}">
        <div class="text-xs text-[--color-ink-soft]">کۆی قەرزی لای کڕیاران</div>
        <div class="text-xl font-bold num mt-0.5 {{ $totalDebt > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
            {{ fmt_money($totalDebt) }}
        </div>
        <div class="text-xs mt-1 font-medium {{ $totalDebt > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
            {{ $totalDebt > 0 ? 'قەرزی ماوەی لای کڕیارەکان' : 'حساب پاکە' }}
        </div>
    </div>
</div>

{{-- بەستەری تابەکان بۆ هەڵبژاردنی نێوان کڕیارەکان و وەسڵەکان --}}
<div class="flex items-center gap-1 border-b border-[--color-line] mb-4">
    <a href="{{ route('customers.index') }}"
       class="px-4 py-2.5 text-sm font-bold border-b-2 border-[--color-brand-700] text-[--color-brand-700] bg-white rounded-t-lg">
        لیستی کڕیارەکان ({{ $totalCustomers }})
    </a>
    <a href="{{ route('orders.index') }}"
       class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-t-lg transition-colors">
        هەموو وەسڵەکان ({{ $totalOrders }})
    </a>
</div>

{{-- فۆرمی گەڕان --}}
<form method="GET" class="card mb-4">
    <div class="card-body flex gap-3">
        <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="گەڕان بەپێی ناوی کڕیار یان ژمارەی تەلەفۆن...">
        <button class="btn btn-primary">گەڕان</button>
        @if(request('q'))
            <a href="{{ route('customers.index') }}" class="btn btn-ghost">پاککردنەوە</a>
        @endif
    </div>
</form>

{{-- خشتەی کڕیارەکان --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr class="bg-slate-50/80 text-xs text-slate-700">
                    <th class="text-right py-3 px-4">ناوی کڕیار</th>
                    <th class="text-right py-3 px-4">ژمارەی تەلەفۆن</th>
                    <th class="num text-right py-3 px-4">ژمارەی وەسڵ</th>
                    <th class="num text-right py-3 px-4">قەرزی ئێستا</th>
                    <th class="text-left py-3 px-4 w-48">کردارەکان</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($customers as $customer)
                    @php $balance = $customer->balance(); @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="text-right py-3 px-4 font-bold">
                            <a href="{{ route('customers.show', $customer) }}" class="text-slate-900 hover:text-[--color-brand-700] transition-colors">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td class="text-right py-3 px-4 num text-slate-600" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                        <td class="text-right py-3 px-4 num font-medium text-slate-700">{{ fmt_num($customer->orders_count) }} وەسڵ</td>
                        <td class="text-right py-3 px-4 num font-bold {{ $balance > 0 ? 'text-[--color-danger]' : ($balance < 0 ? 'text-[--color-brand-700]' : 'text-[--color-ok]') }}">
                            {{ fmt_money($balance) }}
                        </td>
                        <td class="text-left py-3 px-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('orders.create', ['customer' => $customer->id]) }}"
                                   class="btn btn-primary !py-1 !px-2.5 text-xs font-bold shadow-2xs"
                                   title="وەسڵی نوێ بۆ ئەم کڕیارە">
                                    + وەسڵ
                                </a>
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="btn btn-ghost !py-1 !px-2 text-xs border border-slate-200 hover:bg-slate-100"
                                   title="بینینی هەموو مامەڵەکان">
                                    پرۆفایل
                                </a>
                                <a href="{{ route('customers.statement', $customer) }}"
                                   class="btn btn-ghost !py-1 !px-2 text-xs text-[--color-brand-700] hover:bg-blue-50"
                                   title="کەشف حسابی تەواو">
                                    کەشف حساب
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ کڕیارێک نەدۆزرایەوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $customers->links() }}</div>

@endsection
