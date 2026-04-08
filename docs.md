Flow Customer di Booth

1. LANDING SCREEN (idle/attract mode)  
   │ Layar booth tampilkan logo + "Tap untuk mulai"  
   │
2. PILIH CABANG (auto-detect dari device/URL)  
   │ Booth sudah tahu dia cabang mana via URL: booth.app/branch/jakarta-1  
   │
3. PILIH PAKET  
   │ - Paket Strip (2x6 foto) — Rp 20.000  
   │ - Paket A4 (4 foto grid) — Rp 35.000  
   │ - Paket A3 (6 foto grid) — Rp 50.000  
   │
4. BAYAR (QRIS)  
   │ - System create transaction → hit Duitku API  
   │ - Tampilkan QR code di layar
   │ - Customer scan pakai e-wallet/m-banking  
   │ - Polling/websocket tunggu konfirmasi
   │ - Duitku webhook → payment confirmed ✓  
   │
5. SESI FOTO  
   │ - Countdown 3..2..1 → ambil foto dari webcam  
   │ - Ulangi sesuai jumlah foto di paket  
   │ - Preview setiap foto, bisa retake  
   │
6. PILIH FRAME/TEMPLATE  
   │ - Tampilkan pilihan frame yang tersedia
   │ - Customer tap pilih frame favorit  
   │ - Preview foto + frame digabung  
   │
7. PREVIEW FINAL  
   │ - Tampilkan hasil akhir (foto + frame)  
   │ - Tombol "Cetak" dan "Cetak & Kirim Email"
   │
8. CETAK  
   │ - Server compose image final (300 DPI)  
   │ - Kirim ke printer  
   │ - Tampilkan "Foto sedang dicetak..."
   │
9. SELESAI  
   │ Kembali ke landing screen (idle)

Flow Admin Dashboard

ADMIN LOGIN  
 ├── Dashboard (overview semua cabang)
│ ├── Total transaksi hari ini  
 │ ├── Revenue per cabang
│ └── Status booth (online/offline)  
 │  
 ├── Kelola Cabang
│ ├── CRUD cabang (nama, alamat, kode)  
 │ └── Assign device ke cabang
│  
 ├── Kelola Paket
│ ├── CRUD paket (nama, harga, jumlah foto, ukuran cetak)  
 │ └── Aktif/nonaktif per cabang
│  
 ├── Kelola Template/Frame
│ ├── Upload frame (PNG/SVG)  
 │ ├── Atur slot posisi foto  
 │ ├── Preview template
│ └── Assign ke paket/cabang  
 │  
 ├── Transaksi  
 │ ├── List semua transaksi
│ ├── Filter per cabang, tanggal, status  
 │ └── Detail (foto, payment info)
│  
 └── Laporan  
 ├── Revenue harian/bulanan  
 ├── Per cabang  
 └── Export CSV

Flow System (Backend)

[Booth Browser] → POST /api/booth/session/start {branch_id, package_id}  
 → Create transaction → Hit Duitku API → Return QR code

[Duitku] → POST /webhook/duitku {order_id, status}  
 → Validate signature → Update transaction → Broadcast event

[Booth Browser] → Polling GET /api/booth/payment/{id}/status
→ Return paid ✓ → Booth mulai sesi foto

[Booth Browser] → POST /api/booth/photo/capture {session_id, photo_base64}  
 → Save foto ke storage

[Booth Browser] → POST /api/booth/session/compose {session_id, template_id}
→ Server gabung foto + frame → Generate print-ready image  
 → Return image URL

[Booth Browser] → Print via window.print() / download

MENU
├── Dashboard ← sudah ada
│
├── MASTER DATA
│ ├── Cabang ← CRUD cabang
│ ├── Paket Foto ← CRUD + Detail paket (harga, jumlah foto, ukuran) pakai ajax,datatable serverside ,sweetalert
│ └── Template / Frame ← Upload & kelola frame
│
├── OPERASIONAL
│ ├── Transaksi ← List semua transaksi, filter status/cabang
│ └── Sesi Foto ← List sesi, lihat foto hasil, download
│
├── LAPORAN
│ ├── Revenue ← Grafik pendapatan harian/bulanan
│ └── Per Cabang ← Breakdown per cabang
│
├── PENGATURAN
│ ├── Pengaturan Umum ← Nama web, logo, favicon, warna
│ ├── Pengaturan Pembayaran ← API key Duitku, sandbox mode
│ └── Pengguna ← CRUD user admin & operator

Prioritas Build

┌──────────┬────────────────┬──────────────────────────────────────────┐
│ Priority │ Halaman │ Alasan │
├──────────┼────────────────┼──────────────────────────────────────────┤
│ 1 │ Cabang │ Dibutuhkan sebelum apapun bisa jalan │
├──────────┼────────────────┼──────────────────────────────────────────┤
│ 2 │ Paket Foto │ Customer harus pilih paket saat di booth │  
 ├──────────┼────────────────┼──────────────────────────────────────────┤  
 │ 3 │ Template/Frame │ Diperlukan sebelum sesi foto │  
 ├──────────┼────────────────┼──────────────────────────────────────────┤  
 │ 4 │ Pengaturan │ Input API key Duitku │
