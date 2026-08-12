<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چوونەژوورەوە — {{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-lg bg-[--color-brand-700] text-xl font-bold text-white">
                هـ
            </div>
            <h1 class="text-lg font-semibold">{{ \App\Models\Setting::get('company_name', 'کارگەی هێمن') }}</h1>
            <p class="mt-1 text-sm text-[--color-ink-soft]">
                {{ \App\Models\Setting::get('company_tagline') }}
            </p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-md border border-[--color-danger]/35 bg-[--color-danger]/5 px-3 py-2 text-sm text-[--color-danger]">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div>
                        <label class="label" for="email">ئیمەیل</label>
                        <input id="email" name="email" type="email" required autofocus
                               value="{{ old('email') }}"
                               class="field" dir="ltr" placeholder="admin@hemin.krd">
                    </div>

                    <div>
                        <label class="label" for="password">وشەی نهێنی</label>
                        <input id="password" name="password" type="password" required
                               class="field" dir="ltr">
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remember" class="size-4 rounded border-[--color-line-strong]">
                        بیرم بێت
                    </label>

                    <button type="submit" class="btn btn-primary w-full">چوونەژوورەوە</button>
                </form>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-[--color-ink-soft]">
            {{ \App\Models\Setting::get('company_address') }}
        </p>
    </div>

</body>
</html>
