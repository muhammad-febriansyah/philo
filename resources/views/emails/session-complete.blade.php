@extends('emails.layout', ['preheader' => 'Hasil sesi foto Anda telah siap diunduh.'])

@section('body')
    <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#18181b;letter-spacing:-0.4px;">
        Hasil foto Anda sudah siap
    </h1>
    <p style="margin:0 0 22px;color:#52525b;font-size:14px;">
        Terima kasih telah menggunakan layanan {{ $siteName }}. Hasil sesi foto Anda dapat diunduh melalui tombol di bawah.
    </p>

    <p style="margin:0 0 26px;color:#3f3f46;font-size:14px;">
        Halo,
    </p>

    <p style="margin:0 0 24px;color:#52525b;font-size:14px;">
        Sesi foto Anda di cabang <strong style="color:#18181b;">{{ $branchName }}</strong> telah selesai diproses.
        File hasil foto telah kami simpan dan siap untuk diunduh kapan saja.
    </p>

    {{-- Download CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 26px;">
        <tr>
            <td align="center">
                <a href="{{ $downloadUrl }}"
                   style="display:inline-block;background-color:#18181b;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:14px 36px;border-radius:8px;letter-spacing:0.2px;">
                    Unduh Hasil Foto
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 22px;color:#71717a;font-size:12px;text-align:center;">
        Apabila tombol tidak berfungsi, salin tautan berikut ke peramban Anda:<br>
        <a href="{{ $downloadUrl }}" style="color:#a16207;text-decoration:underline;word-break:break-all;">{{ $downloadUrl }}</a>
    </p>

    {{-- Session details --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fafaf5;border:1px solid #e4e4e7;border-radius:10px;margin-bottom:18px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px;font-size:11px;font-weight:600;letter-spacing:1.5px;color:#a1a1aa;text-transform:uppercase;">
                    Rincian Sesi
                </p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:13px;color:#52525b;">
                    <tr>
                        <td style="padding:4px 0;width:130px;color:#71717a;">Cabang</td>
                        <td style="padding:4px 0;color:#18181b;font-weight:600;">{{ $branchName }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#71717a;">Waktu selesai</td>
                        <td style="padding:4px 0;color:#18181b;font-weight:600;">{{ $completedAt }}</td>
                    </tr>
                    @if(! empty($sessionId))
                        <tr>
                            <td style="padding:4px 0;color:#71717a;">ID sesi</td>
                            <td style="padding:4px 0;color:#18181b;font-weight:600;font-family:monospace;">#{{ $sessionId }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#71717a;font-size:13px;">
        Tautan unduh ini bersifat permanen. Simpan email ini bila Anda ingin mengunduh ulang foto di lain waktu.
        Apabila ada kendala, silakan hubungi cabang tempat Anda berfoto.
    </p>
@endsection
