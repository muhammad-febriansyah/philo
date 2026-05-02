@extends('emails.layout', ['preheader' => 'Konfigurasi notifikasi email berhasil diuji.'])

@section('body')
    <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#18181b;letter-spacing:-0.4px;">
        Konfigurasi email berhasil
    </h1>
    <p style="margin:0 0 18px;color:#52525b;font-size:14px;">
        Sistem berhasil mengirim email uji ke alamat tujuan melalui Mailketing.
    </p>

    <p style="margin:0 0 16px;color:#3f3f46;font-size:14px;">
        Halo,
    </p>
    <p style="margin:0 0 22px;color:#52525b;font-size:14px;">
        Email ini dikirim sebagai uji coba dari sistem <strong style="color:#18181b;">{{ $siteName }}</strong>.
        Jika Anda menerima email ini, berarti pengaturan notifikasi email sudah berfungsi dengan baik dan siap dipakai untuk
        notifikasi transaksi maupun konfirmasi sesi foto.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fafaf5;border:1px solid #e4e4e7;border-radius:10px;margin-bottom:18px;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px;font-size:11px;font-weight:600;letter-spacing:1.5px;color:#a1a1aa;text-transform:uppercase;">
                    Detail Pengiriman
                </p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:13px;color:#52525b;">
                    <tr>
                        <td style="padding:4px 0;width:130px;color:#71717a;">Penerima</td>
                        <td style="padding:4px 0;color:#18181b;font-weight:600;">{{ $recipient }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#71717a;">Waktu pengiriman</td>
                        <td style="padding:4px 0;color:#18181b;font-weight:600;">{{ $sentAt }}</td>
                    </tr>
                    <tr>
                        <td style="padding:4px 0;color:#71717a;">Layanan</td>
                        <td style="padding:4px 0;color:#18181b;font-weight:600;">Mailketing</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0;color:#71717a;font-size:13px;">
        Apabila Anda tidak meminta email uji ini, abaikan saja. Tidak ada tindakan lebih lanjut yang perlu dilakukan.
    </p>
@endsection
