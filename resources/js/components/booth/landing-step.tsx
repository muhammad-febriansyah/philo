import { Camera, Image, Minus, Plus, Printer, QrCode, Sparkles } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface TemplatePreview {
    id: number;
    name: string;
    thumbnail_path: string | null;
}

interface Props {
    branchName: string;
    siteName: string;
    logoUrl: string | null;
    templates: TemplatePreview[];
    galleryImages: string[];
    basePrice: number;
    extraPrintPrice: number;
    maxExtraPrints: number;
    starting: boolean;
    onStart: (extraPrints: number) => void;
}

const YELLOW = '#E8C900';

const FALLBACK_BG_IMAGES = [
    'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=1920&q=80',
    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1920&q=80',
    'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1920&q=80',
    'https://images.unsplash.com/photo-1531058020387-3be344556be6?auto=format&fit=crop&w=1920&q=80',
    'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1920&q=80',
];

const FEATURES = [
    { icon: QrCode, label: 'Bayar QRIS', sub: 'Instant & aman' },
    { icon: Image, label: 'Pilih Frame', sub: 'Banyak pilihan' },
    { icon: Camera, label: 'Ambil Foto', sub: 'Preview langsung' },
    { icon: Sparkles, label: 'Download', sub: 'Via QR code' },
];

function formatRupiah(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
}

