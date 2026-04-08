import { Link, usePage } from '@inertiajs/react';
import HomeLayout from '@/layouts/home-layout';
import { dashboard, login } from '@/routes';
import {
    DraggableCardBody,
    DraggableCardContainer,
} from '@/components/ui/draggable-card';

const YELLOW = '#E8C900';
const YELLOW_DIM = 'rgba(232,201,0,0.12)';

const BOOTH_PHOTOS = [
    {
        id: 1,
        src: 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=280&h=380&fit=crop&crop=faces',
        label: 'Studio Portrait',
        pos: 'top-[16px] left-[10px]',
    },
    {
        id: 2,
        src: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=280&h=380&fit=crop',
        label: 'Sesi Grup',
        pos: 'top-[8px] left-[220px]',
    },
    {
        id: 3,
        src: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=280&h=380&fit=crop&crop=faces',
        label: 'Foto Kabinet',
        pos: 'top-[258px] left-[28px]',
    },
    {
        id: 4,
        src: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=280&h=380&fit=crop&crop=faces',
        label: 'Potret Ekspresif',
        pos: 'top-[248px] left-[236px]',
    },
];

type Feature = {
    id: number;
    icon: string;
    title: string;
    description: string;
};

type Step = {
    id: number;
    number: string;
    title: string;
    description: string;
};

const ApertureIcon = ({
    size = 20,
    color = 'black',
}: {
    size?: number;
    color?: string;
}) => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke={color}
        strokeWidth={2}
        width={size}
        height={size}
    >
        <circle cx="12" cy="12" r="4" />
        <line x1="12" y1="2" x2="12" y2="8" strokeLinecap="round" />
        <line x1="12" y1="16" x2="12" y2="22" strokeLinecap="round" />
        <line x1="4.93" y1="4.93" x2="9.17" y2="9.17" strokeLinecap="round" />
        <line
            x1="14.83"
            y1="14.83"
            x2="19.07"
            y2="19.07"
            strokeLinecap="round"
        />
        <line x1="2" y1="12" x2="8" y2="12" strokeLinecap="round" />
        <line x1="16" y1="12" x2="22" y2="12" strokeLinecap="round" />
        <line x1="4.93" y1="19.07" x2="9.17" y2="14.83" strokeLinecap="round" />
        <line x1="14.83" y1="9.17" x2="19.07" y2="4.93" strokeLinecap="round" />
    </svg>
);

const ArrowRight = () => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth={2.5}
        className="size-4"
    >
        <path
            d="M5 12h14M12 5l7 7-7 7"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

/* Gradient warna tiap slot foto di strip */
const SLOT_GRADIENTS = [
    'linear-gradient(150deg, #f97316 0%, #ec4899 100%)',
    'linear-gradient(150deg, #14b8a6 0%, #6366f1 100%)',
    'linear-gradient(150deg, #a855f7 0%, #ec4899 100%)',
    'linear-gradient(150deg, #f59e0b 0%, #ef4444 100%)',
];

const CameraIcon = ({ className }: { className?: string }) => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth={1.5}
        className={className}
    >
        <path
            d="M3 9a2 2 0 0 1 2-2h1.5l1-2h5l1 2H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
        <circle
            cx="12"
            cy="13"
            r="3"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

