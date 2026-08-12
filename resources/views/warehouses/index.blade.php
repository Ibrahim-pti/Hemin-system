@extends('layouts.app')
@section('title', 'کۆگاکان')

@section('actions')
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">کۆگای نوێ</a>
@endsection

@section('content')

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناو</th>
                    <th>شوێن</th>
                    <th class="num">ژمارەی جوڵە</th>
                    <th>دۆخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($warehouses as $warehouse)
                    <tr>
                        <td class="font-medium">
                            {{ $warehouse->name }}
                            @if ($warehouse->is_default)
                                <span class="badge badge-ok mr-1">بنەڕەت</span>
                            @endif
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $warehouse->location ?? '—' }}</td>
                        <td class="num">{{ fmt_num($warehouse->movements_count) }}</td>
                        <td>
                            <span class="badge {{ $warehouse->is_active ? 'badge-ok' : 'badge-warn' }}">
                                {{ $warehouse->is_active ? 'چالاک' : 'ناچالاک' }}
                            </span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-sm text-[--color-brand-700]">دەستکاری</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ کۆگایەک نییە.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
