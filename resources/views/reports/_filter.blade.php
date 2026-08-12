{{-- پاڵاوتنی ماوەی کات — بۆ هەموو راپۆرتەکان. --}}
<form method="GET" class="card mb-4 no-print">
    <div class="card-body flex flex-wrap items-end gap-3">
        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ $from }}" class="field num">
        </div>
        <div>
            <label class="label">تا بەرواری</label>
            <input type="date" name="to" value="{{ $to }}" class="field num">
        </div>
        <button class="btn btn-primary">پیشاندان</button>
        <button type="button" onclick="window.print()" class="btn btn-ghost">چاپ</button>
        <a href="{{ route('reports.index') }}" class="btn btn-ghost mr-auto">هەموو راپۆرتەکان</a>
    </div>
</form>