├──────────┼────────────────┼──────────────────────────────────────────┤  
 │ 5 │ Transaksi │ Monitor pembayaran │
├──────────┼────────────────┼──────────────────────────────────────────┤  
 │ 6 │ Laporan │ Analytics │

1.  Fitur yang Belum Ada

Payment & Keuangan

- Callback/Webhook Duitku — Tidak ada endpoint untuk menerima callback dari Duitku saat pembayaran berhasil. Saat ini
  hanya polling dari frontend, yang tidak reliable.
- Refund/pembatalan — Tidak ada mekanisme refund transaksi.
- Laporan keuangan per cabang — ReportController ada tapi halaman React-nya belum ada (hanya controller, belum ada
  page).
- Export laporan (CSV/PDF) — Belum ada fitur export untuk laporan transaksi/revenue.

Photobooth Workflow

- Image compositing server-side — Belum ada logic untuk menggabungkan foto dengan template frame di server
  (GD/Imagick). completeSession() hanya menyimpan base64, belum ada proses compositing nyata.
- Print integration — Setting print_enabled ada tapi tidak ada logic untuk mengirim print job ke printer.
- Watermark — Setting booth_watermark_path ada tapi belum diimplementasi.
- Retake foto — Belum ada mekanisme untuk mengulang foto tertentu saat capturing.
- Countdown timer — Setting ada (booth_countdown_seconds) tapi perlu dipastikan implementasinya di frontend.
- Idle timeout — Setting ada (booth_idle_timeout_seconds) tapi perlu dipastikan implementasinya di frontend.

Admin Panel

- Halaman admin CRUD — Controller ada untuk branches, packages, templates, users, transactions, photo-sessions, tapi
  halaman React/Inertia-nya belum ada (hanya dashboard.tsx yang ada, itupun placeholder). Semua CRUD masih bergantung
  pada DataTables AJAX, bukan Inertia pages.
- Halaman Reports — Controller ada tapi page React belum dibuat.
- Halaman Settings (General & Payment) — Controller ada tapi page React belum dibuat.

Multi-Cabang

- Branch-specific settings — Saat ini settings global saja. Belum ada settings per cabang (misal: jam operasional,
  paket khusus cabang, harga berbeda per cabang).
- Package per cabang — Tidak ada relasi branch_id di tabel packages. Semua cabang pakai paket yang sama.
- Template per cabang — Tidak ada relasi branch_id di tabel templates. Semua cabang pakai template yang sama.
- Monitoring cabang real-time — Tidak ada status online/offline booth per cabang.
- Inventory management — Tidak ada tracking kertas foto, tinta printer per cabang.

User & Akses

- Permission yang lebih granular — Hanya ada middleware EnsureUserIsAdmin. Role operator dan cabang belum punya
  permission terpisah yang jelas.
- Operator belum bisa akses apa-apa — Tidak ada halaman/fitur khusus operator.
- Activity log/audit trail — Tidak ada logging aktivitas user (siapa edit apa, kapan).

Customer

- Email delivery foto — Field customer_email ada di photo_sessions tapi tidak ada logic pengiriman email.
- Customer gallery/download link — Tidak ada halaman public untuk customer mengakses/download foto mereka.
- Customer feedback/rating — Tidak ada.

Teknis

- Queue/Job — Tidak ada background jobs. Proses berat seperti image compositing, email, seharusnya pakai queue.
- Event/Listener — Tidak ada event system (misal: TransactionPaid event → trigger session creation, email
  notification).
- API untuk booth hardware — Jika booth adalah perangkat fisik, mungkin perlu API terpisah.
- Backup foto — Tidak ada strategi backup untuk foto-foto yang tersimpan.
- Rate limiting — Hanya ada untuk login, belum untuk API booth.
- Testing — Perlu dicek apakah ada test yang sudah ditulis.

---

2. Prioritas Rekomendasi

┌───────────┬─────────────────────────────────────────────────────────────────┐
│ Prioritas │ Item │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Kritis │ Payment callback/webhook Duitku │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Kritis │ Halaman admin React (CRUD branches, packages, templates, users) │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Tinggi │ Image compositing server-side (GD/Imagick) │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Tinggi │ Email pengiriman foto ke customer │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Tinggi │ Customer download/gallery page │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Tinggi │ Halaman reports & settings di admin │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Sedang │ Package & template per cabang │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Sedang │ Print integration │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Sedang │ Queue/Jobs untuk proses berat │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Sedang │ Activity log │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Rendah │ Granular permissions │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Rendah │ Real-time booth monitoring │
├───────────┼─────────────────────────────────────────────────────────────────┤
│ Rendah │ Export laporan CSV/PDF │
└───────────┴─────────────────────────────────────────────────────────────────┘

https://lanishathomas.github.io/photobooth-website/booth/sea/stickers
