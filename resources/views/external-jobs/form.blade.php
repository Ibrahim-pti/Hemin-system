@extends('layouts.app')
@section('title', $job->exists ? 'دەستکاری ئیشی خاریجی' : 'ئیشی خاریجی نوێ')

@section('content')

<form method="POST"
      action="{{ $job->exists ? route('external-jobs.update', $job) : route('external-jobs.store') }}"
      class="mx-auto max-w-3xl" x-data="{ currency: '{{ old('currency', $job->currency ?: 'IQD') }}' }">
    @csrf
    @if ($job->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-head">زانیاری ئیش</div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label class="label" for="title">ناونیشان <span class="text-[--color-danger]">*</span></label>
                <input id="title" name="title" class="field" required value="{{ old('title', $job->title) }}"
                       placeholder="بۆ نموونە: بۆیاخکردنی دەرگا">
            </div>

            <div>
                <label class="label" for="supplier_id">کرێکار (فرۆشیاری تۆمارکراو)</label>
                <select id="supplier_id" name="supplier_id" class="field">
                    <option value="">— هیچ —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $job->supplier_id) == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="contractor_name">یان ناوی کرێکار</label>
                <input id="contractor_name" name="contractor_name" class="field"
                       value="{{ old('contractor_name', $job->contractor_name) }}">
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="order_id">پەیوەست بە وەسڵی</label>
                <select id="order_id" name="order_id" class="field">
                    <option value="">— هیچ —</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}" @selected(old('order_id', $job->order_id) == $order->id)>
                            ژ. {{ $order->invoice_no }} — {{ $order->customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="cost">تێچوو <span class="text-[--color-danger]">*</span></label>
                <input id="cost" name="cost" type="number" step="0.01" min="0" required class="field num"
                       value="{{ old('cost', $job->cost) }}">
            </div>

            <div>
                <label class="label" for="currency">دراو</label>
                <select id="currency" name="currency" class="field" x-model="currency">
                    <option value="IQD">دینار</option>
                    <option value="USD">دۆلار</option>
                </select>
            </div>

            <div x-show="currency === 'USD'" x-cloak>
                <label class="label" for="exchange_rate">نرخی دۆلار</label>
                <input id="exchange_rate" name="exchange_rate" type="number" step="0.01" class="field num"
                       value="{{ old('exchange_rate', $job->exchange_rate ?: $rate) }}">
            </div>

            <div>
                <label class="label" for="paid_amount">دراوە</label>
                <input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="field num"
                       value="{{ old('paid_amount', $job->paid_amount) }}">
            </div>

            <div>
                <label class="label" for="started_at">دەستپێکردن</label>
                <input id="started_at" name="started_at" type="date" class="field num"
                       value="{{ old('started_at', $job->started_at?->toDateString()) }}">
            </div>

            <div>
                <label class="label" for="finished_at">تەواوبوون</label>
                <input id="finished_at" name="finished_at" type="date" class="field num"
                       value="{{ old('finished_at', $job->finished_at?->toDateString()) }}">
            </div>

            <div>
                <label class="label" for="status">دۆخ</label>
                <select id="status" name="status" class="field">
                    @foreach (\App\Models\ExternalJob::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $job->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="description">وردەکاری</label>
                <textarea id="description" name="description" rows="3" class="field">{{ old('description', $job->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $job->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('external-jobs.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($job->exists)
            <button type="submit" form="delete-job" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($job->exists)
    <form id="delete-job" method="POST" action="{{ route('external-jobs.destroy', $job) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
