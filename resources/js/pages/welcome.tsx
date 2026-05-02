import { Link, usePage } from '@inertiajs/react';
import { motion } from 'motion/react';
import HomeLayout from '@/layouts/home-layout';
import { dashboard, login } from '@/routes';
import {
    DraggableCardBody,
    DraggableCardContainer,
} from '@/components/ui/draggable-card';

const SPRING_EASE = [0.22, 1, 0.36, 1] as const;
const EASE_OUT = 'easeOut' as const;

const fadeUp = (delay = 0) => ({
    initial: { opacity: 0, y: 28 },
    animate: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: SPRING_EASE, delay },
});

const fadeIn = (delay = 0) => ({
    initial: { opacity: 0 },
    animate: { opacity: 1 },
    transition: { duration: 0.7, ease: EASE_OUT, delay },
});

const slideRight = (delay = 0) => ({
    initial: { opacity: 0, x: 40 },
    animate: { opacity: 1, x: 0 },
    transition: { duration: 0.7, ease: SPRING_EASE, delay },
});

const YELLOW = '#E8C900';
const YELLOW_DIM = 'rgba(232,201,0,0.12)';

type Gallery = { id: number; title: string; image_path: string };

const CARD_TRANSFORMS = [
    { rotate: '-26deg', tx: '-8px', ty: '14px' },
    { rotate: '-16deg', tx: '-4px', ty: '8px' },
    { rotate: '-7deg',  tx: '2px',  ty: '12px' },
    { rotate: '6deg',   tx: '6px',  ty: '8px' },
    { rotate: '16deg',  tx: '2px',  ty: '4px' },
    { rotate: '-2deg',  tx: '4px',  ty: '0px' },
];

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

        {/* Badge: rating */}
        <div
            className="absolute top-1/3 -right-2 z-20 rounded-xl px-3 py-2 shadow-lg"
            style={{
                background: 'white',
                border: '1px solid rgba(0,0,0,0.07)',
            }}
        >
            <div className="text-[10px] text-zinc-500">Rating Pelanggan</div>
            <div className="flex items-center gap-1 mt-0.5">
                {[1,2,3,4,5].map((s) => (
                    <svg key={s} viewBox="0 0 24 24" className="size-3.5" fill={YELLOW} stroke={YELLOW} strokeWidth={1}>
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                ))}
            </div>
            <div className="mt-1 text-[10px] text-zinc-400">
                4.9 dari <span className="font-semibold text-zinc-600">500+</span> ulasan
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

const PROFILE_ITEMS = [
    {
        id: 1,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <path d="M3 9a2 2 0 0 1 2-2h1.5l1-2h5l1 2H19a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z" strokeLinecap="round" strokeLinejoin="round" />
                <circle cx="12" cy="13" r="3" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        ),
        title: 'Studio Photo Booth Modern',
        description: 'Dilengkapi perangkat kamera profesional, pencahayaan studio, dan layar sentuh interaktif untuk pengalaman foto terbaik.',
    },
    {
        id: 2,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <rect x="3" y="3" width="18" height="18" rx="2" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M3 9h18M9 21V9" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        ),
        title: '50+ Template Eksklusif',
        description: 'Pilih dari puluhan desain frame premium sesuai tema — pernikahan, wisuda, ulang tahun, atau momen kasual sehari-hari.',
    },
    {
        id: 3,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <rect x="2" y="5" width="20" height="14" rx="2" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M2 10h20M6 15h4" strokeLinecap="round" />
            </svg>
        ),
        title: 'Pembayaran QRIS Instan',
        description: 'Bayar mudah lewat QRIS, proses otomatis, dan sesi foto langsung dimulai tanpa antre lama.',
    },
    {
        id: 4,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <polyline points="6 9 6 2 18 2 18 9" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" strokeLinecap="round" strokeLinejoin="round" />
                <rect x="6" y="14" width="12" height="8" rx="1" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        ),
        title: 'Cetak Foto Langsung',
        description: 'Hasil cetak berkualitas tinggi tersedia dalam hitungan menit, siap dibawa pulang sebagai kenangan nyata.',
    },
    {
        id: 5,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" strokeLinecap="round" strokeLinejoin="round" />
                <polyline points="9 22 9 12 15 12 15 22" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        ),
        title: 'Multi-Cabang',
        description: 'Hadir di berbagai kota dengan standar layanan yang sama — profesional, bersih, dan ramah keluarga.',
    },
    {
        id: 6,
        icon: (
            <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.37 19a19.45 19.45 0 0 1-6.91-6.91 19.79 19.79 0 0 1-2.91-8.45A2 2 0 0 1 3.59 2H6.6a2 2 0 0 1 2 1.72 12.6 12.6 0 0 0 .69 2.77 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.92-.92a2 2 0 0 1 2.11-.45 12.6 12.6 0 0 0 2.77.69A2 2 0 0 1 22 16.92z" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        ),
        title: 'Dukungan Penuh',
        description: 'Tim kami siap membantu dari pemilihan paket hingga setelah sesi selesai, memastikan pengalaman yang menyenangkan.',
    },
];

