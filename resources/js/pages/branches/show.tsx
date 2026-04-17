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

const FEATURES = ['Foto cetak instan', 'Template pilihan', 'Pembayaran QRIS', 'Proses 10–15 menit'];

export default function BranchShow() {
    const { branch, otherBranches } = usePage<{
        branch: Branch;
        otherBranches: Branch[];
    }>().props;

    return (
        <HomeLayout title={branch.name}>

            {/* ── HERO ── */}
            <section className="relative min-h-[88vh] overflow-hidden">
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

                {/* Gradient overlay — dark at bottom */}
                <div
                    className="absolute inset-0"
                    style={{
                        background: branch.photo
                            ? 'linear-gradient(to bottom, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.05) 30%, rgba(0,0,0,0.72) 100%)'
                            : 'linear-gradient(to bottom, transparent 40%, rgba(0,0,0,0.55) 100%)',
                    }}
                />

                {/* Breadcrumb — top */}
                <div className="absolute top-0 left-0 right-0 px-8 pt-36">
                    <div className="mx-auto max-w-6xl">
                        <nav className="flex items-center gap-2 text-sm text-white/60">
                            <Link href="/" className="transition hover:text-white/90">Beranda</Link>
                            <span className="text-white/30">›</span>
                            <Link href="/cabang" className="transition hover:text-white/90">Cabang</Link>
                            <span className="text-white/30">›</span>
                            <span className="font-semibold text-white/90">{branch.name}</span>
                        </nav>
                    </div>
                </div>

                {/* Content — bottom */}
                <div className="absolute inset-x-0 bottom-0 px-8 pb-14">
                    <div className="mx-auto max-w-6xl">
                        {/* Code badge */}
                        <span
                            className="mb-4 inline-block rounded-full px-3 py-1 text-[11px] font-bold tracking-widest text-black uppercase"
                            style={{ background: YELLOW }}
                        >
                            {branch.code}
                        </span>

                        {/* Name */}
                        <h1
                            className="text-5xl font-extrabold leading-none text-white md:text-7xl"
                            style={{ fontFamily: "'Poppins', sans-serif", textShadow: '0 2px 24px rgba(0,0,0,0.3)' }}
                        >
                            {branch.name}
                        </h1>

                        {/* Address */}
                        {branch.address && (
                            <div className="mt-4 flex items-center gap-2 text-white/70">
                                <MapPinIcon />
                                <span className="text-sm">{branch.address}</span>
                            </div>
                        )}

                        {/* Buttons */}
                        {branch.phone && (
                            <div className="mt-7 flex flex-wrap gap-3">
                                <a
                                    href={`tel:${branch.phone}`}
                                    className="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-sm font-bold text-black transition hover:-translate-y-0.5 hover:brightness-105 active:scale-95"
                                    style={{ background: YELLOW, boxShadow: '0 6px 28px rgba(232,201,0,0.5)' }}
                                >
                                    <PhoneIcon />
                                    Hubungi Cabang
                                </a>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            {/* ── INFO BAR ── */}
            <section className="border-b border-zinc-100 bg-white px-8 py-6">
                <div className="mx-auto max-w-6xl">
                    <div className="flex flex-wrap items-center justify-between gap-6">
                        {/* Contact */}
                        {branch.phone && (
                            <div className="flex items-center gap-3">
                                <span
                                    className="flex size-9 shrink-0 items-center justify-center rounded-xl text-zinc-500"
                                    style={{ background: 'rgba(232,201,0,0.12)' }}
                                >
                                    <PhoneIcon />
                                </span>
                                <div>
                                    <p className="text-[10px] font-semibold tracking-[0.18em] text-zinc-400 uppercase">Kontak</p>
                                    <p className="text-sm font-bold text-zinc-950">{branch.phone}</p>
                                </div>
                            </div>
                        )}

                        {/* Feature pills */}
                        <div className="flex flex-wrap gap-2">
                            {FEATURES.map((f) => (
                                <span
                                    key={f}
                                    className="rounded-full border border-zinc-200 bg-zinc-50 px-3.5 py-1.5 text-xs font-semibold text-zinc-600"
                                >
                                    {f}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── MAPS ── */}
            {branch.address && (
                <section className="px-8 py-10">
                    <div className="mx-auto max-w-6xl">
                        <div className="overflow-hidden rounded-[2rem] border border-zinc-200 shadow-sm">
                            <div className="flex items-center justify-between border-b border-zinc-100 bg-white px-6 py-4">
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
                                className="h-80 w-full border-0"
                                loading="lazy"
                                allowFullScreen
                            />
                        </div>
                    </div>
                </section>
            )}

            {/* ── BOTTOM CARDS ── */}
            <section className="px-8 py-16">
                <div className="mx-auto max-w-6xl">
                    <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">

                        {/* Experience card */}
                        <div
                            className="relative overflow-hidden rounded-[2rem] p-8 text-white"
                            style={{ background: 'linear-gradient(145deg, #18181b 0%, #09090b 100%)' }}
                        >
                            <div
                                className="pointer-events-none absolute -top-16 -right-16 size-48 rounded-full blur-3xl"
                                style={{ background: 'rgba(232,201,0,0.12)' }}
                            />
                            <div className="relative">
                                <div
                                    className="mb-5 inline-flex size-10 items-center justify-center rounded-xl"
                                    style={{ background: 'rgba(232,201,0,0.15)' }}
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-5">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </div>
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-white/40 uppercase">Tentang Cabang</p>
                                <div className="mt-4 space-y-3 text-sm leading-relaxed text-white/65">
                                    <p>
                                        Cabang ini dirancang untuk memberi pengalaman photobooth
                                        yang cepat, mudah dipakai, dan tetap terasa menyenangkan
                                        untuk semua tamu.
                                    </p>
                                    <p>
                                        Cocok untuk sesi santai, event komunitas, perayaan keluarga,
                                        maupun aktivasi brand yang butuh alur rapi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Other branches */}
                        <div className="rounded-[2rem] border border-zinc-200 bg-white p-8 shadow-sm">
                            <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">Cabang Lainnya</p>
                            <div className="mt-5 space-y-3">
                                {otherBranches.length > 0 ? (
                                    otherBranches.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={`/cabang/${item.code}`}
                                            className="group flex items-center justify-between rounded-2xl border border-zinc-100 bg-zinc-50 px-5 py-4 transition hover:border-zinc-900 hover:bg-white"
                                        >
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-bold text-zinc-950">{item.name}</p>
                                                <p className="mt-0.5 truncate text-xs text-zinc-400">
                                                    {item.address || 'Alamat belum tersedia'}
                                                </p>
                                            </div>
                                            <span className="ml-4 flex size-7 shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-400 shadow-sm transition group-hover:border-zinc-900 group-hover:text-zinc-900">
                                                <ArrowUpRight />
                                            </span>
                                        </Link>
                                    ))
                                ) : (
                                    <p className="text-sm text-zinc-400">Belum ada cabang lain yang ditampilkan.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </HomeLayout>
    );
}