const PhotoStripVisual = () => (
    <div className="relative mx-auto flex h-[520px] w-full max-w-sm items-center justify-center lg:mx-0">
        {/* Yellow blob glow */}
        <div
            className="pointer-events-none absolute -top-10 right-0 size-80 rounded-full blur-3xl"
            style={{ background: 'rgba(232,201,0,0.22)' }}
        />
        <div
            className="pointer-events-none absolute bottom-0 left-10 size-48 rounded-full blur-3xl"
            style={{ background: 'rgba(232,201,0,0.1)' }}
        />

        {/* Back strip (rotated, behind) */}
        <div
            className="absolute top-8 right-8 w-36 rotate-6 overflow-hidden rounded-2xl shadow-lg"
            style={{
                background: '#f0ede4',
                border: '1px solid rgba(0,0,0,0.07)',
            }}
        >
            <div className="bg-zinc-800 px-2 py-1.5 text-center">
                <span className="text-[8px] font-bold tracking-widest text-zinc-400">
                    ◆ PHILO ◆
                </span>
            </div>
            <div className="space-y-1.5 p-2">
                {SLOT_GRADIENTS.map((g, i) => (
                    <div
                        key={i}
                        className="h-20 rounded-lg opacity-50"
                        style={{ background: g }}
                    />
                ))}
            </div>
            <div className="bg-zinc-800 px-2 py-1 text-center">
                <span className="font-mono text-[7px] text-zinc-500">
                    PHOTOBOOTH
                </span>
            </div>
        </div>

        {/* Main front strip */}
        <div
            className="relative z-10 -rotate-2 overflow-hidden rounded-2xl shadow-2xl"
            style={{
                width: 156,
                background: 'white',
                border: '1px solid rgba(0,0,0,0.08)',
                boxShadow:
                    '0 24px 64px rgba(0,0,0,0.16), 0 4px 16px rgba(0,0,0,0.08)',
            }}
        >
            {/* Top band */}
            <div className="flex items-center justify-between bg-zinc-900 px-3 py-2">
                <div className="flex gap-1">
                    {[0, 1, 2].map((i) => (
                        <div
                            key={i}
                            className="size-1 rounded-full bg-white/25"
                        />
                    ))}
                </div>
                <span className="text-[8px] font-bold tracking-[0.2em] text-white/60">
                    PHILO
                </span>
                <div className="flex gap-1">
                    {[0, 1, 2].map((i) => (
                        <div
                            key={i}
                            className="size-1 rounded-full bg-white/25"
                        />
                    ))}
                </div>
            </div>

            {/* Film perforations + photos */}
            <div className="flex">
                <div className="flex w-4 shrink-0 flex-col justify-around bg-zinc-100 py-2">
                    {Array.from({ length: 10 }).map((_, i) => (
                        <div
                            key={i}
                            className="mx-auto h-2 w-2 rounded-sm bg-zinc-300"
                        />
                    ))}
                </div>
                <div className="flex-1 space-y-1.5 p-2">
                    {SLOT_GRADIENTS.map((g, i) => (
                        <div
                            key={i}
                            className="relative flex h-[88px] items-center justify-center overflow-hidden rounded-md"
                            style={{ background: g }}
                        >
                            <CameraIcon className="size-7 text-white/25" />
                            <span className="absolute right-1.5 bottom-1 font-mono text-[9px] text-white/40">
                                {String(i + 1).padStart(2, '0')}
                            </span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Bottom band */}
            <div className="flex items-center justify-between bg-zinc-900 px-3 py-2">
                <span className="font-mono text-[7px] text-yellow-400">
                    {new Date().toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                    })}
                </span>
                <span className="text-[7px] font-bold tracking-widest text-white/40">
                    PHOTOBOOTH
                </span>
            </div>
        </div>

        {/* Badge: booth online */}
        <div
            className="absolute -top-2 left-4 z-20 flex items-center gap-2 rounded-full px-3 py-1.5 shadow-lg"
            style={{
                background: 'white',
                border: '1px solid rgba(0,0,0,0.07)',
            }}
        >
            <span className="size-2 animate-pulse rounded-full bg-emerald-400" />
            <span className="text-xs font-semibold text-zinc-800">
                3 Booth Online
            </span>
        </div>

        {/* Badge: payment success */}
        <div
            className="absolute bottom-10 -left-4 z-20 rounded-xl px-3.5 py-2.5 shadow-xl"
            style={{
                background: 'white',
                border: '1px solid rgba(0,0,0,0.07)',
                minWidth: 160,
            }}
        >
            <div className="mb-1 flex items-center gap-1.5">
                <div className="flex size-5 items-center justify-center rounded-full bg-emerald-100">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#10b981"
                        strokeWidth={2.5}
                        className="size-3"
                    >
                        <path
                            d="M5 13l4 4L19 7"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                    </svg>
                </div>
                <span className="text-xs font-semibold text-zinc-800">
                    Pembayaran Berhasil
                </span>
            </div>
            <div className="flex items-baseline justify-between">
                <span className="text-lg font-extrabold text-zinc-900">
                    Rp 35.000
                </span>
                <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-600">
                    QRIS
                </span>
            </div>
            <div className="mt-1 text-[10px] text-zinc-400">
                Paket Strip 4 Foto · #0041
            </div>
        </div>

        {/* Badge: revenue */}
        <div
            className="absolute top-1/3 -right-2 z-20 rounded-xl px-3 py-2 shadow-lg"
            style={{
                background: 'white',
                border: '1px solid rgba(0,0,0,0.07)',
            }}
        >
            <div className="text-[10px] text-zinc-500">Revenue Hari Ini</div>
            <div className="text-base font-extrabold" style={{ color: YELLOW }}>
                Rp 1.2jt
            </div>
            <div className="mt-1 flex items-center gap-1 text-[10px] text-emerald-500">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={2}
                    className="size-3"
                >
                    <path
                        d="M7 17l10-10M17 7H7v10"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
                +18% vs kemarin
            </div>
        </div>
    </div>
);

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const _BoothMockupUnused = () => (
    <div className="relative mx-auto w-full max-w-sm lg:mx-0">
        <div
            className="pointer-events-none absolute -inset-8 rounded-3xl blur-3xl"
            style={{
                background:
                    'radial-gradient(circle, rgba(232,201,0,0.2) 0%, transparent 70%)',
            }}
        />

        <div
            className="relative overflow-hidden rounded-2xl border border-white/10"
            style={{
                background: 'rgba(10,10,10,0.95)',
                backdropFilter: 'blur(20px)',
            }}
        >
            {/* Card header */}
            <div className="flex items-center justify-between border-b border-white/8 px-4 py-3">
                <div className="flex items-center gap-2">
                    <div className="size-2 rounded-full bg-red-500/80" />
                    <div className="size-2 rounded-full bg-yellow-500/80" />
                    <div className="size-2 rounded-full bg-green-500/80" />
                </div>
                <span className="text-xs font-medium text-zinc-500">
                    philo dashboard
                </span>
                <div className="flex items-center gap-1.5">
                    <span
                        className="size-1.5 animate-pulse rounded-full"
                        style={{ background: YELLOW }}
                    />
                    <span className="text-xs text-zinc-500">Live</span>
                </div>
            </div>

            {/* Stats row */}
            <div className="grid grid-cols-3 gap-px border-b border-white/8 bg-white/5">
                {[
                    { label: 'Sesi Hari Ini', val: '24' },
                    { label: 'Revenue', val: 'Rp 1.2jt' },
                    { label: 'Booth Aktif', val: '3/3' },
                ].map((s) => (
                    <div key={s.label} className="bg-black/40 px-3 py-3">
                        <div
                            className="text-lg font-bold"
                            style={{ color: YELLOW }}
                        >
                            {s.val}
                        </div>
                        <div className="mt-0.5 text-xs text-zinc-600">
                            {s.label}
                        </div>
                    </div>
                ))}
            </div>

            {/* Session list */}
            <div className="space-y-2 p-3">
                {[
                    {
                        id: '#0041',
                        pkg: 'Paket 4 Foto',
                        status: 'Selesai',
                        time: '13:42',
                    },
                    {
                        id: '#0042',
                        pkg: 'Paket 6 Foto',
                        status: 'Berlangsung',
                        time: '14:05',
                    },
                    {
                        id: '#0043',
                        pkg: 'Paket 4 Foto',
                        status: 'Menunggu',
                        time: '14:10',
                    },
                ].map((s) => (
                    <div
                        key={s.id}
                        className="flex items-center justify-between rounded-xl border border-white/6 px-3 py-2.5"
                        style={{ background: 'rgba(255,255,255,0.03)' }}
                    >
                        <div className="flex items-center gap-2.5">
                            <div
                                className="flex size-7 shrink-0 items-center justify-center rounded-lg"
                                style={{ background: YELLOW_DIM }}
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke={YELLOW}
                                    strokeWidth={2}
                                    className="size-3.5"
                                >
                                    <path
                                        d="M3 9a2 2 0 0 1 2-2h1.5l1-2h5l1 2H17a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                    <circle
                                        cx="12"
                                        cy="13"
                                        r="3"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div className="text-xs font-semibold text-white">
                                    {s.pkg}
                                </div>
                                <div className="text-xs text-zinc-600">
                                    {s.id} · {s.time}
                                </div>
                            </div>
                        </div>
                        <span
                            className="rounded-full px-2 py-0.5 text-xs font-medium"
                            style={
                                s.status === 'Berlangsung'
                                    ? { background: YELLOW_DIM, color: YELLOW }
                                    : s.status === 'Selesai'
                                      ? {
                                            background: 'rgba(34,197,94,0.12)',
                                            color: '#4ade80',
                                        }
                                      : {
                                            background:
                                                'rgba(255,255,255,0.06)',
                                            color: '#71717a',
                                        }
                            }
                        >
                            {s.status}
                        </span>
                    </div>
                ))}
            </div>

            {/* QRIS card */}
            <div
                className="absolute -right-4 -bottom-4 rounded-xl border border-white/10 px-4 py-3 shadow-2xl"
                style={{
                    background: 'rgba(10,10,10,0.98)',
                    backdropFilter: 'blur(20px)',
                    minWidth: 140,
                }}
            >
                <div className="mb-1.5 flex items-center gap-2">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke={YELLOW}
                        strokeWidth={2}
                        className="size-3.5"
                    >
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <path d="M2 10h20" strokeLinecap="round" />
                    </svg>
                    <span className="text-xs font-semibold text-white">
                        QRIS Aktif
                    </span>
                </div>
                <div className="grid grid-cols-4 gap-0.5">
                    {Array.from({ length: 16 }).map((_, i) => (
                        <div
                            key={i}
                            className="rounded-sm"
                            style={{
                                width: 7,
                                height: 7,
                                background:
                                    i % 3 !== 0
                                        ? YELLOW
                                        : 'rgba(255,255,255,0.08)',
                            }}
                        />
                    ))}
                </div>
                <div className="mt-1.5 text-xs text-zinc-600">
                    Scan untuk bayar
                </div>
            </div>
        </div>

        {/* Floating badge */}
        <div
            className="absolute -top-4 -left-4 rounded-xl border border-white/10 px-3 py-2 shadow-xl"
            style={{
                background: 'rgba(10,10,10,0.98)',
                backdropFilter: 'blur(20px)',
            }}
        >
            <div className="flex items-center gap-2">
                <div
                    className="size-2 animate-pulse rounded-full"
                    style={{ background: '#4ade80' }}
                />
                <span className="text-xs font-medium text-white">
                    3 Booth Online
                </span>
            </div>
        </div>
    </div>
);