export default function Welcome() {
    const { auth, galleries } = usePage<{
        auth: any;
        galleries: Gallery[];
    }>().props;
    const ctaHref = auth.user ? dashboard().url : login().url;

    return (
        <HomeLayout title="Abadikan Setiap Momen Spesial">
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

                <div className="relative mx-auto w-full max-w-6xl">
                    <div className="grid items-center gap-16 lg:grid-cols-2">
                        {/* Left — text */}
                        <div>
                            {/* Badge */}
                            <motion.div {...fadeUp(0.1)} className="mb-7 inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-medium">
                                <svg viewBox="0 0 24 24" fill={YELLOW} stroke="none" className="size-3.5">
                                    <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" />
                                </svg>
                                <span className="text-zinc-600">Photo Booth Profesional #1</span>
                            </motion.div>

                            <motion.h1 {...fadeUp(0.2)} className="mb-6 text-5xl leading-[1.1] font-extrabold tracking-tight md:text-6xl lg:text-7xl">
                                Abadikan
                                <br />
                                Setiap{' '}
                                <span style={{ color: YELLOW }}>Momen</span>
                                <br />
                                Spesialmu
                            </motion.h1>

                            <motion.p {...fadeUp(0.35)} className="mb-10 max-w-lg text-lg leading-relaxed text-zinc-600">
                                Wedding, wisuda, ulang tahun, atau sekadar foto bareng — studio photo booth kami siap mengabadikan momen terbaikmu jadi kenangan seumur hidup.
                            </motion.p>

                            {/* CTA buttons */}
                            <motion.div {...fadeUp(0.45)} className="flex flex-wrap gap-3">
                                <Link
                                    href={ctaHref}
                                    className="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-base font-bold text-black transition hover:-translate-y-0.5 hover:brightness-110 active:scale-95"
                                    style={{ background: YELLOW, boxShadow: '0 4px 24px rgba(232,201,0,0.4)' }}
                                >
                                    {auth.user ? 'Buka Dashboard' : 'Pesan Sekarang'}
                                    <ArrowRight />
                                </Link>
                                <a
                                    href="/harga"
                                    className="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-zinc-700 transition hover:bg-zinc-100 active:scale-95"
                                >
                                    Lihat Paket
                                </a>
                            </motion.div>

                            {/* Stats strip */}
                            <motion.div {...fadeUp(0.55)} className="mt-12 inline-flex flex-wrap items-center divide-x divide-zinc-200 overflow-hidden rounded-2xl bg-white">
                                {[
                                    { val: '500+', label: 'Sesi Foto' },
                                    { val: '50+',  label: 'Template' },
                                    { val: 'QRIS', label: 'Pembayaran' },
                                    { val: '4.9★', label: 'Rating' },
                                ].map((s) => (
                                    <div key={s.label} className="flex flex-col items-center px-5 py-3">
                                        <span className="text-base font-extrabold" style={{ color: YELLOW }}>{s.val}</span>
                                        <span className="text-xs text-zinc-500">{s.label}</span>
                                    </div>
                                ))}
                            </motion.div>
                        </div>

                        {/* Right — polaroid deck */}
                        <motion.div {...slideRight(0.3)} className="relative hidden h-[620px] items-center justify-center lg:flex">
                            {/* Yellow blob background */}
                            <div
                                className="pointer-events-none absolute inset-0 rounded-[3rem]"
                                style={{ background: 'radial-gradient(ellipse 70% 60% at 55% 50%, rgba(232,201,0,0.22), transparent 70%)' }}
                            />
                            {/* Subtle dot pattern */}
                            <div
                                className="pointer-events-none absolute inset-0 rounded-[3rem] opacity-40"
                                style={{ backgroundImage: 'radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px)', backgroundSize: '20px 20px' }}
                            />

                            <DraggableCardContainer className="relative flex h-full w-full items-center justify-center">
                                {galleries.slice(0, 6).map((photo, index) => {
                                    const t = CARD_TRANSFORMS[index % CARD_TRANSFORMS.length];
                                    return (
                                        <div
                                            key={photo.id}
                                            className="absolute top-1/2 left-1/2"
                                            style={{
                                                transform: `translate(calc(-50% + ${t.tx}), calc(-50% + ${t.ty})) rotate(${t.rotate})`,
                                                zIndex: index + 1,
                                            }}
                                        >
                                            <DraggableCardBody className="h-[420px] min-h-0 w-[290px] cursor-grab bg-white p-3 pb-10 shadow-2xl active:cursor-grabbing">
                                                <img
                                                    src={`/storage/${photo.image_path}`}
                                                    alt={photo.title}
                                                    draggable={false}
                                                    className="h-[355px] w-full rounded object-cover select-none"
                                                />
                                                <p className="mt-2 text-center text-[10px] font-semibold tracking-[0.15em] text-zinc-400 uppercase">
                                                    {photo.title}
                                                </p>
                                            </DraggableCardBody>
                                        </div>
                                    );
                                })}
                            </DraggableCardContainer>

                            {/* Floating badge — booth online */}
                            <motion.div {...fadeIn(0.8)} className="absolute top-6 left-4 z-20 flex items-center gap-2 rounded-full bg-white px-3.5 py-2 shadow-lg">
                                <span className="size-2 animate-pulse rounded-full bg-emerald-400" />
                                <span className="text-xs font-semibold text-zinc-800">Booth Online</span>
                            </motion.div>

                            {/* Floating badge — payment */}
                            <motion.div {...fadeUp(1.0)} className="absolute bottom-16 -left-2 z-20 min-w-[160px] rounded-2xl bg-white px-4 py-3 shadow-xl">
                                <div className="mb-1 flex items-center gap-1.5">
                                    <div className="flex size-5 items-center justify-center rounded-full bg-emerald-100">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" strokeWidth={2.5} className="size-3">
                                            <path d="M5 13l4 4L19 7" strokeLinecap="round" strokeLinejoin="round" />
                                        </svg>
                                    </div>
                                    <span className="text-xs font-semibold text-zinc-800">Pembayaran Berhasil</span>
                                </div>
                                <div className="flex items-baseline justify-between gap-3">
                                    <span className="text-lg font-extrabold text-zinc-900">Rp 35.000</span>
                                    <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-600">QRIS</span>
                                </div>
                                <div className="mt-0.5 text-[10px] text-zinc-400">Paket Strip 4 Foto · #0041</div>
                            </motion.div>

                            {/* Floating badge — rating */}
                            <motion.div {...fadeIn(0.9)} className="absolute top-1/3 -right-2 z-20 rounded-xl bg-white px-3 py-2 shadow-lg" style={{ border: '1px solid rgba(0,0,0,0.07)' }}>
                                <div className="text-[10px] text-zinc-500">Rating Pelanggan</div>
                                <div className="flex items-center gap-0.5 mt-0.5">
                                    {[1,2,3,4,5].map((s) => (
                                        <svg key={s} viewBox="0 0 24 24" className="size-3.5" fill={YELLOW} stroke={YELLOW} strokeWidth={1}>
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                    ))}
                                </div>
                                <div className="mt-1 text-[10px] text-zinc-400">
                                    4.9 dari <span className="font-semibold text-zinc-600">500+</span> ulasan
                                </div>
                            </motion.div>

                            <p className="absolute bottom-3 left-1/2 -translate-x-1/2 text-xs whitespace-nowrap text-zinc-400">
                                ☝️ Tarik kartu untuk lihat lainnya
                            </p>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* ── MARQUEE ── */}
            <div className="relative overflow-hidden py-5" style={{ background: YELLOW }}>
                <div
                    className="flex animate-[marquee_25s_linear_infinite] gap-0 whitespace-nowrap"
                    style={{ width: 'max-content' }}
                >
                    {Array.from({ length: 4 }).flatMap((_, gi) =>
                        ['Wedding', 'Wisuda', 'Ulang Tahun', 'Keluarga', 'Corporate Event', 'Prewedding', 'Komunitas', 'Brand Activation'].map((label) => (
                            <span key={`${gi}-${label}`} className="inline-flex items-center gap-3 px-6 text-sm font-bold tracking-widest text-black/70 uppercase">
                                {label}
                                <span className="text-black/30">✦</span>
                            </span>
                        ))
                    )}
                </div>
                <style>{`
                    @keyframes marquee {
                        from { transform: translateX(0); }
                        to { transform: translateX(-50%); }
                    }
                `}</style>
            </div>

            {/* ── CARA KERJA ── */}
            <section className="py-24 px-6">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-14 text-center">
                        <span
                            className="mb-3 inline-block rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5 text-sm font-semibold"
                            style={{ color: YELLOW }}
                        >
                            Cara Kerja
                        </span>
                        <h2 className="text-3xl font-bold text-zinc-900 md:text-4xl">
                            Foto selesai dalam 3 langkah
                        </h2>
                        <p className="mt-3 text-zinc-500">
                            Proses cepat, mudah, tanpa ribet — langsung cetak di tempat.
                        </p>
                    </div>

                    <div className="relative grid gap-8 md:grid-cols-3">
                        {/* Connector line (desktop) */}
                        <div
                            className="pointer-events-none absolute top-9 left-[calc(16.67%+1rem)] right-[calc(16.67%+1rem)] hidden h-px md:block"
                            style={{ background: 'linear-gradient(to right, transparent, rgba(232,201,0,0.4), rgba(232,201,0,0.4), transparent)' }}
                        />

                        {[
                            {
                                step: '01',
                                title: 'Pilih Paket',
                                desc: 'Tentukan jumlah foto dan template frame yang kamu inginkan sesuai momen.',
                                icon: (
                                    <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                                        <rect x="3" y="3" width="7" height="7" rx="1" strokeLinecap="round" strokeLinejoin="round" />
                                        <rect x="14" y="3" width="7" height="7" rx="1" strokeLinecap="round" strokeLinejoin="round" />
                                        <rect x="3" y="14" width="7" height="7" rx="1" strokeLinecap="round" strokeLinejoin="round" />
                                        <path d="M14 17.5h7M17.5 14v7" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                ),
                            },
                            {
                                step: '02',
                                title: 'Bayar via QRIS',
                                desc: 'Scan QRIS, bayar lewat dompet digital atau m-banking mana saja. Proses otomatis.',
                                icon: (
                                    <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                                        <rect x="2" y="5" width="20" height="14" rx="2" strokeLinecap="round" strokeLinejoin="round" />
                                        <path d="M2 10h20" strokeLinecap="round" />
                                        <path d="M6 15h4" strokeLinecap="round" />
                                    </svg>
                                ),
                            },
                            {
                                step: '03',
                                title: 'Cetak & Bawa Pulang',
                                desc: 'Foto langsung dicetak dalam hitungan menit. Siap jadi kenangan nyata.',
                                icon: (
                                    <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={1.8} className="size-6">
                                        <polyline points="6 9 6 2 18 2 18 9" strokeLinecap="round" strokeLinejoin="round" />
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" strokeLinecap="round" strokeLinejoin="round" />
                                        <rect x="6" y="14" width="12" height="8" rx="1" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                ),
                            },
                        ].map(({ step, title, desc, icon }, i) => (
                            <motion.div
                                key={step}
                                initial={{ opacity: 0, y: 32 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true, margin: '-60px' }}
                                transition={{ duration: 0.55, ease: SPRING_EASE, delay: i * 0.12 }}
                                className="relative flex flex-col items-center text-center"
                            >
                                {/* Step number + icon */}
                                <div className="relative mb-6">
                                    <div
                                        className="flex size-18 items-center justify-center rounded-2xl"
                                        style={{ background: 'rgba(232,201,0,0.12)' }}
                                    >
                                        {icon}
                                    </div>
                                    <span
                                        className="absolute -top-2.5 -right-2.5 flex size-6 items-center justify-center rounded-full text-[10px] font-black text-black"
                                        style={{ background: YELLOW }}
                                    >
                                        {step}
                                    </span>
                                </div>
                                <h3 className="mb-2 text-lg font-bold text-zinc-900">{title}</h3>
                                <p className="text-sm leading-relaxed text-zinc-500">{desc}</p>
                            </motion.div>
                        ))}
                    </div>

                    {/* Bottom CTA */}
                    <div className="mt-14 text-center">
                        <a
                            href="/harga"
                            className="inline-flex items-center gap-2 rounded-full px-8 py-3.5 text-sm font-bold text-black transition hover:-translate-y-0.5 hover:brightness-105 active:scale-95"
                            style={{ background: YELLOW, boxShadow: '0 4px 24px rgba(232,201,0,0.35)' }}
                        >
                            Lihat Paket Harga
                            <ArrowRight />
                        </a>
                    </div>
                </div>
            </section>

            {/* ── PROFIL ── */}
            <section id="features" className="py-28">
                <div className="mx-auto max-w-6xl px-6">
                    <div className="mb-16 text-center">
                        <span
                            className="mb-3 inline-block rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5 text-sm font-semibold"
                            style={{ color: YELLOW }}
                        >
                            Tentang Kami
                        </span>
                        <h2 className="text-3xl font-bold text-zinc-900 md:text-4xl">
                            Kenapa Pilih Philo Photobooth?
                        </h2>
                        <p className="mt-3 text-zinc-500">
                            Kami hadir untuk menjadikan setiap momen spesialmu
                            tak terlupakan dengan layanan foto terbaik.
                        </p>
                    </div>

                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        {PROFILE_ITEMS.map((f, i) => (
                            <motion.div
                                key={f.id}
                                initial={{ opacity: 0, y: 24 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true, margin: '-50px' }}
                                transition={{ duration: 0.5, ease: SPRING_EASE, delay: i * 0.08 }}
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
                                    style={{ background: YELLOW_DIM }}
                                >
                                    {f.icon}
                                </div>
                                <h3 className="relative mb-2 font-semibold text-zinc-900">
                                    {f.title}
                                </h3>
                                <p className="relative text-sm leading-relaxed text-zinc-500">
                                    {f.description}
                                </p>
                            </motion.div>
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
