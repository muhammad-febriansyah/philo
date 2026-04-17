<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Login | {{ $siteSettings['site_name'] ?? config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @if(!empty($siteSettings['favicon_path']))
            <link rel="icon" href="{{ Storage::url($siteSettings['favicon_path']) }}" sizes="any">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            body {
                font-family: 'Poppins', sans-serif;
                background: #FAFAF5;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                position: relative;
                color: #18181b;
            }

            /* Noise texture */
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                opacity: 0.04;
                background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            }

            .wrapper {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 400px;
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            /* Branding */
            .brand {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }

            .brand-logo {
                height: 150px;
                width: auto;
                object-fit: cover;
            }

            .brand-icon {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                background: #E8C900;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(232,201,0,0.35);
            }

            .brand-name {
                font-family: 'Syne', sans-serif;
                font-weight: 800;
                font-size: 1.5rem;
                color: #18181b;
                line-height: 1;
            }

            .brand-title {
                font-size: 1.1rem;
                font-weight: 600;
                color: #18181b;
                text-align: center;
            }

            .brand-sub {
                font-size: 0.85rem;
                color: #71717a;
                text-align: center;
            }

            /* Card */
            .card {
                background: white;
                border: 1px solid rgba(0,0,0,0.07);
                border-radius: 1rem;
                padding: 1.75rem;
                box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
                margin-bottom: 1.1rem;
            }

            label {
                font-size: 0.85rem;
                font-weight: 500;
                color: #3f3f46;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                width: 100%;
                padding: 0.6rem 0.85rem;
                border: 1px solid #d4d4d8;
                border-radius: 0.5rem;
                font-size: 0.9rem;
                font-family: 'Poppins', sans-serif;
                color: #18181b;
                background: white;
                outline: none;
                transition: border-color 0.15s, box-shadow 0.15s;
            }

            input[type="email"]:focus,
            input[type="password"]:focus,
            input[type="text"]:focus {
                border-color: #E8C900;
                box-shadow: 0 0 0 3px rgba(232,201,0,0.2);
            }

            input.is-invalid {
                border-color: #ef4444;
            }

            .invalid-feedback {
                font-size: 0.78rem;
                color: #ef4444;
                margin-top: 0.25rem;
            }

            /* Password wrapper */
            .password-wrap {
                position: relative;
            }

            .password-wrap input {
                padding-right: 2.8rem;
            }

            .toggle-password {
                position: absolute;
                right: 0.7rem;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                color: #a1a1aa;
                padding: 0;
                line-height: 1;
                display: flex;
                align-items: center;
            }

            .toggle-password:hover { color: #3f3f46; }

            /* Remember */
            .remember-row {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 1.25rem;
            }

            .remember-row input[type="checkbox"] {
                width: 15px;
                height: 15px;
                accent-color: #E8C900;
                cursor: pointer;
            }

            .remember-row label {
                font-size: 0.85rem;
                color: #52525b;
                cursor: pointer;
            }

            /* Alert */
            .alert {
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                font-size: 0.85rem;
                margin-bottom: 1.1rem;
            }

            .alert-danger {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }

            .alert-success {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #15803d;
            }

            /* Submit button */
            .btn-submit {
                width: 100%;
                padding: 0.65rem;
                background: #E8C900;
                color: #18181b;
                border: none;
                border-radius: 0.5rem;
                font-family: 'Poppins', sans-serif;
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.15s, box-shadow 0.15s;
                box-shadow: 0 2px 8px rgba(232,201,0,0.3);
            }

            .btn-submit:hover {
                background: #d4b800;
                box-shadow: 0 4px 14px rgba(232,201,0,0.4);
            }

            .btn-submit:active { background: #bfa300; }
        </style>
    </head>

    <body>
        <div class="wrapper">
            <!-- Branding -->
            <div class="brand">
                @if(!empty($siteSettings['logo_path']))
                    <img src="{{ Storage::url($siteSettings['logo_path']) }}"
                         alt="{{ $siteSettings['site_name'] ?? config('app.name') }}"
                         class="brand-logo" width="50">
                @else
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" width="22" height="22">
                            <circle cx="12" cy="12" r="4"/>
                            <line x1="12" y1="2" x2="12" y2="8" stroke-linecap="round"/>
                            <line x1="12" y1="16" x2="12" y2="22" stroke-linecap="round"/>
                            <line x1="4.93" y1="4.93" x2="9.17" y2="9.17" stroke-linecap="round"/>
                            <line x1="14.83" y1="14.83" x2="19.07" y2="19.07" stroke-linecap="round"/>
                            <line x1="2" y1="12" x2="8" y2="12" stroke-linecap="round"/>
                            <line x1="16" y1="12" x2="22" y2="12" stroke-linecap="round"/>
                            <line x1="4.93" y1="19.07" x2="9.17" y2="14.83" stroke-linecap="round"/>
                            <line x1="14.83" y1="9.17" x2="19.07" y2="4.93" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="brand-name">{{ $siteSettings['site_name'] ?? config('app.name') }}</span>
                @endif
                <p class="brand-title">Masuk ke akun Anda</p>
                <p class="brand-sub">Masukkan email dan password untuk melanjutkan</p>
            </div>

            <!-- Card -->
            <div class="card">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="email"
                               class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password"
                                   required autocomplete="current-password"
                                   class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                                <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg id="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn-submit">Log In</button>
                </form>
            </div>
        </div>

        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const eyeIcon = document.getElementById('eye-icon');
                const eyeOffIcon = document.getElementById('eye-off-icon');
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eyeIcon.style.display = isPassword ? 'none' : '';
                eyeOffIcon.style.display = isPassword ? '' : 'none';
            }
        </script>
    </body>
</html>
