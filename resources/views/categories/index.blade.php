@extends('layouts.app')
@section('title', 'جۆرەکانی بابەت')

@section('actions')
    <a href="{{ route('items.index') }}" class="btn btn-ghost">گەڕانەوە بۆ بابەتەکان</a>
@endsection

@section('content')

<div class="grid gap-6 lg:grid-cols-3">

    {{-- فۆرمی زیادکردنی جۆری نوێ --}}
    <div class="card lg:col-span-1 h-fit">
        <div class="card-head flex items-center gap-2">
            <span class="icon-chip bg-[--color-brand-soft] text-[--color-brand-700] size-7 rounded-md">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </span>
            <span>زیادکردنی جۆری نوێ</span>
        </div>
        <form method="POST" action="{{ route('categories.store') }}" class="card-body space-y-4">
            @csrf
            <div>
                <label class="label" for="new_name">ناوی جۆر <span class="text-[--color-danger]">*</span></label>
                <input id="new_name" name="name" class="field" required placeholder="بۆ نموونە: ئاسن و لوولە، بۆیاخ..." value="{{ old('name') }}">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="new_note">تێبینی</label>
                <input id="new_note" name="note" class="field" placeholder="کورتە ڕوونکردنەوە (ئیختیاری)" value="{{ old('note') }}">
            </div>

            <button class="btn btn-primary w-full">زیادکردنی جۆر</button>
        </form>
    </div>

    {{-- خشتەی جۆرەکان --}}
    <div class="card lg:col-span-2">
        <div class="card-head flex items-center justify-between">
            <span>هەموو جۆرەکان</span>
            <span class="num text-xs font-semibold text-[--color-ink-soft]">{{ fmt_num($categories->count()) }} جۆر</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>ناوی جۆر</th>
                        <th>تێبینی</th>
                        <th class="num">ژمارەی بابەت</th>
                        <th class="text-left">کردار</th>
                    </tr>
                </thead>
                <tbody x-data="{ editingId: null, editName: '', editNote: '' }">
                    @forelse ($categories as $cat)
                        <tr>
                            <td class="font-semibold">
                                <template x-if="editingId === {{ $cat->id }}">
                                    <form id="edit-form-{{ $cat->id }}" method="POST" action="{{ route('categories.update', $cat) }}" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" x-model="editName" class="field !py-1 text-xs" required>
                                    </form>
                                </template>
                                <template x-if="editingId !== {{ $cat->id }}">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="size-2 rounded-full bg-[--color-brand-600]"></span>
                                        {{ $cat->name }}
                                    </span>
                                </template>
                            </td>

                            <td class="text-xs text-[--color-ink-soft]">
                                <template x-if="editingId === {{ $cat->id }}">
                                    <input form="edit-form-{{ $cat->id }}" type="text" name="note" x-model="editNote" class="field !py-1 text-xs" placeholder="تێبینی">
                                </template>
                                <template x-if="editingId !== {{ $cat->id }}">
                                    <span>{{ $cat->note ?? '—' }}</span>
                                </template>
                            </td>

                            <td class="num">
                                <a href="{{ route('items.index', ['category' => $cat->id]) }}" class="inline-flex items-center gap-1 font-semibold text-[--color-brand-700] hover:underline">
                                    {{ fmt_num($cat->items_count) }} بابەت
                                </a>
                            </td>

                            <td class="whitespace-nowrap text-left text-xs">
                                <template x-if="editingId === {{ $cat->id }}">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="submit" form="edit-form-{{ $cat->id }}" class="btn btn-primary !py-1 !px-2.5 text-xs">پاشەکەوت</button>
                                        <button type="button" @click="editingId = null" class="btn btn-ghost !py-1 !px-2 text-xs">پاشگەزبوونەوە</button>
                                    </div>
                                </template>

                                <template x-if="editingId !== {{ $cat->id }}">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                                @click="editingId = {{ $cat->id }}; editName = '{{ addslashes($cat->name) }}'; editNote = '{{ addslashes($cat->note ?? '') }}'"
                                                class="font-medium text-[--color-brand-700] hover:underline">
                                            دەستکاری
                                        </button>

                                        @if ($cat->items_count == 0)
                                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" 
                                                  onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم جۆرە؟')">
                                                @csrf @method('DELETE')
                                                <button class="text-[--color-danger] hover:underline">سڕینەوە</button>
                                            </form>
                                        @endif
                                    </div>
                                </template>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-sm text-[--color-ink-soft]">
                                هیچ جۆرێک تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
