<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? $siteName }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; padding: 0 16px !important; }
            .px-content { padding-left: 22px !important; padding-right: 22px !important; }
            h1 { font-size: 22px !important; }
            h2 { font-size: 17px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f4ed;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#27272a;line-height:1.55;">
    @if(! empty($preheader))
        <div style="display:none;font-size:1px;color:#f4f4ed;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
            {{ $preheader }}
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4ed;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- ─── Header ─── --}}
                    <tr>
                        <td style="padding:0 0 22px 0;text-align:center;">
                            @if($siteLogoUrl)
                                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" style="max-height:44px;width:auto;display:inline-block;border:0;outline:none;text-decoration:none;">
                            @else
                                {{-- Brand wordmark fallback when logo URL is not reachable (e.g. localhost) --}}
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                    <tr>
                                        <td style="background-color:#18181b;border-radius:10px;padding:10px 18px;">
                                            <span style="font-size:18px;font-weight:800;color:#ffffff;letter-spacing:-0.4px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                                                {{ $siteName }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>

                    {{-- ─── Body Card ─── --}}
                    <tr>
                        <td style="background-color:#ffffff;border-radius:14px;border:1px solid #e4e4e7;overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                {{-- Accent bar --}}
                                <tr>
                                    <td style="height:4px;background-color:#C9A800;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="px-content" style="padding:32px 36px;">
                                        @yield('body')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ─── Footer ─── --}}
                    <tr>
                        <td style="padding:24px 8px 8px;text-align:center;color:#71717a;font-size:12px;line-height:1.6;">
                            <p style="margin:0 0 6px;font-weight:600;color:#52525b;">{{ $siteName }}</p>
                            @if(! empty($footerText))
                                <p style="margin:0 0 10px;color:#71717a;">{{ $footerText }}</p>
                            @endif

                            @php
                                $socialLinks = array_filter([
                                    'Website' => $appUrl ?? null,
                                    'Instagram' => $socials['instagram'] ?? null,
                                    'Facebook' => $socials['facebook'] ?? null,
                                ]);
                            @endphp

                            @if(! empty($socialLinks))
                                <p style="margin:8px 0 0;">
                                    @foreach($socialLinks as $label => $url)
                                        <a href="{{ $url }}" style="color:#52525b;text-decoration:none;margin:0 6px;font-weight:500;">{{ $label }}</a>
                                        @if(! $loop->last)<span style="color:#d4d4d8;">|</span>@endif
                                    @endforeach
                                </p>
                            @endif

                            <p style="margin:14px 0 0;color:#a1a1aa;font-size:11px;">
                                Email otomatis. Tidak perlu dibalas.<br>
                                &copy; {{ date('Y') }} {{ $siteName }}. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
