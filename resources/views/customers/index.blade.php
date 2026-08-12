@extends('layouts.app')
@section('title', 'کڕیارەکان')

@section('actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">کڕیاری نوێ</a>
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body flex gap-3">
        <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناو، تەلەفۆن یان شوێن...">
        <button class="btn btn-primary">گەڕان</button>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناو</th>
                    <th>تەلەفۆن</th>
                    <th>شوێن</th>
                    <th class="num">وەسڵ</th>
                    <th class="num">داشکاندن</th>
                    <th class="num">قەرز</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    @php $balance = $customer->balance(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="font-medium text-[--color-brand-700]">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td class="num" dir="ltr">{{ $customer->phone ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $customer->address ?? '—' }}</td>
                        <td class="num">{{ fmt_num($customer->orders_count) }}</td>
                        <td class="num text-[--color-ink-soft]">
                            {{ $customer->discount_percent > 0 ? fmt_num($customer->discount_percent, 2).'٪' : '—' }}
                        </td>
                        <td class="num font-medium {{ $balance > 0 ? 'text-[--color-danger]' : '' }}">
                            {{ fmt_money($balance) }}
                        </td>
                        <td class="whitespace-nowrap text-left">
                            <a href="{{ route('customers.statement', $customer) }}" class="text-sm text-[--color-brand-700]">کەشف حساب</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ کڕیارێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $customers->links() }}</div>

@endsection
