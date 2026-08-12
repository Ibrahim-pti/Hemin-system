@extends('layouts.app')
@section('title', 'ئیشی خاریجی')

@section('actions')
    <a href="{{ route('external-jobs.create') }}" class="btn btn-primary">ئیشی نوێ</a>
@endsection

@section('content')

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'کۆی تێچوو', 'value' => fmt_money($totalCost), 'tone' => null])
</div>

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-3">
        <div class="sm:col-span-2">
            <label class="label">گەڕان</label>
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناونیشان، ژمارە یان کرێکار...">
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="label">دۆخ</label>
                <select name="status" class="field">
                    <option value="">هەموو</option>
                    @foreach (\App\Models\ExternalJob::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
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
                    <th>ژمارە</th><th>ناونیشان</th><th>کرێکار</th><th>وەسڵ</th>
                    <th class="num">تێچوو</th><th class="num">ماوە</th><th>دۆخ</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td class="num font-medium">{{ $job->job_no }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->contractor_label }}</td>
                        <td class="num">
                            @if ($job->order)
                                <a href="{{ route('orders.show', $job->order) }}" class="text-[--color-brand-700]">
                                    {{ $job->order->invoice_no }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="num">{{ fmt_money($job->cost, $job->currency) }}</td>
                        <td class="num {{ $job->remaining() > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                            {{ fmt_money($job->remaining()) }}
                        </td>
                        <td>
                            <span class="badge {{ match ($job->status) {
                                'done' => 'badge-ok',
                                'cancelled' => 'badge-danger',
                                default => 'badge-warn',
                            } }}">{{ $job->status_label }}</span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('external-jobs.edit', $job) }}" class="text-sm text-[--color-brand-700]">دەستکاری</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ ئیشێکی خاریجی نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $jobs->links() }}</div>

@endsection