export default function Welcome() {
    const { auth, features, steps } = usePage<{
        auth: any;
        features: Feature[];
        steps: Step[];
    }>().props;
    const ctaHref = auth.user ? dashboard().url : login().url;

    return (
        <HomeLayout title="Kelola Photo Booth Lebih Mudah">
            {/* ── HERO ── */}
            <section className="relative flex min-h-screen items-center overflow-hidden px-6 pt-24 pb-16">
                <div className="pointer-events-none absolute inset-0">
                    <div
                        className="absolute top-0 right-0 size-[600px] rounded-full blur-3xl"
                        style={{
                            background:
                                'radial-gradient(circle, rgba(232,201,0,0.15) 0%, transparent 70%)',
                        }}
                    />
                    <div
                        className="absolute bottom-0 left-0 size-96 rounded-full blur-3xl"
                        style={{
                            background:
                                'radial-gradient(circle, rgba(232,201,0,0.1) 0%, transparent 70%)',
                        }}
                    />
                    <div
                        className="absolute inset-0"
                        style={{
                            backgroundImage:
                                'radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px)',
                            backgroundSize: '28px 28px',
                        }}
                    />
                    <div
                        className="absolute inset-x-0 top-1/2 h-px"
                        style={{
                            background:
                                'linear-gradient(to right, transparent, rgba(232,201,0,0.3), transparent)',
                        }}
                    />
                </div>

                <div className="relative mx-auto w-full max-w-7xl">
                    <div className="grid items-center gap-16 lg:grid-cols-2">
                        {/* Left — text */}
                        <div>
                            <div className="mb-7 inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-1.5 text-sm font-medium shadow-sm">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill={YELLOW}
                                    stroke="none"
                                    className="size-3.5"
                                >
                                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" />
                                </svg>
                                <span className="text-zinc-600">
                                    Platform manajemen photo booth #1
                                </span>
                            </div>

                            <h1 className="mb-6 text-5xl leading-[1.1] font-extrabold tracking-tight md:text-6xl lg:text-7xl">
                                Kelola Photo
                                <br />
                                Booth{' '}
                                <span style={{ color: YELLOW }}>
                                    Lebih Mudah
                                </span>
                            </h1>

                            <p className="mb-10 max-w-lg text-lg leading-relaxed text-zinc-600">
                                Satu platform untuk mengelola sesi foto,
                                pembayaran otomatis, template, multi-cabang, dan
                                laporan bisnis photo booth Anda.
                            </p>

                            <div className="flex flex-wrap gap-4">
                                <Link
                                    href={ctaHref}
                                    className="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-base font-bold text-black transition hover:-translate-y-0.5 hover:brightness-110 active:scale-95"
                                    style={{
                                        background: YELLOW,
                                        boxShadow: `0 4px 24px rgba(232,201,0,0.4)`,
                                    }}
                                >
                                    {auth.user
                                        ? 'Buka Dashboard'
                                        : 'Mulai Gratis'}
                                    <ArrowRight />
                                </Link>
                                <a
                                    href="#features"
                                    className="inline-flex items-center gap-2 rounded-full border border-zinc-300 bg-white px-8 py-3.5 text-base font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50"
                                >
                                    Lihat Fitur
                                </a>
                            </div>

                            <div className="mt-12 flex flex-wrap gap-6">
                                {[
                                    { val: '99.9%', label: 'Uptime' },
                                    { val: '< 1s', label: 'Respon Sistem' },
                                    { val: 'QRIS', label: 'Siap Pakai' },
                                    { val: '∞', label: 'Multi Cabang' },
                                ].map((s) => (
                                    <div
                                        key={s.label}
                                        className="flex items-center gap-2.5"
                                    >
                                        <div
                                            className="text-xl font-bold"
                                            style={{ color: YELLOW }}
                                        >
                                            {s.val}
                                        </div>
                                        <div className="text-sm text-zinc-500">
                                            {s.label}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Right — draggable photo cards */}
                        <div className="relative hidden h-[520px] lg:block">
                            {/* Ambient glow */}
                            <div
                                className="pointer-events-none absolute -top-10 right-0 size-80 rounded-full blur-3xl"
                                style={{ background: 'rgba(232,201,0,0.22)' }}
                            />
                            <div
                                className="pointer-events-none absolute bottom-0 left-10 size-48 rounded-full blur-3xl"
                                style={{ background: 'rgba(232,201,0,0.1)' }}
                            />

                            <DraggableCardContainer className="relative h-full w-full">
                                {BOOTH_PHOTOS.map((photo) => (
                                    <DraggableCardBody
                                        key={photo.id}
                                        className={`absolute ${photo.pos} h-[260px] min-h-0 w-[190px] cursor-grab bg-white p-2 active:cursor-grabbing`}
                                    >
                                        <img
                                            src={photo.src}
                                            alt={photo.label}
                                            draggable={false}
                                            className="h-[210px] w-full rounded object-cover select-none"
                                        />
                                        <p className="mt-1.5 text-center text-[11px] font-medium text-zinc-500">
                                            {photo.label}
                                        </p>
                                    </DraggableCardBody>
                                ))}
                            </DraggableCardContainer>

                            {/* Drag hint */}
                            <p className="absolute bottom-1 left-1/2 -translate-x-1/2 text-xs whitespace-nowrap text-zinc-400">
                                ☝️ Drag foto-foto ini!
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── PHOTO STRIP DIVIDER ── */}
            <div
                className="relative overflow-hidden border-y border-zinc-200 py-6"
                style={{ background: 'rgba(232,201,0,0.04)' }}
            >
                <div
                    className="flex animate-[scroll_20s_linear_infinite] gap-3"
                    style={{ width: 'max-content' }}
                >
                    {Array.from({ length: 16 }).map((_, i) => (
                        <div
                            key={i}
                            className="flex size-14 shrink-0 items-center justify-center rounded-lg border border-zinc-200"
                            style={{
                                background:
                                    i % 3 === 0
                                        ? YELLOW_DIM
                                        : 'rgba(0,0,0,0.03)',
                            }}
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke={
                                    i % 3 === 0 ? YELLOW : 'rgba(0,0,0,0.2)'
                                }
                                strokeWidth={1.5}
                                className="size-5"
                            >
                                <path
                                    d="M3 9a2 2 0 0 1 2-2h1.5l1-2h5l1 2H17a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                                <circle
                                    cx="12"
                                    cy="13"
                                    r="3"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                        </div>
                    ))}
                </div>
                <style>{`
                    @keyframes scroll {
                        from { transform: translateX(0); }
                        to { transform: translateX(-50%); }
                    }
                `}</style>
            </div>

            {/* ── FEATURES ── */}
            <section id="features" className="py-28">
                <div className="mx-auto max-w-6xl px-6">
                    <div className="mb-16 text-center">
                        <span
                            className="mb-3 inline-block rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5 text-sm font-semibold"
                            style={{ color: YELLOW }}
                        >
                            Fitur Lengkap
                        </span>
                        <h2 className="text-3xl font-bold text-zinc-900 md:text-4xl">
                            Semua yang Anda Butuhkan
                        </h2>
                        <p className="mt-3 text-zinc-500">
                            Dari pemilihan paket hingga laporan keuangan, semua
                            tersedia dalam satu sistem.
                        </p>
                    </div>

                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        {features.map((f) => (
                            <div
                                key={f.id}
                                className="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-300 hover:border-zinc-300 hover:shadow-md"
                            >
                                <div
                                    className="absolute inset-x-0 top-0 h-px opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    style={{
                                        background: `linear-gradient(to right, transparent, ${YELLOW}, transparent)`,
                                    }}
                                />
                                <div
                                    className="absolute -top-8 -right-8 size-24 rounded-full opacity-0 blur-2xl transition-opacity duration-300 group-hover:opacity-100"
                                    style={{ background: YELLOW_DIM }}
                                />
                                <div
                                    className="relative mb-4 inline-flex size-11 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                                    style={{
                                        background: YELLOW_DIM,
                                        color: YELLOW,
                                    }}
                                >
                                    <i className={`${f.icon} text-xl`} />
                                </div>
                                <h3 className="relative mb-2 font-semibold text-zinc-900">
                                    {f.title}
                                </h3>
                                <p className="relative text-sm leading-relaxed text-zinc-500">
                                    {f.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* ── HOW IT WORKS ── */}
            <section id="how" className="relative overflow-hidden py-28">
                <div
                    className="absolute inset-0"
                    style={{
                        background:
                            'linear-gradient(to bottom, transparent, rgba(232,201,0,0.05), transparent)',
                    }}
                />
                <div
                    className="absolute inset-0"
                    style={{
                        backgroundImage:
                            'linear-gradient(rgba(0,0,0,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.04) 1px, transparent 1px)',
                        backgroundSize: '60px 60px',
                    }}
                />

                <div className="relative mx-auto max-w-6xl px-6">
                    <div className="mb-16 text-center">
                        <span
                            className="mb-3 inline-block rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5 text-sm font-semibold"
                            style={{ color: YELLOW }}
                        >
                            Cara Kerja
                        </span>
                        <h2 className="text-3xl font-bold text-zinc-900 md:text-4xl">
                            Mulai dalam 4 Langkah
                        </h2>
                        <p className="mt-3 text-zinc-500">
                            Setup cepat, langsung operasional dari hari pertama.
                        </p>
                    </div>

                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        {steps.map((s, i) => (
                            <div key={s.id} className="group relative">
                                {i < steps.length - 1 && (
                                    <div className="absolute top-7 left-full z-10 hidden w-full -translate-x-1/2 border-t border-dashed border-zinc-300 lg:block" />
                                )}
                                <div className="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-300 hover:border-zinc-300 hover:shadow-md">
                                    <div className="relative mb-5 inline-flex">
                                        <div
                                            className="flex size-12 items-center justify-center rounded-full border border-yellow-200 text-sm font-extrabold"
                                            style={{
                                                color: YELLOW,
                                                background: YELLOW_DIM,
                                            }}
                                        >
                                            {s.number}
                                        </div>
                                        <div
                                            className="absolute inset-0 rounded-full opacity-60 blur-sm"
                                            style={{ background: YELLOW_DIM }}
                                        />
                                    </div>
                                    <h3 className="mb-2 font-semibold text-zinc-900">
                                        {s.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-zinc-500">
                                        {s.description}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* ── CTA ── */}
            <section className="relative overflow-hidden py-24">
                <div className="pointer-events-none absolute inset-0">
                    <div
                        className="absolute top-1/2 left-1/2 size-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl"
                        style={{
                            background:
                                'radial-gradient(circle, rgba(232,201,0,0.1) 0%, transparent 70%)',
                        }}
                    />
                    <div
                        className="absolute inset-x-0 top-0 h-px"
                        style={{
                            background:
                                'linear-gradient(to right, transparent, rgba(232,201,0,0.5), transparent)',
                        }}
                    />
                    <div
                        className="absolute inset-x-0 bottom-0 h-px"
                        style={{
                            background:
                                'linear-gradient(to right, transparent, rgba(232,201,0,0.5), transparent)',
                        }}
                    />
                </div>

                <div className="relative mx-auto max-w-3xl px-6 text-center">
                    <div
                        className="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl"
                        style={{
                            background: YELLOW,
                            boxShadow: `0 0 40px rgba(232,201,0,0.4)`,
                        }}
                    >
                        <ApertureIcon size={28} color="black" />
                    </div>

                    <h2 className="mb-4 text-4xl font-extrabold text-zinc-900 md:text-5xl">
                        Siap Kelola{' '}
                        <span style={{ color: YELLOW }}>Booth Anda?</span>
                    </h2>
                    <p className="mb-10 text-zinc-600">
                        Bergabung dan nikmati kemudahan manajemen photo booth
                        dengan teknologi terkini.
                    </p>
                    <Link
                        href={ctaHref}
                        className="inline-flex items-center gap-2 rounded-full px-10 py-4 text-base font-bold text-black transition hover:-translate-y-0.5 hover:brightness-110 active:scale-95"
                        style={{
                            background: YELLOW,
                            boxShadow: `0 4px 32px rgba(232,201,0,0.45)`,
                        }}
                    >
                        {auth.user ? 'Buka Dashboard' : 'Masuk Sekarang'}
                        <ArrowRight />
                    </Link>
                </div>
            </section>
        </HomeLayout>
    );
}
