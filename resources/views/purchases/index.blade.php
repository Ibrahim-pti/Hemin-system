@extends('layouts.app')
@section('title', 'پسوولەکانی کڕین')

@section('actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">پسوولەی نوێ</a>
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="label">گەڕان</label>
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ژمارەی پسوولە یان فرۆشیار...">
        </div>
        <div>
            <label class="label">دۆخ</label>
            <select name="status" class="field">
                <option value="">هەموو</option>
                @foreach (\App\Models\Purchase::STATUSES as $key => $label)
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
                    <th>فرۆشیار</th>
                    <th>کۆگا</th>
                    <th class="num">کۆی گشتی</th>
                    <th class="num">ماوە</th>
                    <th>دۆخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    @php $remaining = $purchase->remaining(); @endphp
                    <tr>
                        <td class="num font-medium">{{ $purchase->invoice_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($purchase->purchase_date) }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td class="text-[--color-ink-soft]">{{ $purchase->warehouse->name }}</td>
                        <td class="num">{{ fmt_money($purchase->total, $purchase->currency) }}</td>
                        <td class="num {{ $remaining > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($remaining) }}
                        </td>
                        <td>
                            <span class="badge {{ $purchase->status === 'confirmed' ? 'badge-ok' : 'badge-warn' }}">
                                {{ \App\Models\Purchase::STATUSES[$purchase->status] }}
                            </span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-sm text-[--color-brand-700]">بینین</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ پسوولەیەک نەدۆزرایەوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $purchases->links() }}</div>

@endsection
