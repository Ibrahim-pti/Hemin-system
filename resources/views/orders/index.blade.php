@extends('layouts.app')
@section('title', 'فرۆشتن (وەسڵەکان)')

@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('orders.create') }}" class="btn btn-primary !py-2 !px-4 text-xs font-bold gap-1.5 shadow-sm bg-blue-600 hover:bg-blue-700">
            <span>+</span>
            <span>وەسڵی نوێ (فرۆشتن)</span>
        </a>
    </div>
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="label">گەڕان</label>
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ژمارەی وەسڵ، ناو یان تەلەفۆن...">
        </div>
        <div>
            <label class="label">دۆخ</label>
            <select name="status" class="field">
                <option value="">هەموو</option>
                @foreach (\App\Models\Order::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ request('from') }}" class="field num">
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="label">تا بەرواری</label>
                <input type="date" name="to" value="{{ request('to') }}" class="field num">
            </div>
            <button class="btn btn-primary">پاڵاوتن</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ژمارە</th>
                    <th>بەروار</th>
                    <th>بەڕێز (کڕیار)</th>
                    <th class="num">کۆی گشتی</th>
                    <th class="num">دراوە</th>
                    <th class="num">ماوە (قەرز)</th>
                    <th>دۆخی پارەدان</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php
                        $remaining = $order->remaining();
                        $paid = $order->paidAmount();
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="num font-bold text-slate-800">{{ $order->invoice_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($order->order_date) }}</td>
                        <td>
                            @if ($order->customer)
                                <a href="{{ route('customers.show', $order->customer) }}" class="font-bold text-slate-900 hover:text-blue-600">
                                    {{ $order->customer->name }}
                                </a>
                                @if ($order->customer->phone)
                                    <span class="num block text-xs text-slate-500" dir="ltr">{{ $order->customer->phone }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="num font-bold">{{ fmt_money($order->total, $order->currency) }}</td>
                        <td class="num text-emerald-700 font-semibold">{{ fmt_money($paid, $order->currency) }}</td>
                        <td class="num font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ fmt_money($remaining, $order->currency) }}
                        </td>
                        <td>
                            @if ($remaining <= 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span>✓</span> <span>تەواوبوو (کاش)</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span>ماوە:</span> <span class="num">{{ fmt_money($remaining) }}</span>
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-left">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost !py-1 !px-2 text-xs font-bold text-blue-700 hover:bg-blue-50">بینین</a>
                            <a href="{{ route('orders.print', $order) }}" target="_blank"
                               class="btn btn-ghost !py-1 !px-2 text-xs font-bold text-slate-700 hover:bg-slate-100 mr-1">چاپ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ وەسڵێک نەدۆزرایەوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $orders->links() }}</div>

@endsection
