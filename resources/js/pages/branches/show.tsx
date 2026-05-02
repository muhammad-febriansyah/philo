import { Link, usePage } from '@inertiajs/react';
import HomeLayout from '@/layouts/home-layout';

const YELLOW = '#E8C900';

type Branch = {
    id: number;
    name: string;
    code: string;
    address: string | null;
    phone: string | null;
    photo: string | null;
    photo_sessions_count?: number;
    transactions_count?: number;
};

const PhoneIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-4 shrink-0">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.37 19a19.45 19.45 0 0 1-6.91-6.91 19.79 19.79 0 0 1-2.91-8.45A2 2 0 0 1 3.59 2H6.6a2 2 0 0 1 2 1.72 12.6 12.6 0 0 0 .69 2.77 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.92-.92a2 2 0 0 1 2.11-.45 12.6 12.6 0 0 0 2.77.69A2 2 0 0 1 22 16.92z" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const ArrowUpRight = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5} className="size-3.5">
        <path d="M7 17L17 7M7 7h10v10" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const MapPinIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-4 shrink-0">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" strokeLinecap="round" strokeLinejoin="round" />
        <circle cx="12" cy="9" r="2.5" />
    </svg>
);

const CameraIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-5">
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" strokeLinecap="round" strokeLinejoin="round" />
        <circle cx="12" cy="13" r="4" />
    </svg>
);

const ClockIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-5">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 6v6l4 2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const QrIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-5">
        <rect x="3" y="3" width="7" height="7" rx="1" />
        <rect x="14" y="3" width="7" height="7" rx="1" />
        <rect x="3" y="14" width="7" height="7" rx="1" />
        <path d="M14 14h3M14 17v4M17 17v1M21 14v3M21 21h-4" strokeLinecap="round" />
    </svg>
);

const StarIcon = () => (
    <svg viewBox="0 0 24 24" fill="currentColor" className="size-4">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
    </svg>
);

const PrintIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-5">
        <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" strokeLinecap="round" strokeLinejoin="round" />
        <rect x="6" y="14" width="12" height="8" rx="1" />
    </svg>
);

const SHOW_HOURS = [
    { day: 'Senin – Jumat', hours: '10:00 – 22:00' },
    { day: 'Sabtu – Minggu', hours: '09:00 – 23:00' },
    { day: 'Hari Libur Nasional', hours: '09:00 – 23:00' },
];

const SERVICES = [
    { icon: <CameraIcon />, label: 'Foto cetak instan', desc: 'Hasil foto siap dibawa pulang' },
    { icon: <PrintIcon />, label: 'Berbagai ukuran cetak', desc: 'Photo Strip, 4R, A4, A3' },
    { icon: <QrIcon />, label: 'Pembayaran QRIS', desc: 'Cepat & tanpa kembalian' },
    { icon: <ClockIcon />, label: 'Proses 10–15 menit', desc: 'Dari setup sampai cetak' },
];

function formatNumber(n?: number): string {
    if (!n || n < 1000) return String(n ?? 0);
    if (n < 10000) return (n / 1000).toFixed(1).replace('.0', '') + 'rb';
    return Math.round(n / 1000) + 'rb';
}

