<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php
            $siteName = $siteSettings['site_name'] ?? config('app.name', 'Philo Photobooth');
            $siteDescription = $siteSettings['site_description'] ?? ($siteName . ' - Photobooth terbaik untuk momen tak terlupakan.');
            $faviconPath = $siteSettings['favicon_path'] ?? null;
            $logoPath = $siteSettings['logo_path'] ?? null;
            $ogImage = $logoPath ? Storage::url($logoPath) : '/philo.png';
            $ogImageAbsolute = str_starts_with($ogImage, 'http') ? $ogImage : (request()->getSchemeAndHttpHost() . $ogImage);

            $faviconExt = strtolower(pathinfo($faviconPath ?? 'philo.png', PATHINFO_EXTENSION));
            $faviconType = match ($faviconExt) {
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                default => 'image/png',
            };
            $faviconUrl = $faviconPath ? Storage::url($faviconPath) : '/philo.png';
            // Cache-bust so admin changes propagate without manual clear.
            $faviconVersion = $faviconPath ? substr(md5($faviconPath), 0, 8) : 'default';
            $faviconUrl .= (str_contains($faviconUrl, '?') ? '&' : '?') . 'v=' . $faviconVersion;

            $currentUrl = url()->current();
        @endphp

        {{-- Open Graph / SEO (server-side for social media scrapers) --}}
        <meta name="description" content="{{ $siteDescription }}">
        <meta name="application-name" content="{{ $siteName }}">
        <meta name="theme-color" content="#E8C900">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $siteName }}">
        <meta property="og:description" content="{{ $siteDescription }}">
        <meta property="og:image" content="{{ $ogImageAbsolute }}">
        <meta property="og:image:secure_url" content="{{ $ogImageAbsolute }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="500">
        <meta property="og:image:height" content="500">
        <meta property="og:image:alt" content="{{ $siteName }}">
        <meta property="og:url" content="{{ $currentUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $siteName }}">
        <meta name="twitter:description" content="{{ $siteDescription }}">
        <meta name="twitter:image" content="{{ $ogImageAbsolute }}">
        <link rel="icon" href="{{ $faviconUrl }}" sizes="any" type="{{ $faviconType }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}" type="{{ $faviconType }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        <link rel="image_src" href="{{ $ogImageAbsolute }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ $siteName }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