export default function LandingStep({
    branchName,
    siteName,
    logoUrl,
    galleryImages,
    basePrice,
    extraPrintPrice,
    maxExtraPrints,
    starting,
    onStart,
}: Props) {
    const bgImages = galleryImages.length > 0 ? galleryImages : FALLBACK_BG_IMAGES;
    const [bgIndex, setBgIndex] = useState(0);
    const [fading, setFading] = useState(false);
    const [extraPrints, setExtraPrints] = useState(0);

    useEffect(() => {
        const id = setInterval(() => {
            setFading(true);
            setTimeout(() => {
                setBgIndex((i) => (i + 1) % bgImages.length);
                setFading(false);
            }, 700);
        }, 5000);
        return () => clearInterval(id);
    }, [bgImages.length]);

    const totalPrints = 1 + extraPrints;
    const totalPrice = useMemo(
        () => basePrice + extraPrintPrice * extraPrints,
        [basePrice, extraPrintPrice, extraPrints],
    );

    const decrement = () => setExtraPrints((n) => Math.max(0, n - 1));
    const increment = () =>
        setExtraPrints((n) => Math.min(maxExtraPrints, n + 1));

    const handleStart = () => {
        if (!starting) {
            onStart(extraPrints);
        }
    };

    return (
        <div className="relative flex h-screen flex-col items-center justify-center overflow-hidden">
            {/* Background image */}
            <div
                className="absolute inset-0 z-0 bg-cover bg-center transition-opacity duration-700"
                style={{
                    backgroundImage: `url(${bgImages[bgIndex]})`,
                    opacity: fading ? 0 : 1,
                }}
            />

            {/* Overlays */}
            <div className="absolute inset-0 z-10 bg-black/55" />
            <div className="absolute inset-0 z-10 bg-gradient-to-t from-black/80 via-transparent to-black/20" />
            <div
                className="pointer-events-none absolute inset-0 z-10 opacity-[0.03]"
                style={{
                    backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                }}
            />

            {/* BG dots indicator */}
            <div className="absolute top-6 right-6 z-20 flex gap-1.5">
                {bgImages.map((_, i) => (
                    <button
                        key={i}
                        onClick={() => setBgIndex(i)}
                        className="h-1.5 rounded-full transition-all duration-500"
                        style={{
                            width: i === bgIndex ? 20 : 6,
                            background: i === bgIndex ? YELLOW : 'rgba(255,255,255,0.4)',
                        }}
                    />
                ))}
            </div>

            {/* Main content */}
            <div className="relative z-20 flex w-full max-w-2xl flex-col items-center gap-7 px-6 text-center">
                {/* Logo */}
                <div className="relative">
                    <div
                        className="absolute inset-0 animate-ping rounded-full"
                        style={{ background: 'rgba(232,201,0,0.20)', animationDuration: '2.5s' }}
                    />
                    <div
                        className="relative flex h-24 w-24 items-center justify-center rounded-full shadow-2xl ring-4 ring-white/10"
                        style={{ background: YELLOW }}
                    >
                        {logoUrl ? (
                            <img
                                src={logoUrl}
                                alt={siteName}
                                className="h-16 w-16 rounded-full object-cover"
                            />
                        ) : (
                            <Camera className="h-12 w-12 text-black" strokeWidth={1.5} />
                        )}
                    </div>
                </div>

                {/* Badge */}
                <div className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                    <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400" />
                    <span className="text-sm font-semibold text-white/80">Booth siap digunakan</span>
                </div>

                {/* Title */}
                <div>
                    <h1
                        className="text-5xl font-extrabold leading-tight tracking-tight text-white drop-shadow-lg md:text-6xl"
                        style={{ fontFamily: "'Poppins', sans-serif" }}
                    >
                        {siteName}
                    </h1>
                    <p className="mt-2 text-lg font-semibold text-white/70">{branchName}</p>
                </div>

                {/* Print counter */}
                {maxExtraPrints > 0 && (
                    <div className="flex items-center gap-3 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur-md">
                        <Printer className="h-4 w-4 text-white/70" />
                        <span className="text-sm font-semibold text-white/80">
                            Cetak
                        </span>
                        <div className="flex items-center gap-2">
                            <button
                                onClick={decrement}
                                disabled={extraPrints === 0 || starting}
                                className="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-white transition active:scale-90 disabled:opacity-30"
                                aria-label="Kurangi cetakan"
                            >
                                <Minus className="h-3.5 w-3.5" />
                            </button>
                            <span
                                className="min-w-8 text-center text-lg font-extrabold text-white"
                                style={{ fontVariantNumeric: 'tabular-nums' }}
                            >
                                {totalPrints}
                            </span>
                            <button
                                onClick={increment}
                                disabled={extraPrints >= maxExtraPrints || starting}
                                className="flex h-8 w-8 items-center justify-center rounded-full text-black transition active:scale-90 disabled:opacity-30"
                                style={{ background: YELLOW }}
                                aria-label="Tambah cetakan"
                            >
                                <Plus className="h-3.5 w-3.5" strokeWidth={3} />
                            </button>
                        </div>
                        <span className="text-xs text-white/50">
                            lembar
                        </span>
                        {extraPrints > 0 && (
                            <span className="ml-1 rounded-full bg-yellow-500/20 px-2 py-0.5 text-[11px] font-bold text-yellow-200">
                                +{formatRupiah(extraPrintPrice * extraPrints)}
                            </span>
                        )}
                    </div>
                )}

                {/* CTA */}
                <button
                    onClick={handleStart}
                    disabled={starting}
                    className="group relative overflow-hidden rounded-full px-12 py-5 text-lg font-black text-black shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 disabled:cursor-not-allowed disabled:opacity-70 md:px-16"
                    style={{
                        background: YELLOW,
                        boxShadow: '0 10px 40px rgba(232,201,0,0.5)',
                    }}
                >
                    <span className="relative z-10 flex items-center gap-3">
                        {starting ? (
                            <>Memproses...</>
                        ) : (
                            <>
                                <Camera className="h-5 w-5" strokeWidth={2.5} />
                                Mulai Foto
                                <span className="ml-1 rounded-full bg-black/15 px-3 py-1 text-base font-extrabold">
                                    {formatRupiah(totalPrice)}
                                </span>
                            </>
                        )}
                    </span>
                    <div
                        className="absolute inset-0 translate-x-[-100%] bg-white/20 transition-transform duration-500 group-hover:translate-x-[100%]"
                        style={{ transform: 'skewX(-20deg)' }}
                    />
                </button>

                <p className="text-xs text-white/40">
                    Sentuh layar untuk memulai · Bisa tambah cetakan lebih dari 1 lembar
                </p>
            </div>

            {/* Features bar */}
            <div className="absolute right-0 bottom-0 left-0 z-20 px-6 pb-6">
                <div className="mx-auto flex max-w-2xl justify-center gap-3">
                    {FEATURES.map(({ icon: Icon, label, sub }) => (
                        <div
                            key={label}
                            className="flex flex-1 flex-col items-center gap-1.5 rounded-2xl border border-white/10 bg-white/10 px-3 py-3 backdrop-blur-md"
                        >
                            <div
                                className="flex h-8 w-8 items-center justify-center rounded-full"
                                style={{ background: YELLOW }}
                            >
                                <Icon className="h-4 w-4 text-black" strokeWidth={2.5} />
                            </div>
                            <p className="text-xs font-bold text-white">{label}</p>
                            <p className="text-[10px] text-white/50">{sub}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
