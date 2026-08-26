<!DOCTYPE html>
<html lang="ckb" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>چوونەژوورەوە بۆ سیستەم</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ebf0f7;
            background-image: 
                radial-gradient(at 0% 0%, rgba(219, 234, 254, 0.8) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(224, 231, 255, 0.8) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
        }

        /* SVG Facet Polygon Shapes in Background */
        .facet-bg {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 680px;
            background: #ffffff;
            border-radius: 32px;
            padding: 52px 48px;
            box-shadow: 0 30px 70px -15px rgba(15, 23, 42, 0.09), 0 0 0 1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .card-title {
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .card-subtitle {
            font-size: 14px;
            color: #94a3b8;
            font-weight: 500;
        }

        .pill-container {
            background-color: #e2e8f0;
            border-radius: 9999px;
            padding: 6px;
            display: flex;
            gap: 6px;
            margin-bottom: 30px;
        }

        .pill-btn {
            flex: 1;
            padding: 11px 18px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pill-btn.active {
            background: #ffffff;
            color: #2563eb;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            text-align: right;
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            height: 52px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 16px;
            font-size: 15px;
            color: #0f172a;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.12);
        }

        .input-email {
            padding: 0 46px 0 18px;
            text-align: left;
        }

        .input-password {
            padding: 0 46px 0 46px;
            text-align: center;
            letter-spacing: 3px;
        }

        .input-icon-right {
            position: absolute;
            right: 16px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .eye-btn {
            position: absolute;
            right: 16px;
            color: #94a3b8;
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
        }

        .eye-btn:hover {
            color: #475569;
        }

        .form-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 28px;
            gap: 16px;
        }

        .submit-btn {
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 13px 28px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.38);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            accent-color: #2563eb;
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .login-card {
                max-width: 540px;
                padding: 40px 32px;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 16px 12px;
            }

            .login-card {
                padding: 30px 20px;
                border-radius: 24px;
                max-width: 100%;
            }

            .card-title {
                font-size: 20px;
            }

            .card-subtitle {
                font-size: 12px;
            }

            .pill-btn {
                font-size: 12px;
                padding: 8px 10px;
            }

            .form-input {
                height: 46px;
                font-size: 13.5px;
            }

            .submit-btn {
                padding: 11px 20px;
                font-size: 13px;
            }

            .remember-label {
                font-size: 12.5px;
            }
        }
    </style>
</head>
<body x-data="{
          selectedRole: 'admin',
          email: 'admin@hemin.krd',
          password: '',
          showPassword: false,
          loading: false,
          setRole(role) {
              this.selectedRole = role;
              this.email = role === 'admin' ? 'admin@hemin.krd' : 'kogha@hemin.krd';
              this.password = '';
              this.$nextTick(() => {
                  const passInput = document.getElementById('password');
                  if (passInput) passInput.focus();
              });
          }
      }">

    <!-- SVG Facet Geometric Background -->
    <svg class="facet-bg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 900" preserveAspectRatio="none">
        <polygon points="0,0 600,0 350,450 0,300" fill="#f8fafc" opacity="0.6"/>
        <polygon points="600,0 1440,0 1440,400 900,300" fill="#e2e8f0" opacity="0.4"/>
        <polygon points="0,300 350,450 200,900 0,900" fill="#e2e8f0" opacity="0.5"/>
        <polygon points="350,450 900,300 1100,750 500,900" fill="#ffffff" opacity="0.4"/>
        <polygon points="900,300 1440,400 1440,900 1100,750" fill="#cbd5e1" opacity="0.3"/>
        <polygon points="200,900 500,900 1100,750 1440,900" fill="#f1f5f9" opacity="0.7"/>
    </svg>

    <!-- Centered Login Box -->
    <div class="login-card">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 26px;">
            <h1 class="card-title">
                بەخێربێیتەوە بۆ سیستەم
            </h1>
            <p class="card-subtitle">
                تکایە بەشەکەت دیاری بکە و زانیارییەکانت بنووسە بۆ چوونەژوورەوە
            </p>
        </div>

        <!-- Pill Switcher -->
        <div class="pill-container">
            <!-- کارگە و دروستکردن -->
            <button type="button" 
                    @click="setRole('wasta')"
                    class="pill-btn"
                    :class="{ 'active': selectedRole === 'wasta' }">
                <span>کارگە و دروستکردن</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </button>

            <!-- ئۆفیس و دارایی -->
            <button type="button" 
                    @click="setRole('admin')"
                    class="pill-btn"
                    :class="{ 'active': selectedRole === 'admin' }">
                <span>ئۆفیس و دارایی</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    <path d="M9 22v-4h6v4"/>
                    <path d="M8 6h.01"/>
                    <path d="M16 6h.01"/>
                    <path d="M12 6h.01"/>
                    <path d="M12 10h.01"/>
                    <path d="M12 14h.01"/>
                    <path d="M16 10h.01"/>
                    <path d="M16 14h.01"/>
                    <path d="M8 10h.01"/>
                    <path d="M8 14h.01"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('login') }}" @submit="loading = true">
            @csrf

            <!-- Errors Alert -->
            @if (isset($errors) && $errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 12px; font-weight: 700; border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Email Field -->
            <div class="form-group">
                <label class="form-label" for="email">ئیمەیڵ</label>
                <div class="input-wrapper">
                    <input id="email" 
                           name="email" 
                           type="email" 
                           required
                           x-model="email"
                           class="form-input input-email"
                           dir="ltr" 
                           placeholder="admin@hemin.krd">
                    <div class="input-icon-right">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label class="form-label" for="password">وشەی نهێنی</label>
                <div class="input-wrapper">
                    <!-- Key icon on the left -->
                    <div class="input-icon-left">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="7.5" cy="15.5" r="4.5"/>
                            <path d="m21 3-9.5 9.5"/>
                            <path d="m15.5 7.5 3 3"/>
                        </svg>
                    </div>

                    <!-- Input -->
                    <input id="password" 
                           name="password" 
                           :type="showPassword ? 'text' : 'password'"
                           required
                           x-model="password"
                           class="form-input input-password"
                           dir="ltr" 
                           placeholder="••••••••">

                    <!-- Eye toggle on the right -->
                    <button type="button" 
                            @click="showPassword = !showPassword"
                            class="eye-btn">
                        <svg x-show="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg x-show="showPassword" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="form-bottom">
                <!-- Button on the LEFT in RTL visual row -->
                <button type="submit" 
                        :disabled="loading"
                        class="submit-btn">
                    <span style="font-size: 15px; line-height: 1;">←</span>
                    <span x-text="selectedRole === 'admin' ? 'چوونەژوورەوەی ئۆفیس' : 'چوونەژوورەوەی کارگە'"></span>
                </button>

                <!-- Remember me on the RIGHT in RTL visual row -->
                <label class="remember-label">
                    <span>لەبیرم مەکە</span>
                    <input type="checkbox" name="remember" class="remember-checkbox">
                </label>
            </div>
        </form>

    </div>

</body>
</html>
