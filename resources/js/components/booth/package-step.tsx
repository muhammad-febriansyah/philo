import {
    ArrowLeft,
    Camera,
    CheckCircle2,
    ChevronRight,
    Image as ImageIcon,
    Layers,
    Star,
    Zap,
} from 'lucide-react';
import { useMemo, useState } from 'react';

interface Package {
    id: number;
    name: string;
    description: string | null;
    photo_count: number;
    print_size: string;
    price: number;
    template_ids: number[];
}

interface Template {
    id: number;
    name: string;
    print_size: string;
    thumbnail_path: string | null;
    frame_path: string | null;
}

interface Props {
    packages: Package[];
    templates: Template[];
    paymentProvider: 'doku' | 'duitku';
    duitkuPaymentMethod: string;
    onDuitkuPaymentMethodChange: (value: string) => void;
    onSelect: (pkg: Package) => void;
    onBack: () => void;
    loading: boolean;
}

const YELLOW = '#E8C900';

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
}

function PackageSilhouette({ count }: { count: number }) {
    if (count <= 2) {
        return (
            <div className="flex h-full gap-2">
                {[0, 1].map((i) => (
                    <div key={i} className="flex-1 rounded-xl bg-zinc-200/70" />
                ))}
            </div>
        );
    }
    if (count <= 4) {
        return (
            <div className="grid h-full grid-cols-2 gap-2">
                {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="rounded-xl bg-zinc-200/70" />
                ))}
            </div>
        );
    }
    return (
        <div className="grid h-full grid-cols-3 gap-1.5">
            {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="rounded-lg bg-zinc-200/70" />
            ))}
        </div>
    );
}