export default function BranchShow() {
    const { branch, otherBranches } = usePage<{
        branch: Branch;
        otherBranches: Branch[];
    }>().props;

    const sessions = branch.photo_sessions_count ?? 0;
    const transactions = branch.transactions_count ?? 0;
    const boothUrl = `/booth/${branch.code}`;

    return (
        <HomeLayout title={branch.name}>
            {/* ── HERO ── */}
            <section className="relative min-h-[78vh] overflow-hidden">
                {/* Background image or fallback */}
                {branch.photo ? (
                    <img
                        src={branch.photo}
                        alt={branch.name}
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                ) : (
                    <div
                        className="absolute inset-0"
                        style={{ background: 'linear-gradient(160deg, #f5f2e8 0%, #fff8cf 100%)' }}
                    >
                        <div
                            className="absolute inset-0"
                            style={{
                                backgroundImage: 'radial-gradient(rgba(0,0,0,0.04) 1px, transparent 1px)',
                                backgroundSize: '28px 28px',
                            }}
                        />
                        <div className="absolute inset-0 flex items-center justify-center">
                            <span
                                className="select-none text-[28vw] font-extrabold leading-none opacity-20"
                                style={{ fontFamily: "'Poppins', sans-serif", color: YELLOW }}
                            >
                                {branch.name.slice(0, 1)}
                            </span>
                        </div>
                    </div>
                )}

                {/* Gradient overlay */}
                <div
                    className="absolute inset-0"
                    style={{
                        background: branch.photo
                            ? 'linear-gradient(to bottom, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.15) 30%, rgba(0,0,0,0.85) 100%)'
                            : 'linear-gradient(to bottom, transparent 30%, rgba(0,0,0,0.55) 100%)',
                    }}
                />

                {/* Breadcrumb */}
                <div className="absolute inset-x-0 top-0 px-6 pt-32 sm:px-8">
                    <div className="mx-auto max-w-6xl">
                        <nav className="flex items-center gap-2 text-sm text-white/65">
                            <Link href="/" className="transition hover:text-white">Beranda</Link>
                            <span className="text-white/30">›</span>
                            <Link href="/cabang" className="transition hover:text-white">Cabang</Link>
                            <span className="text-white/30">›</span>
                            <span className="font-semibold text-white/95">{branch.name}</span>
                        </nav>
                    </div>
                </div>

                {/* Hero Content */}
                <div className="absolute inset-x-0 bottom-0 px-6 pb-14 sm:px-8 md:pb-20">
                    <div className="mx-auto max-w-6xl">
                        {/* Pills row */}
                        <div className="mb-5 flex flex-wrap items-center gap-2">
                            <span
                                className="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold tracking-widest text-black uppercase"
                                style={{ background: YELLOW }}
                            >
                                <span className="size-1.5 rounded-full bg-black" />
                                {branch.code}
                            </span>
                            <span className="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-black/40 px-3 py-1.5 text-[11px] font-semibold text-white/85 backdrop-blur-md">
                                <span className="relative flex size-2">
                                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75" />
                                    <span className="relative inline-flex size-2 rounded-full bg-emerald-400" />
                                </span>
                                Buka Sekarang
                            </span>
                            <span className="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-black/40 px-3 py-1.5 text-[11px] font-semibold text-white/85 backdrop-blur-md">
                                <StarIcon />
                                <span style={{ color: YELLOW }}>4.9</span>
                                <span className="text-white/60">/5</span>
                            </span>
                        </div>

                        {/* Name */}
                        <h1
                            className="text-4xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-5xl md:text-7xl"
                            style={{ fontFamily: "'Poppins', sans-serif", textShadow: '0 2px 24px rgba(0,0,0,0.35)' }}
                        >
                            {branch.name}
                        </h1>

                        {/* Address */}
                        {branch.address && (
                            <div className="mt-5 flex items-start gap-2 text-white/80">
                                <span className="mt-0.5"><MapPinIcon /></span>
                                <span className="max-w-2xl text-sm md:text-base">{branch.address}</span>
                            </div>
                        )}

                        {/* CTAs */}
                        <div className="mt-8 flex flex-wrap gap-3">
                            <Link
                                href={boothUrl}
                                className="group inline-flex items-center gap-2 rounded-full px-7 py-3.5 text-sm font-bold text-black transition hover:-translate-y-0.5 hover:brightness-105 active:scale-95"
                                style={{ background: YELLOW, boxShadow: '0 6px 28px rgba(232,201,0,0.55)' }}
                            >
                                <CameraIcon />
                                Mulai Sesi Foto
                                <span className="-mr-1 transition group-hover:translate-x-1">→</span>
                            </Link>
                            {branch.phone && (
                                <a
                                    href={`tel:${branch.phone}`}
                                    className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-6 py-3.5 text-sm font-bold text-white backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                                >
                                    <PhoneIcon />
                                    Hubungi Cabang
                                </a>
                            )}
                            {branch.address && (
                                <a
                                    href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(branch.address)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-6 py-3.5 text-sm font-bold text-white backdrop-blur-md transition hover:bg-white/20 active:scale-95"
                                >
                                    <MapPinIcon />
                                    Petunjuk Arah
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── STATS STRIP ── */}
            <section className="relative -mt-12 px-6 sm:px-8">
                <div className="mx-auto max-w-6xl">
                    <div className="grid grid-cols-2 gap-3 rounded-[1.5rem] border border-zinc-200 bg-white p-5 shadow-xl shadow-zinc-900/5 sm:grid-cols-4 sm:p-6">
                        <Stat
                            icon={<CameraIcon />}
                            value={formatNumber(sessions)}
                            label="Sesi foto"
                            sub="terdokumentasi"
                            tone="yellow"
                        />
                        <Stat
                            icon={<PrintIcon />}
                            value={formatNumber(transactions)}
                            label="Transaksi"
                            sub="sukses dilayani"
                            tone="green"
                        />
                        <Stat
                            icon={<StarIcon />}
                            value="4.9"
                            label="Rating"
                            sub="dari pengunjung"
                            tone="amber"
                        />
                        <Stat
                            icon={<ClockIcon />}
                            value="~10m"
                            label="Rata-rata"
                            sub="durasi sesi foto"
                            tone="blue"
                        />
                    </div>
                </div>
            </section>

            {/* ── SERVICES + INFO ── */}
            <section className="px-6 py-16 sm:px-8 md:py-20">
                <div className="mx-auto max-w-6xl">
                    <div className="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                        {/* Services */}
                        <div className="rounded-[2rem] border border-zinc-200 bg-white p-7 shadow-sm md:p-9">
                            <div className="flex items-center gap-2">
                                <span className="size-1.5 rounded-full" style={{ background: YELLOW }} />
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">Yang Tersedia</p>
                            </div>
                            <h2
                                className="mt-2 text-2xl font-extrabold tracking-tight text-zinc-950 md:text-3xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Layanan di cabang ini
                            </h2>
                            <p className="mt-2 max-w-xl text-sm text-zinc-500">
                                Semua sesi foto di {branch.name} mengikuti alur yang sama: pilih paket, ambil foto, lalu cetak instan dari mesin booth.
                            </p>

                            <div className="mt-7 grid gap-3 sm:grid-cols-2">
                                {SERVICES.map((s) => (
                                    <div
                                        key={s.label}
                                        className="group flex items-start gap-3 rounded-2xl border border-zinc-100 bg-zinc-50/60 p-4 transition hover:-translate-y-0.5 hover:border-zinc-300 hover:bg-white hover:shadow-md"
                                    >
                                        <span
                                            className="flex size-10 shrink-0 items-center justify-center rounded-xl text-zinc-700 transition group-hover:scale-110"
                                            style={{ background: 'rgba(232,201,0,0.16)' }}
                                        >
                                            {s.icon}
                                        </span>
                                        <div>
                                            <p className="text-sm font-bold text-zinc-950">{s.label}</p>
                                            <p className="mt-0.5 text-xs text-zinc-500">{s.desc}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Right column: Hours + Contact */}
                        <div className="flex flex-col gap-6">
                            {/* Operating hours */}
                            <div className="rounded-[2rem] border border-zinc-200 bg-white p-7 shadow-sm md:p-8">
                                <div className="flex items-center gap-2">
                                    <span className="flex size-9 items-center justify-center rounded-xl text-zinc-700" style={{ background: 'rgba(232,201,0,0.16)' }}>
                                        <ClockIcon />
                                    </span>
                                    <div>
                                        <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">Jam Operasional</p>
                                        <p className="text-sm font-bold text-zinc-950">Setiap hari</p>
                                    </div>
                                </div>
                                <ul className="mt-5 divide-y divide-zinc-100">
                                    {SHOW_HOURS.map((h) => (
                                        <li key={h.day} className="flex items-center justify-between py-3 text-sm">
                                            <span className="text-zinc-500">{h.day}</span>
                                            <span className="font-bold text-zinc-950">{h.hours}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>

                            {/* Contact card */}
                            {(branch.phone || branch.address) && (
                                <div
                                    className="relative overflow-hidden rounded-[2rem] p-7 text-white shadow-sm md:p-8"
                                    style={{ background: 'linear-gradient(145deg, #18181b 0%, #09090b 100%)' }}
                                >
                                    <div
                                        className="pointer-events-none absolute -top-20 -right-20 size-56 rounded-full blur-3xl"
                                        style={{ background: 'rgba(232,201,0,0.18)' }}
                                    />
                                    <div className="relative">
                                        <p className="text-[10px] font-semibold tracking-[0.22em] text-white/40 uppercase">Hubungi Kami</p>
                                        {branch.phone && (
                                            <a
                                                href={`tel:${branch.phone}`}
                                                className="group mt-3 flex items-center gap-3 transition hover:opacity-90"
                                            >
                                                <span
                                                    className="flex size-10 items-center justify-center rounded-xl"
                                                    style={{ background: 'rgba(232,201,0,0.18)', color: YELLOW }}
                                                >
                                                    <PhoneIcon />
                                                </span>
                                                <div>
                                                    <p className="text-[11px] tracking-widest text-white/40 uppercase">Telepon</p>
                                                    <p className="text-base font-bold">{branch.phone}</p>
                                                </div>
                                            </a>
                                        )}
                                        {branch.address && (
                                            <div className="mt-4 flex items-start gap-3">
                                                <span
                                                    className="mt-0.5 flex size-10 items-center justify-center rounded-xl"
                                                    style={{ background: 'rgba(232,201,0,0.18)', color: YELLOW }}
                                                >
                                                    <MapPinIcon />
                                                </span>
                                                <div>
                                                    <p className="text-[11px] tracking-widest text-white/40 uppercase">Alamat</p>
                                                    <p className="text-sm text-white/85">{branch.address}</p>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── MAPS ── */}
            {branch.address && (
                <section className="px-6 pb-16 sm:px-8">
                    <div className="mx-auto max-w-6xl">
                        <div className="overflow-hidden rounded-[2rem] border border-zinc-200 shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 bg-white px-5 py-4 sm:px-6">
                                <div className="flex items-center gap-2.5 text-sm font-semibold text-zinc-700">
                                    <MapPinIcon />
                                    {branch.address}
                                </div>
                                <a
                                    href={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(branch.address)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex items-center gap-1.5 rounded-full border border-zinc-200 bg-zinc-50 px-3.5 py-1.5 text-xs font-semibold text-zinc-600 transition hover:border-zinc-900 hover:text-zinc-900"
                                >
                                    Buka di Maps
                                    <ArrowUpRight />
                                </a>
                            </div>
                            <iframe
                                title={`Lokasi ${branch.name}`}
                                src={`https://maps.google.com/maps?q=${encodeURIComponent(branch.address)}&t=&z=15&ie=UTF8&iwloc=&output=embed`}
                                className="h-80 w-full border-0 sm:h-96"
                                loading="lazy"
                                allowFullScreen
                            />
                        </div>
                    </div>
                </section>
            )}

            {/* ── CTA ── */}
            <section className="px-6 pb-16 sm:px-8">
                <div className="mx-auto max-w-6xl">
                    <div
                        className="relative overflow-hidden rounded-[2rem] px-6 py-12 text-center sm:px-12 md:py-16"
                        style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 60%, #18181b 100%)' }}
                    >
                        <div
                            className="pointer-events-none absolute -top-24 left-1/2 size-72 -translate-x-1/2 rounded-full blur-3xl"
                            style={{ background: 'rgba(232,201,0,0.18)' }}
                        />
                        <div className="relative">
                            <span
                                className="inline-block rounded-full px-3 py-1 text-[11px] font-bold tracking-widest text-black uppercase"
                                style={{ background: YELLOW }}
                            >
                                Siap berfoto?
                            </span>
                            <h2
                                className="mt-4 text-3xl font-extrabold leading-tight text-white md:text-5xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Mulai sesi di {branch.name}
                            </h2>
                            <p className="mx-auto mt-3 max-w-xl text-sm text-white/60 md:text-base">
                                Pilih paket, ambil foto, dan langsung cetak. Pembayaran QRIS, hasilnya bisa diunduh & dicetak instan.
                            </p>
                            <Link
                                href={boothUrl}
                                className="group mt-7 inline-flex items-center gap-2 rounded-full px-8 py-4 text-base font-extrabold text-black transition hover:-translate-y-0.5 hover:brightness-105 active:scale-95"
                                style={{ background: YELLOW, boxShadow: '0 12px 36px rgba(232,201,0,0.45)' }}
                            >
                                <CameraIcon />
                                Buka Booth Sekarang
                                <span className="transition group-hover:translate-x-1">→</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── OTHER BRANCHES ── */}
            {otherBranches.length > 0 && (
                <section className="px-6 pb-20 sm:px-8">
                    <div className="mx-auto max-w-6xl">
                        <div className="mb-7 flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">Lokasi lain</p>
                                <h3
                                    className="mt-1 text-2xl font-extrabold tracking-tight text-zinc-950 md:text-3xl"
                                    style={{ fontFamily: "'Poppins', sans-serif" }}
                                >
                                    Cabang Philo lainnya
                                </h3>
                            </div>
                            <Link
                                href="/cabang"
                                className="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:border-zinc-900 hover:text-zinc-900"
                            >
                                Lihat semua cabang
                                <ArrowUpRight />
                            </Link>
                        </div>

                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {otherBranches.map((item) => (
                                <Link
                                    key={item.id}
                                    href={`/cabang/${item.code}`}
                                    className="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-zinc-900 hover:shadow-xl"
                                >
                                    {/* Photo */}
                                    <div className="relative aspect-[4/3] overflow-hidden bg-zinc-100">
                                        {item.photo ? (
                                            <img
                                                src={item.photo}
                                                alt={item.name}
                                                className="size-full object-cover transition duration-500 group-hover:scale-105"
                                            />
                                        ) : (
                                            <div
                                                className="flex size-full items-center justify-center"
                                                style={{ background: 'linear-gradient(160deg, #fef9c3 0%, #fef08a 100%)' }}
                                            >
                                                <span
                                                    className="text-7xl font-extrabold opacity-30"
                                                    style={{ fontFamily: "'Poppins', sans-serif", color: '#b45309' }}
                                                >
                                                    {item.name.slice(0, 1)}
                                                </span>
                                            </div>
                                        )}
                                        <span
                                            className="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-widest text-black uppercase"
                                            style={{ background: YELLOW }}
                                        >
                                            {item.code}
                                        </span>
                                    </div>
                                    {/* Body */}
                                    <div className="p-5">
                                        <p className="text-base font-extrabold text-zinc-950">{item.name}</p>
                                        <p className="mt-1 line-clamp-2 text-xs text-zinc-500">
                                            {item.address || 'Alamat belum tersedia'}
                                        </p>
                                        <div className="mt-4 flex items-center justify-between">
                                            {item.phone ? (
                                                <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-600">
                                                    <PhoneIcon />
                                                    {item.phone}
                                                </span>
                                            ) : <span />}
                                            <span className="inline-flex size-7 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 text-zinc-400 transition group-hover:border-zinc-900 group-hover:bg-zinc-900 group-hover:text-white">
                                                <ArrowUpRight />
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </HomeLayout>
    );
}

type StatProps = {
    icon: React.ReactNode;
    value: string;
    label: string;
    sub: string;
    tone: 'yellow' | 'green' | 'amber' | 'blue';
};

const TONE_BG: Record<StatProps['tone'], string> = {
    yellow: 'rgba(232,201,0,0.16)',
    green: 'rgba(34,197,94,0.14)',
    amber: 'rgba(245,158,11,0.14)',
    blue: 'rgba(59,130,246,0.12)',
};

const TONE_FG: Record<StatProps['tone'], string> = {
    yellow: '#a16207',
    green: '#15803d',
    amber: '#b45309',
    blue: '#1d4ed8',
};

function Stat({ icon, value, label, sub, tone }: StatProps) {
    return (
        <div className="flex items-center gap-3 sm:gap-4">
            <span
                className="flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-12"
                style={{ background: TONE_BG[tone], color: TONE_FG[tone] }}
            >
                {icon}
            </span>
            <div className="min-w-0">
                <p className="text-[9px] font-semibold tracking-[0.18em] text-zinc-400 uppercase">{label}</p>
                <p
                    className="text-xl font-extrabold tracking-tight text-zinc-950 sm:text-2xl"
                    style={{ fontFamily: "'Poppins', sans-serif" }}
                >
                    {value}
                </p>
                <p className="mt-0.5 truncate text-[10px] text-zinc-500">{sub}</p>
            </div>
        </div>
    );
}
