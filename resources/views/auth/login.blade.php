<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Login | {{ $siteSettings['site_name'] ?? config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $faviconUrl = !empty($siteSettings['favicon_path']) ? Storage::url($siteSettings['favicon_path']) : '/philo.png';
            $logoUrl = !empty($siteSettings['logo_path']) ? Storage::url($siteSettings['logo_path']) : null;
            $siteName = $siteSettings['site_name'] ?? config('app.name');
            $heroImages = ($galleries ?? collect())->values();
            $bento = $heroImages->take(4);
            $mobilePics = $heroImages->take(3);
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}" sizes="any" type="image/png">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>
            html { scroll-behavior: smooth; scroll-padding-top: 5rem; }
            @media (prefers-reduced-motion: reduce) {
                html { scroll-behavior: auto; }
            }

            :root {
                --ink: #0a0a0a;
                --ink-2: #18181b;
                --ink-3: #27272a;
                --paper: #ffffff;
                --paper-2: #fafaf5;
                --paper-3: #f4f4ed;
                --line: #e4e4e7;
                --line-2: #d4d4d8;
                --muted: #71717a;
                --muted-2: #a1a1aa;
                --gold: #E8C900;
                --gold-deep: #c9a800;
                --gold-soft: #fef9c3;
                --danger: #ef4444;
            }

            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

            html, body { height: 100%; }

            body {
                font-family: 'Poppins', system-ui, -apple-system, sans-serif;
                background: var(--paper);
                min-height: 100vh;
                min-height: 100dvh;
                color: var(--ink);
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                overflow-x: hidden;
            }

            ::selection { background: var(--gold); color: var(--ink); }

            /* ────────── Layout ────────── */
            .shell {
                min-height: 100vh;
                min-height: 100dvh;
                display: grid;
                grid-template-columns: 1fr;
                background: var(--paper);
            }

            @media (min-width: 1024px) {
                .shell {
                    grid-template-columns: 1fr 1fr;
                    height: 100vh;
                    height: 100dvh;
                    min-height: 0;
                }
            }

            /* ─────────────────────────────────────────────
               HERO (Bento side) — desktop only
               ───────────────────────────────────────────── */
            .hero {
                position: relative;
                overflow: hidden;
                background: var(--paper-2);
                padding: 2rem 2.5rem;
                display: none;
                flex-direction: column;
                gap: 1.25rem;
                height: 100vh;
                height: 100dvh;
                border-right: 1px solid var(--line);
            }

            @media (min-width: 1024px) {
                .hero { display: flex; }
            }

            @media (min-width: 1280px) {
                .hero { padding: 2.25rem 3rem; gap: 1.5rem; }
            }

            /* Subtle grid texture */
            .hero::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(to right, rgba(0,0,0,0.025) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(0,0,0,0.025) 1px, transparent 1px);
                background-size: 32px 32px;
                pointer-events: none;
                mask-image: radial-gradient(ellipse at 70% 30%, #000 0%, transparent 70%);
                -webkit-mask-image: radial-gradient(ellipse at 70% 30%, #000 0%, transparent 70%);
            }

            /* ─── Hero header ─── */
            .hero-head {
                position: relative;
                z-index: 5;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .hero-brand {
                display: flex;
                align-items: center;
                gap: 0.7rem;
                min-height: 110px;
            }

            .hero-mark {
                width: 56px;
                height: 56px;
                border-radius: 14px;
                background: var(--ink);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--gold);
                flex-shrink: 0;
                overflow: hidden;
            }

            .hero-logo-img {
                height: 110px;
                width: auto;
                max-width: 380px;
                object-fit: contain;
                display: block;
            }

            .hero-brand-name {
                font-weight: 800;
                font-size: 1.05rem;
                letter-spacing: -0.01em;
                color: var(--ink);
            }

            .hero-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.06em;
                color: var(--muted);
                background: var(--paper);
                border: 1px solid var(--line);
                padding: 0.4rem 0.85rem;
                border-radius: 999px;
            }

            .hero-pill::before {
                content: '';
                width: 6px;
                height: 6px;
                background: #22c55e;
                border-radius: 50%;
                box-shadow: 0 0 10px #22c55e;
                animation: pulse 2s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.4; }
            }

            /* ─── Hero copy block ─── */
            .hero-copy {
                position: relative;
                z-index: 2;
                max-width: 520px;
            }

            .hero-eyebrow {
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.18em;
                text-transform: uppercase;
                color: var(--gold-deep);
                margin-bottom: 0.75rem;
                display: flex;
                align-items: center;
                gap: 0.6rem;
            }

            .hero-eyebrow::before {
                content: '';
                width: 22px;
                height: 1.5px;
                background: var(--gold-deep);
            }

            .hero-display {
                font-weight: 800;
                font-size: clamp(1.85rem, 3vw, 2.5rem);
                line-height: 1.05;
                letter-spacing: -0.035em;
                color: var(--ink);
                margin-bottom: 0.7rem;
            }

            .hero-display .accent {
                position: relative;
                display: inline-block;
                color: var(--ink);
                z-index: 1;
            }

            .hero-display .accent::after {
                content: '';
                position: absolute;
                left: -4px;
                right: -4px;
                bottom: 4px;
                height: 12px;
                background: var(--gold);
                z-index: -1;
                border-radius: 2px;
                transform: rotate(-1deg);
            }

            .hero-sub {
                font-size: 0.95rem;
                line-height: 1.6;
                color: var(--muted);
                font-weight: 400;
                max-width: 460px;
            }

            /* ─── BENTO grid ─── */
            .bento {
                position: relative;
                z-index: 2;
                display: grid;
                gap: 12px;
                flex: 1;
                min-height: 0;
                grid-template-columns: 1.5fr 1fr;
                grid-template-rows: 1fr 1fr;
                grid-template-areas:
                    "a b"
                    "a c";
            }

            @media (min-width: 1280px) {
                .bento {
                    grid-template-columns: 1.5fr 1fr 0.85fr;
                    grid-template-rows: 1.2fr 1fr;
                    grid-template-areas:
                        "a b d"
                        "a c d";
                    gap: 14px;
                }
            }

            .bento-cell {
                position: relative;
                overflow: hidden;
                border-radius: 16px;
                background: var(--paper-3);
                box-shadow:
                    0 14px 30px -16px rgba(0,0,0,0.18),
                    0 0 0 1px rgba(0,0,0,0.04);
                transition: transform 0.4s cubic-bezier(.2,.8,.2,1), box-shadow 0.4s;
            }

            .bento-cell:hover {
                transform: translateY(-4px);
                box-shadow:
                    0 22px 40px -16px rgba(0,0,0,0.28),
                    0 0 0 1px rgba(0,0,0,0.04);
            }

            .bento-cell img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.6s cubic-bezier(.2,.8,.2,1);
            }

            .bento-cell:hover img {
                transform: scale(1.05);
            }

            .bento-cell.a { grid-area: a; }
            .bento-cell.b { grid-area: b; }
            .bento-cell.c { grid-area: c; }
            .bento-cell.d { grid-area: d; display: none; }

            @media (min-width: 1280px) {
                .bento-cell.d { display: block; }
            }

            /* Image overlay caption on big tile */
            .bento-cell.a::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.55) 100%);
                pointer-events: none;
            }

            .bento-cap {
                position: absolute;
                left: 16px;
                right: 16px;
                bottom: 16px;
                z-index: 2;
                color: #fff;
                font-size: 0.78rem;
                font-weight: 500;
                letter-spacing: 0.04em;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .bento-cap .dot {
                width: 6px;
                height: 6px;
                background: var(--gold);
                border-radius: 50%;
                box-shadow: 0 0 10px var(--gold);
            }

            /* Stat strip footer */
            .hero-stats {
                position: relative;
                z-index: 2;
                display: flex;
                align-items: center;
                gap: 1.5rem;
                padding-top: 1.25rem;
                border-top: 1px dashed var(--line-2);
            }

            .stat {
                display: flex;
                flex-direction: column;
                gap: 0.15rem;
            }

            .stat-num {
                font-size: 1.35rem;
                font-weight: 800;
                color: var(--ink);
                letter-spacing: -0.02em;
                line-height: 1;
            }

            .stat-num span { color: var(--gold-deep); }

            .stat-label {
                font-size: 0.72rem;
                font-weight: 500;
                color: var(--muted);
                letter-spacing: 0.02em;
            }

            .stat-divider {
                width: 1px;
                height: 32px;
                background: var(--line);
            }

            /* ─────────────────────────────────────────────
               FORM SIDE
               ───────────────────────────────────────────── */
            .form-side {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem 1.25rem;
                background: var(--paper);
            }

            @media (min-width: 768px) {
                .form-side { padding: 2.5rem 2rem; }
            }

            @media (min-width: 1024px) {
                .form-side {
                    height: 100vh;
                    height: 100dvh;
                    overflow-y: auto;
                }
            }

            .form-wrap {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 420px;
            }

            /* Mobile photo strip */
            .mobile-strip {
                display: flex;
                justify-content: center;
                margin-bottom: 1.5rem;
            }

            @media (min-width: 1024px) {
                .mobile-strip { display: none; }
            }

            .mobile-strip-inner {
                display: flex;
                gap: 6px;
                background: var(--paper);
                padding: 8px 8px 14px;
                border-radius: 4px;
                box-shadow: 0 10px 24px -10px rgba(0,0,0,0.25), 0 0 0 1px rgba(0,0,0,0.05);
                transform: rotate(-2deg);
            }

            .mobile-strip-frame {
                width: 56px;
                height: 56px;
                border-radius: 2px;
                overflow: hidden;
                background: var(--paper-3);
            }

            .mobile-strip-frame img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            /* Mobile brand */
            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                min-height: 80px;
                margin-bottom: 1.5rem;
            }

            .mobile-brand .hero-logo-img {
                height: 80px;
                max-width: 280px;
            }

            @media (min-width: 1024px) {
                .mobile-brand { display: none; }
            }

            .mobile-brand-name {
                font-weight: 800;
                font-size: 1.05rem;
                color: var(--ink);
                letter-spacing: -0.01em;
            }

            /* Form header */
            .form-head {
                margin-bottom: 1.25rem;
            }

            .form-eyebrow {
                font-size: 0.7rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 0.85rem;
            }

            .form-title {
                font-weight: 800;
                font-size: clamp(1.55rem, 2.6vw, 1.85rem);
                line-height: 1.1;
                letter-spacing: -0.025em;
                color: var(--ink);
                margin-bottom: 0.45rem;
            }

            .form-sub {
                font-size: 0.92rem;
                color: var(--muted);
                line-height: 1.55;
                font-weight: 400;
            }

            /* Form fields */
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
                margin-bottom: 0.85rem;
            }

            label {
                font-size: 0.78rem;
                font-weight: 600;
                color: var(--ink-2);
                letter-spacing: 0.01em;
            }

            .input-wrap {
                position: relative;
            }

            .input-wrap .icon-left {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--muted-2);
                pointer-events: none;
                display: flex;
                align-items: center;
                transition: color 0.18s;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                width: 100%;
                padding: 0.8rem 1rem 0.8rem 2.85rem;
                border: 1.5px solid var(--line);
                border-radius: 12px;
                font-size: 0.93rem;
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
                color: var(--ink);
                background: var(--paper);
                outline: none;
                transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
                -webkit-appearance: none;
            }

            input::placeholder {
                color: var(--muted-2);
                font-weight: 400;
            }

            input[type="email"]:hover,
            input[type="password"]:hover,
            input[type="text"]:hover {
                border-color: var(--line-2);
            }

            input[type="email"]:focus,
            input[type="password"]:focus,
            input[type="text"]:focus {
                border-color: var(--ink);
                box-shadow: 0 0 0 4px rgba(24,24,27,0.08);
            }

            .input-wrap:focus-within .icon-left {
                color: var(--ink);
            }

            input.is-invalid {
                border-color: var(--danger);
                background: #fef2f2;
            }

            input.is-invalid:focus {
                box-shadow: 0 0 0 4px rgba(239,68,68,0.12);
            }

            .invalid-feedback {
                font-size: 0.78rem;
                color: var(--danger);
                margin-top: 0.35rem;
                display: flex;
                align-items: center;
                gap: 0.35rem;
                font-weight: 500;
            }

            /* Password */
            .password-wrap input { padding-right: 2.9rem; }

            .toggle-password {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                color: var(--muted-2);
                padding: 0.4rem;
                line-height: 1;
                display: flex;
                align-items: center;
                border-radius: 8px;
                transition: color 0.15s, background 0.15s;
            }

            .toggle-password:hover {
                color: var(--ink);
                background: var(--paper-2);
            }

            .toggle-password:focus-visible {
                outline: 2px solid var(--ink);
                outline-offset: 1px;
            }

            /* Remember row */
            .remember-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin: 0.35rem 0 1.1rem;
                flex-wrap: wrap;
            }

            .remember {
                display: flex;
                align-items: center;
                gap: 0.55rem;
            }

            .remember input[type="checkbox"] {
                width: 16px;
                height: 16px;
                accent-color: var(--ink);
                cursor: pointer;
                margin: 0;
            }

            .remember label {
                font-size: 0.85rem;
                color: var(--ink-2);
                cursor: pointer;
                font-weight: 500;
                user-select: none;
            }

            .forgot-link {
                font-size: 0.83rem;
                color: var(--muted);
                text-decoration: none;
                font-weight: 500;
                transition: color 0.15s;
                position: relative;
            }

            .forgot-link::after {
                content: '';
                position: absolute;
                left: 0;
                bottom: -2px;
                width: 100%;
                height: 1.5px;
                background: var(--ink);
                transform: scaleX(0);
                transform-origin: right;
                transition: transform 0.25s;
            }

            .forgot-link:hover { color: var(--ink); }
            .forgot-link:hover::after { transform: scaleX(1); transform-origin: left; }

            /* Alerts */
            .alert {
                padding: 0.85rem 1rem;
                border-radius: 12px;
                font-size: 0.85rem;
                margin-bottom: 1.1rem;
                display: flex;
                align-items: flex-start;
                gap: 0.65rem;
                line-height: 1.45;
                font-weight: 500;
            }

            .alert svg { flex-shrink: 0; margin-top: 1px; }

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

            /* CTA */
            .btn-submit {
                width: 100%;
                padding: 0.9rem 1.25rem;
                background: var(--ink);
                color: var(--paper);
                border: none;
                border-radius: 12px;
                font-family: 'Poppins', sans-serif;
                font-size: 0.93rem;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.12s ease, background 0.18s;
                box-shadow: 0 8px 22px -6px rgba(24,24,27,0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.6rem;
                position: relative;
                letter-spacing: -0.005em;
            }

            .btn-submit .arrow {
                display: inline-flex;
                transition: transform 0.25s cubic-bezier(.2,.8,.2,1);
            }

            .btn-submit:hover { background: var(--ink-3); }
            .btn-submit:hover .arrow { transform: translateX(4px); }
            .btn-submit:active { transform: translateY(1px); }

            .btn-submit:focus-visible {
                outline: 2px solid var(--gold);
                outline-offset: 3px;
            }

            /* Trust strip */
            .trust-strip {
                margin-top: 1.4rem;
                padding-top: 1.1rem;
                border-top: 1px solid var(--line);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                font-size: 0.78rem;
                color: var(--muted);
                flex-wrap: wrap;
            }

            .trust-item {
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }

            /* Reduced motion */
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }

            /* iOS Safari font size fix */
            @supports (-webkit-touch-callout: none) {
                input[type="email"],
                input[type="password"],
                input[type="text"] {
                    font-size: 16px;
                }
            }
        </style>
    </head>

    <body>
        <div class="shell">
            <!-- ────────── Hero (desktop) ────────── -->
            <aside class="hero">
                <header class="hero-head">
                    <div class="hero-brand">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="hero-logo-img">
                        @else
                            <div class="hero-mark">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="20" height="20">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="13" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="hero-brand-name">{{ $siteName }}</span>
                        @endif
                    </div>
                </header>

                <div class="hero-copy">
                    <p class="hero-eyebrow">Photo booth admin</p>
                    <h1 class="hero-display">
                        Setiap klik, sebuah <span class="accent">cerita.</span>
                    </h1>
                    <p class="hero-sub">
                        Kelola booth, template, paket, dan transaksi Anda dari satu dasbor yang dirancang untuk fokus dan kecepatan.
                    </p>
                </div>

                @if($bento->count() > 0)
                    <div class="bento" aria-hidden="true">
                        @if($bento->get(0))
                            <div class="bento-cell a">
                                <img src="{{ Storage::url($bento[0]->image_path) }}" alt="" loading="lazy">
                                <div class="bento-cap">
                                    <span><span class="dot" style="display:inline-block;margin-right:6px;"></span>{{ $bento[0]->title }}</span>
                                    <span style="font-size:0.7rem;opacity:0.75;">Galeri</span>
                                </div>
                            </div>
                        @endif
                        @if($bento->get(1))
                            <div class="bento-cell b">
                                <img src="{{ Storage::url($bento[1]->image_path) }}" alt="" loading="lazy">
                            </div>
                        @endif
                        @if($bento->get(2))
                            <div class="bento-cell c">
                                <img src="{{ Storage::url($bento[2]->image_path) }}" alt="" loading="lazy">
                            </div>
                        @endif
                        @if($bento->get(3))
                            <div class="bento-cell d">
                                <img src="{{ Storage::url($bento[3]->image_path) }}" alt="" loading="lazy">
                            </div>
                        @endif
                    </div>
                @endif

                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-num">1.2K<span>+</span></span>
                        <span class="stat-label">Momen diabadikan</span>
                    </div>
                    <span class="stat-divider"></span>
                    <div class="stat">
                        <span class="stat-num">50<span>+</span></span>
                        <span class="stat-label">Template tersedia</span>
                    </div>
                    <span class="stat-divider"></span>
                    <div class="stat">
                        <span class="stat-num">24/7</span>
                        <span class="stat-label">Dukungan tim</span>
                    </div>
                </div>
            </aside>

            <!-- ────────── Form ────────── -->
            <main class="form-side">
                <div class="form-wrap">
                    <!-- Mobile photo strip -->
                    @if($mobilePics->count() > 0)
                        <div class="mobile-strip" aria-hidden="true">
                            <div class="mobile-strip-inner">
                                @foreach($mobilePics as $img)
                                    <div class="mobile-strip-frame">
                                        <img src="{{ Storage::url($img->image_path) }}" alt="" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mobile brand -->
                    <div class="mobile-brand">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="hero-logo-img">
                        @else
                            <div class="hero-mark">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="20" height="20">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="13" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="mobile-brand-name">{{ $siteName }}</span>
                        @endif
                    </div>

                    <header class="form-head">
                        <p class="form-eyebrow">Masuk</p>
                        <h2 class="form-title">Selamat datang<br>kembali.</h2>
                        <p class="form-sub" style="margin-top:0.7rem;">Masuk ke akun Anda untuk melanjutkan mengelola dasbor.</p>
                    </header>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success" role="status">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-linecap="round" stroke-linejoin="round"/>
                                <polyline points="22 4 12 14.01 9 11.01" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>{{ session('status') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" novalidate id="login-form">
                        @csrf
                        @if(! empty($recaptchaEnabled) && ! empty($recaptchaSiteKey))
                            <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        @endif

                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrap">
                                <span class="icon-left">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="22,6 12,13 2,6" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input type="email" id="email" name="email"
                                       value="{{ old('email') }}"
                                       placeholder="nama@email.com"
                                       required autofocus autocomplete="email"
                                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                            </div>
                            @error('email')
                                <div class="invalid-feedback">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrap password-wrap">
                                <span class="icon-left">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input type="password" id="password" name="password"
                                       placeholder="••••••••"
                                       required autocomplete="current-password"
                                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                                <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Tampilkan password">
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
                                <div class="invalid-feedback">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="remember-row">
                            <div class="remember">
                                <input type="checkbox" id="remember" name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">Ingat saya</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn-submit">
                            <span>Masuk ke Dasbor</span>
                            <span class="arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16">
                                    <line x1="5" y1="12" x2="19" y2="12" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="12 5 19 12 12 19" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>
                    </form>

                    <div class="trust-strip">
                        <div class="trust-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Koneksi aman & terenkripsi</span>
                        </div>
                        <span>&copy; {{ date('Y') }} {{ $siteName }}</span>
                    </div>
                </div>
            </main>
        </div>

        @if(! empty($recaptchaEnabled) && ! empty($recaptchaSiteKey))
            <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
            <script>
                (function () {
                    var form = document.getElementById('login-form');
                    var tokenInput = document.getElementById('recaptcha_token');
                    if (! form || ! tokenInput || typeof grecaptcha === 'undefined') return;

                    form.addEventListener('submit', function (e) {
                        if (form.dataset.recaptchaReady === '1') return;
                        e.preventDefault();
                        grecaptcha.ready(function () {
                            grecaptcha.execute('{{ $recaptchaSiteKey }}', { action: 'login' })
                                .then(function (token) {
                                    tokenInput.value = token;
                                    form.dataset.recaptchaReady = '1';
                                    form.submit();
                                })
                                .catch(function () {
                                    var existingAlert = form.parentElement.querySelector('[data-recaptcha-alert]');
                                    if (existingAlert) return;

                                    var alert = document.createElement('div');
                                    alert.className = 'alert alert-danger';
                                    alert.setAttribute('role', 'alert');
                                    alert.setAttribute('data-recaptcha-alert', '1');
                                    alert.innerHTML =
                                        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">' +
                                            '<circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>' +
                                            '<line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round" stroke-linejoin="round"/>' +
                                            '<line x1="12" y1="16" x2="12.01" y2="16" stroke-linecap="round" stroke-linejoin="round"/>' +
                                        '</svg>' +
                                        '<div>Verifikasi reCAPTCHA gagal dimuat. Coba muat ulang halaman lalu kirim lagi.</div>';

                                    form.parentElement.insertBefore(alert, form);
                                });
                        });
                    });
                })();
            </script>
        @endif

        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const eyeIcon = document.getElementById('eye-icon');
                const eyeOffIcon = document.getElementById('eye-off-icon');
                const button = document.querySelector('.toggle-password');
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eyeIcon.style.display = isPassword ? 'none' : '';
                eyeOffIcon.style.display = isPassword ? '' : 'none';
                button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            }
        </script>
    </body>
</html>