export default function PackageStep({
    packages,
    templates,
    paymentProvider,
    duitkuPaymentMethod,
    onDuitkuPaymentMethodChange,
    onSelect,
    onBack,
    loading,
}: Props) {
    const [selected, setSelected] = useState<Package | null>(
        packages[0] ?? null,
    );
    const [activePreviewId, setActivePreviewId] = useState<number | null>(null);

    const selectedTemplates = useMemo(() => {
        if (!selected) return [];
        const assigned = templates.filter((t) =>
            selected.template_ids.includes(t.id),
        );
        if (assigned.length > 0) return assigned;
        return templates.filter((t) => t.print_size === selected.print_size);
    }, [selected, templates]);

    const activePreview = useMemo(
        () =>
            selectedTemplates.find((t) => t.id === activePreviewId) ??
            selectedTemplates[0] ??
            null,
        [activePreviewId, selectedTemplates],
    );

    const handlePackageClick = (pkg: Package) => {
        setSelected(pkg);
        setActivePreviewId(null);
    };

    return (
        <div className="flex min-h-full flex-col px-6 py-8 md:px-10">
            {/* ── Header ── */}
            <div className="mb-8 flex items-center justify-between gap-6">
                <div className="flex items-center gap-4">
                    <button
                        onClick={onBack}
                        className="flex h-11 w-11 items-center justify-center rounded-full bg-white/70 text-zinc-700 transition hover:bg-white active:scale-95"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </button>
                    <div>
                        <div className="mb-1.5 inline-flex items-center gap-1.5 rounded-full bg-zinc-900 px-3 py-1 text-[11px] font-bold tracking-[0.2em] text-white uppercase">
                            <span className="h-1.5 w-1.5 rounded-full bg-yellow-400" />
                            Step 1
                        </div>
                        <h2
                            className="text-4xl font-extrabold tracking-tight text-zinc-950"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                        >
                            Pilih Paket Foto
                        </h2>
                        <p className="mt-0.5 text-sm text-zinc-500">
                            Pilih paket, lalu lihat preview template di panel kanan.
                        </p>
                    </div>
                </div>

                <div className="hidden items-center gap-2 xl:flex">
                    {['Paket', 'Payment', 'Frame', 'Foto', 'Preview', 'Cetak'].map(
                        (step, i, arr) => (
                            <div key={step} className="flex items-center gap-2">
                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-semibold ${
                                        i === 0
                                            ? 'bg-zinc-900 text-white'
                                            : 'bg-white/60 text-zinc-500'
                                    }`}
                                >
                                    {step}
                                </span>
                                {i < arr.length - 1 && (
                                    <span className="text-zinc-300">→</span>
                                )}
                            </div>
                        ),
                    )}
                </div>
            </div>

            {paymentProvider === 'duitku' && (
                <div className="mb-6 rounded-2xl border border-yellow-200/70 bg-yellow-50/70 p-4">
                    <p className="text-xs font-bold uppercase tracking-widest text-zinc-500">
                        Metode Pembayaran Duitku
                    </p>
                    <div className="mt-2 flex flex-col gap-2 md:flex-row md:items-center">
                        <input
                            type="text"
                            list="duitku-method-options"
                            value={duitkuPaymentMethod}
                            onChange={(event) =>
                                onDuitkuPaymentMethodChange(
                                    event.target.value.toUpperCase(),
                                )
                            }
                            className="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 outline-none ring-0 transition focus:border-yellow-400"
                            placeholder="Kode metode, contoh: GQ"
                        />
                        <span className="text-xs text-zinc-500">
                            Contoh: `GQ` (QRIS), `BR` (BRI VA), `I1` (Indomaret).
                        </span>
                    </div>
                    <datalist id="duitku-method-options">
                        <option value="GQ" />
                        <option value="BC" />
                        <option value="M2" />
                        <option value="VA" />
                        <option value="BR" />
                        <option value="I1" />
                    </datalist>
                </div>
            )}

            {/* ── Main grid ── */}
            <div className="grid flex-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_400px]">
                {/* ── Package cards ── */}
                <div className="grid auto-rows-fr grid-cols-1 gap-4 md:grid-cols-2">
                    {packages.map((pkg, index) => {
                        const isSelected = selected?.id === pkg.id;
                        const isPopular = index === 1;

                        return (
                            <button
                                key={pkg.id}
                                onClick={() => handlePackageClick(pkg)}
                                className="group relative flex flex-col overflow-hidden rounded-[1.75rem] p-6 text-left transition-all duration-200 active:scale-[0.99]"
                                style={
                                    isSelected
                                        ? {
                                              background:
                                                  'linear-gradient(145deg, rgba(232,201,0,0.12) 0%, rgba(255,255,255,0.97) 60%)',
                                              outline: `2.5px solid ${YELLOW}`,
                                              outlineOffset: '-1px',
                                          }
                                        : {
                                              background: 'rgba(255,255,255,0.72)',
                                              outline: '2.5px solid transparent',
                                              outlineOffset: '-1px',
                                          }
                                }
                            >
                                {/* Popular badge */}
                                {isPopular && (
                                    <div
                                        className="absolute top-0 right-0 rounded-bl-2xl rounded-tr-[1.75rem] px-4 py-1.5"
                                        style={{ background: YELLOW }}
                                    >
                                        <span className="flex items-center gap-1 text-xs font-bold text-black">
                                            <Star className="h-3 w-3 fill-black" />
                                            Favorit
                                        </span>
                                    </div>
                                )}

                                {/* Selected indicator */}
                                {isSelected && (
                                    <div className="absolute top-4 right-4 flex h-7 w-7 items-center justify-center rounded-full bg-zinc-900">
                                        <CheckCircle2 className="h-4 w-4 text-white" />
                                    </div>
                                )}

                                {/* Icon + title */}
                                <div className="flex items-start gap-3">
                                    <div
                                        className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition-colors"
                                        style={{
                                            background: isSelected
                                                ? YELLOW
                                                : '#F0F0F0',
                                        }}
                                    >
                                        <Camera
                                            className={`h-6 w-6 ${isSelected ? 'text-black' : 'text-zinc-400'}`}
                                        />
                                    </div>
                                    <div className="min-w-0 pt-0.5">
                                        <h3 className="text-xl font-bold leading-tight text-zinc-900">
                                            {pkg.name}
                                        </h3>
                                        <p className="mt-1 line-clamp-2 text-sm leading-relaxed text-zinc-500">
                                            {pkg.description ||
                                                'Paket praktis untuk sesi photobooth yang cepat dan rapi.'}
                                        </p>
                                    </div>
                                </div>

                                {/* Tags */}
                                <div className="mt-4 flex flex-wrap gap-2">
                                    <span className="flex items-center gap-1.5 rounded-full bg-zinc-100/80 px-3 py-1 text-xs font-semibold text-zinc-600">
                                        <Camera className="h-3 w-3" />
                                        {pkg.photo_count} foto
                                    </span>
                                    <span className="flex items-center gap-1.5 rounded-full bg-zinc-100/80 px-3 py-1 text-xs font-semibold text-zinc-600">
                                        <Layers className="h-3 w-3" />
                                        Cetak {pkg.print_size}
                                    </span>
                                </div>

                                {/* Price */}
                                <div className="mt-auto flex items-end justify-between pt-5">
                                    <div
                                        className="flex h-8 items-center gap-1.5 rounded-xl px-3 text-xs font-bold"
                                        style={{
                                            background: isSelected
                                                ? 'rgba(232,201,0,0.18)'
                                                : 'rgba(0,0,0,0.05)',
                                            color: isSelected
                                                ? '#6b5900'
                                                : '#71717a',
                                        }}
                                    >
                                        <Zap className="h-3.5 w-3.5" />
                                        {pkg.photo_count} pose
                                    </div>
                                    <div className="text-right">
                                        <p className="text-[11px] font-semibold tracking-wider text-zinc-400 uppercase">
                                            Harga
                                        </p>
                                        <p
                                            className="text-3xl font-black leading-none text-zinc-900"
                                            style={{
                                                fontFamily: "'Poppins', sans-serif",
                                            }}
                                        >
                                            {formatPrice(pkg.price)}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        );
                    })}
                </div>

                {/* ── Right panel ── */}
                <div className="hidden xl:block">
                    <div className="sticky top-6 flex max-h-[calc(100vh-3rem)] flex-col gap-4 overflow-y-auto rounded-[2rem] bg-white/75 p-5 backdrop-blur-md">
                        {/* Package info */}
                        <div>
                            <p className="text-[11px] font-bold tracking-[0.22em] text-zinc-400 uppercase">
                                Paket Dipilih
                            </p>
                            <div className="mt-2 flex items-start justify-between gap-3">
                                <h3
                                    className="text-2xl font-extrabold text-zinc-900"
                                    style={{ fontFamily: "'Poppins', sans-serif" }}
                                >
                                    {selected?.name ?? '—'}
                                </h3>
                                {selected && (
                                    <span
                                        className="mt-1 shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                                        style={{
                                            background: 'rgba(232,201,0,0.18)',
                                            color: '#7a6200',
                                        }}
                                    >
                                        {selectedTemplates.length} template
                                    </span>
                                )}
                            </div>
                            <p className="mt-1 line-clamp-2 text-sm text-zinc-500">
                                {selected?.description ||
                                    'Pilih paket untuk melihat preview template.'}
                            </p>
                        </div>

                        {/* Template preview */}
                        <div className="rounded-[1.5rem] bg-[linear-gradient(160deg,rgba(232,201,0,0.12),rgba(255,255,255,0.94))] p-4">
                            <div className="mb-3 flex items-center justify-between">
                                <div className="flex items-center gap-2 text-sm font-semibold text-zinc-700">
                                    <ImageIcon className="h-4 w-4" />
                                    Preview Template
                                </div>
                                <span className="rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-semibold text-zinc-500">
                                    {activePreview?.print_size ??
                                        selected?.print_size ??
                                        '—'}
                                </span>
                            </div>

                            <div className="flex h-[260px] items-center justify-center overflow-hidden rounded-[1.2rem] bg-white/60">
                                {activePreview &&
                                (activePreview.thumbnail_path ||
                                    activePreview.frame_path) ? (
                                    <img
                                        src={
                                            activePreview.thumbnail_path ??
                                            activePreview.frame_path ??
                                            ''
                                        }
                                        alt={activePreview.name}
                                        className="h-full w-full object-contain"
                                    />
                                ) : selected ? (
                                    <div className="h-full w-full p-5">
                                        <PackageSilhouette
                                            count={selected.photo_count}
                                        />
                                    </div>
                                ) : null}
                            </div>

                            {activePreview && (
                                <div className="mt-3">
                                    <p className="truncate font-bold text-zinc-900">
                                        {activePreview.name}
                                    </p>
                                    <p className="text-xs text-zinc-500">
                                        Template {activePreview.print_size} ·{' '}
                                        {selected?.name}
                                    </p>
                                </div>
                            )}
                        </div>

                        {/* Template thumbnails */}
                        {selectedTemplates.length > 0 && (
                            <div>
                                <div className="mb-3 flex items-center justify-between">
                                    <p className="text-[11px] font-bold tracking-[0.18em] text-zinc-400 uppercase">
                                        Pilihan Template
                                    </p>
                                    <span
                                        className="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                        style={{
                                            background: 'rgba(232,201,0,0.18)',
                                            color: '#7a6200',
                                        }}
                                    >
                                        {selectedTemplates.length} tersedia
                                    </span>
                                </div>
                                <div className="grid grid-cols-2 gap-2.5">
                                    {selectedTemplates.map((template) => {
                                        const isActive =
                                            activePreview?.id === template.id;
                                        return (
                                            <button
                                                key={template.id}
                                                onClick={() =>
                                                    setActivePreviewId(
                                                        template.id,
                                                    )
                                                }
                                                className="group relative flex flex-col overflow-hidden rounded-[1.2rem] bg-white/60 transition-all duration-150 active:scale-[0.97]"
                                                style={
                                                    isActive
                                                        ? {
                                                              outline: `2.5px solid ${YELLOW}`,
                                                              background:
                                                                  'rgba(232,201,0,0.08)',
                                                          }
                                                        : {}
                                                }
                                            >
                                                {/* Active checkmark */}
                                                {isActive && (
                                                    <div
                                                        className="absolute top-2 right-2 z-10 flex h-6 w-6 items-center justify-center rounded-full"
                                                        style={{
                                                            background: YELLOW,
                                                        }}
                                                    >
                                                        <CheckCircle2 className="h-3.5 w-3.5 text-black" />
                                                    </div>
                                                )}

                                                {/* Thumbnail */}
                                                <div className="aspect-[3/4] w-full overflow-hidden bg-zinc-100/60">
                                                    {template.thumbnail_path ||
                                                    template.frame_path ? (
                                                        <img
                                                            src={
                                                                template.thumbnail_path ??
                                                                template.frame_path ??
                                                                ''
                                                            }
                                                            alt={template.name}
                                                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                        />
                                                    ) : (
                                                        <div className="flex h-full w-full items-center justify-center p-3">
                                                            <PackageSilhouette
                                                                count={
                                                                    selected?.photo_count ??
                                                                    4
                                                                }
                                                            />
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Name */}
                                                <div className="p-2.5">
                                                    <p className="truncate text-xs font-bold text-zinc-900 leading-tight">
                                                        {template.name}
                                                    </p>
                                                    <p className="mt-0.5 text-[10px] font-medium text-zinc-400">
                                                        {template.print_size}
                                                    </p>
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        )}

                        {/* Stats row */}
                        <div className="grid grid-cols-2 gap-3">
                            <div className="rounded-[1.2rem] bg-zinc-950 px-4 py-3 text-white">
                                <p className="text-[10px] font-semibold tracking-widest text-white/50 uppercase">
                                    Jumlah Foto
                                </p>
                                <p className="mt-1 text-2xl font-black">
                                    {selected?.photo_count ?? '—'}
                                </p>
                            </div>
                            <div className="rounded-[1.2rem] bg-zinc-950 px-4 py-3 text-white">
                                <p className="text-[10px] font-semibold tracking-widest text-white/50 uppercase">
                                    Ukuran Cetak
                                </p>
                                <p className="mt-1 text-2xl font-black">
                                    {selected?.print_size ?? '—'}
                                </p>
                            </div>
                        </div>

                        {/* Price + CTA */}
                        <div
                            className="rounded-[1.5rem] p-4"
                            style={{
                                background:
                                    'linear-gradient(160deg, rgba(232,201,0,0.22) 0%, rgba(255,255,255,0.96) 100%)',
                            }}
                        >
                            <p className="text-[11px] font-semibold tracking-widest text-zinc-500 uppercase">
                                Total Pembayaran
                            </p>
                            <p
                                className="mt-1 text-4xl font-black text-zinc-900"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                {selected ? formatPrice(selected.price) : '—'}
                            </p>
                            <button
                                onClick={() => selected && onSelect(selected)}
                                disabled={!selected || loading}
                                className="mt-3 flex w-full items-center justify-center gap-2 rounded-[1.2rem] py-3.5 text-base font-black text-black transition-all active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                                style={{ background: YELLOW }}
                            >
                                {loading ? 'Memproses...' : 'Lanjut ke Pembayaran'}
                                <ChevronRight className="h-5 w-5" strokeWidth={2.5} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Mobile bottom bar ── */}
            <div className="mt-5 xl:hidden">
                <div className="flex items-center justify-between gap-4 rounded-[1.75rem] bg-white/75 px-5 py-4 backdrop-blur-sm">
                    <div className="min-w-0">
                        <p className="text-xs font-semibold tracking-wider text-zinc-400 uppercase">
                            Pilihan Aktif
                        </p>
                        <p className="mt-0.5 truncate text-base font-bold text-zinc-900">
                            {selected
                                ? `${selected.name} · ${formatPrice(selected.price)}`
                                : 'Belum dipilih'}
                        </p>
                    </div>
                    <button
                        onClick={() => selected && onSelect(selected)}
                        disabled={!selected || loading}
                        className="inline-flex shrink-0 items-center gap-2 rounded-2xl px-6 py-3.5 text-base font-black text-black transition-all active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                        style={{ background: YELLOW }}
                    >
                        {loading ? 'Memproses...' : 'Lanjut'}
                        <ChevronRight className="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    );
}
