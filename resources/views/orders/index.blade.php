@extends('layouts.app')
@section('title', 'وەسڵ و داواکاری')

@section('actions')
    <a href="{{ route('orders.create') }}" class="btn btn-primary">وەسڵی نوێ</a>
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
                    <th>بەڕێز</th>
                    <th class="num">کۆی گشتی</th>
                    <th class="num">ماوە</th>
                    <th>دۆخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    @php $remaining = $order->remaining(); @endphp
                    <tr>
                        <td class="num font-medium">{{ $order->invoice_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($order->order_date) }}</td>
                        <td>
                            {{ $order->customer?->name }}
                            @if ($order->customer->phone)
                                <span class="num block text-xs text-[--color-ink-soft]" dir="ltr">{{ $order->customer->phone }}</span>
                            @endif
                        </td>
                        <td class="num">{{ fmt_money($order->total, $order->currency) }}</td>
                        <td class="num {{ $remaining > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($remaining) }}
                        </td>
                        <td>
                            <span class="badge {{ match ($order->status) {
                                'delivered' => 'badge-ok',
                                'cancelled' => 'badge-danger',
                                default => 'badge-warn',
                            } }}">{{ $order->status_label }}</span>
                        </td>
                        <td class="whitespace-nowrap text-left">
                            <a href="{{ route('orders.show', $order) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                            <a href="{{ route('orders.print', $order) }}" target="_blank"
                               class="mr-2 text-sm text-[--color-brand-700]">چاپ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ وەسڵێک نەدۆزرایەوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $orders->links() }}</div>

@endsection
