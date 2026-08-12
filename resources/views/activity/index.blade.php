@extends('layouts.app')
@section('title', 'مێژووی کردارەکان')

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="label">بەش</label>
            <select name="subject" class="field">
                <option value="">هەموو</option>
                @foreach ($subjects as $class => $label)
                    <option value="{{ $class }}" @selected(request('subject') === $class)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">کردار</label>
            <select name="event" class="field">
                <option value="">هەموو</option>
                @foreach ($events as $key => $label)
                    <option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">بەکارهێنەر</label>
            <select name="user" class="field">
                <option value="">هەموو</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button class="btn btn-primary w-full">پاڵاوتن</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>کات</th><th>بەکارهێنەر</th><th>بەش</th><th>کردار</th><th>وردەکاری</th></tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td class="num whitespace-nowrap text-[--color-ink-soft]">
                            {{ $activity->created_at->format('Y/m/d H:i') }}
                        </td>
                        <td>{{ $activity->causer?->name ?? 'سیستەم' }}</td>
                        <td>{{ $subjects[$activity->subject_type] ?? class_basename($activity->subject_type) }}</td>
                        <td>
                            <span class="badge {{ match ($activity->event) {
                                'created' => 'badge-ok',
                                'deleted' => 'badge-danger',
                                default => 'badge-warn',
                            } }}">{{ $events[$activity->event] ?? $activity->event }}</span>
                        </td>
                        <td class="text-xs text-[--color-ink-soft]">
                            @php $changed = $activity->properties['attributes'] ?? []; @endphp
                            @if ($changed)
                                {{ collect($changed)->take(4)->map(fn ($v, $k) => "{$k}: ".(is_scalar($v) ? $v : '—'))->implode(' · ') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-sm text-[--color-ink-soft]">هێشتا هیچ کردارێک تۆمار نەکراوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $activities->links() }}</div>

@endsection
