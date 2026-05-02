import { CheckCircle2, ChevronRight, ImageOff, Sparkles } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface Template {
    id: number;
    name: string;
    thumbnail_path: string | null;
    frame_path: string | null;
    photo_slots: number;
    slot_positions: Array<{
        x: number;
        y: number;
        width: number;
        height: number;
    }> | null;
    print_size: string;
}

interface Props {
    templates: Template[];
    extraPrints: number;
    onSelect: (template: Template) => void;
    loading: boolean;
}

const YELLOW = '#E8C900';

function PhotoSilhouette({ count }: { count: number }) {
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

export default function FrameStep({
    templates,
    extraPrints,
    onSelect,
    loading,
}: Props) {
    const activeTemplates = useMemo(
        () => templates.filter((t) => (t.thumbnail_path || t.frame_path)),
        [templates],
    );
    const initialId = activeTemplates[0]?.id ?? null;
    const [selectedId, setSelectedId] = useState<number | null>(initialId);

    useEffect(() => {
        if (activeTemplates.length === 0) {
            setSelectedId(null);
            return;
        }
        const stillValid = activeTemplates.some((t) => t.id === selectedId);
        if (!stillValid) {
            setSelectedId(activeTemplates[0].id);
        }
    }, [activeTemplates, selectedId]);

    const selectedTemplate = useMemo(
        () => activeTemplates.find((t) => t.id === selectedId) ?? null,
        [activeTemplates, selectedId],
    );

    const totalPrints = 1 + extraPrints;

    const handleProceed = () => {
        if (selectedTemplate && !loading) {
            onSelect(selectedTemplate);
        }
    };

    return (
        <div className="flex min-h-full flex-col px-6 pt-8 pb-32 md:px-10 xl:pb-10">
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div className="mb-1.5 inline-flex items-center gap-1.5 rounded-full bg-zinc-900 px-3 py-1 text-[11px] font-bold tracking-[0.2em] text-white uppercase">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                        Pembayaran berhasil · Pilih frame
                    </div>
                    <h2 className="text-3xl font-extrabold tracking-tight text-zinc-950 md:text-4xl">
                        Pilih Frame Foto Kamu
                    </h2>
                    <p className="mt-0.5 text-sm text-zinc-500">
                        Setelah memilih frame, langsung lanjut ambil foto. Akan dicetak{' '}
                        <strong>{totalPrints} lembar</strong>.
                    </p>
                </div>
                <div className="hidden items-center gap-2 lg:flex">
                    {['Bayar', 'Frame', 'Foto', 'Selesai'].map((label, i, arr) => (
                        <div key={label} className="flex items-center gap-2">
                            <span
                                className={`rounded-full px-3 py-1 text-xs font-semibold ${
                                    i === 1
                                        ? 'bg-zinc-900 text-white'
                                        : i === 0
                                          ? 'bg-emerald-500/15 text-emerald-700'
                                          : 'bg-white/60 text-zinc-500'
                                }`}
                            >
                                {label}
                            </span>
                            {i < arr.length - 1 && <span className="text-zinc-300">→</span>}
                        </div>
                    ))}
                </div>
            </div>

            <div className="grid flex-1 gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(360px,420px)] xl:gap-8">
                {/* Frame grid */}
                <div>
                    {activeTemplates.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 rounded-[1.4rem] border border-dashed border-zinc-300 bg-white/60 p-10 text-center">
                            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                <ImageOff className="h-6 w-6 text-zinc-400" />
                            </div>
                            <p className="text-sm font-bold text-zinc-700">
                                Belum ada frame aktif
                            </p>
                            <p className="text-xs text-zinc-500">
                                Hubungi admin untuk menambahkan template.
                            </p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            {activeTemplates.map((template) => {
                                const isActive = selectedId === template.id;
                                return (
                                    <button
                                        key={template.id}
                                        onClick={() => setSelectedId(template.id)}
                                        className="group relative flex flex-col overflow-hidden rounded-[1rem] bg-white/70 transition-all duration-150 active:scale-[0.97]"
                                        style={
                                            isActive
                                                ? {
                                                      outline: `3px solid ${YELLOW}`,
                                                      outlineOffset: '-1px',
                                                      background: 'rgba(232,201,0,0.08)',
                                                  }
                                                : {
                                                      outline: '1.5px solid #e4e4e7',
                                                  }
                                        }
                                    >
                                        {isActive && (
                                            <div
                                                className="absolute top-1.5 right-1.5 z-10 flex h-5 w-5 items-center justify-center rounded-full"
                                                style={{ background: YELLOW }}
                                            >
                                                <CheckCircle2 className="h-3 w-3 text-black" />
                                            </div>
                                        )}
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
                                                <div className="flex h-full w-full items-center justify-center p-2">
                                                    <PhotoSilhouette
                                                        count={template.photo_slots}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                        <div className="px-2 py-1.5 text-left">
                                            <p className="truncate text-xs font-bold leading-tight text-zinc-900">
                                                {template.name}
                                            </p>
                                            <p className="text-[10px] text-zinc-500">
                                                {template.print_size.toUpperCase()} ·{' '}
                                                {template.photo_slots} slot
                                            </p>
                                        </div>
                                    </button>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* Right: preview + CTA */}
                <div className="xl:sticky xl:top-6">
                    <div className="flex flex-col gap-4 rounded-[1.6rem] bg-white/80 p-5 backdrop-blur-md">
                        <div>
                            <p className="text-[11px] font-bold tracking-widest text-zinc-400 uppercase">
                                Preview frame
                            </p>
                            <div className="mt-2 rounded-[1.1rem] bg-[linear-gradient(160deg,rgba(232,201,0,0.12),rgba(255,255,255,0.94))] p-3">
                                <div className="mx-auto flex aspect-[3/4] w-full max-w-[220px] items-center justify-center overflow-hidden rounded-[0.9rem] bg-white">
                                    {selectedTemplate &&
                                    (selectedTemplate.thumbnail_path ||
                                        selectedTemplate.frame_path) ? (
                                        <img
                                            src={
                                                selectedTemplate.thumbnail_path ??
                                                selectedTemplate.frame_path ??
                                                ''
                                            }
                                            alt={selectedTemplate.name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : selectedTemplate ? (
                                        <div className="h-full w-full p-3">
                                            <PhotoSilhouette
                                                count={selectedTemplate.photo_slots}
                                            />
                                        </div>
                                    ) : null}
                                </div>
                                {selectedTemplate && (
                                    <div className="mt-3 flex items-center justify-between gap-2 px-1">
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-bold text-zinc-900">
                                                {selectedTemplate.name}
                                            </p>
                                            <p className="text-[11px] text-zinc-500">
                                                {selectedTemplate.print_size.toUpperCase()} ·{' '}
                                                {selectedTemplate.photo_slots} slot foto
                                            </p>
                                        </div>
                                        <span
                                            className="flex shrink-0 items-center gap-1 rounded-full px-2 py-1 text-[10px] font-bold"
                                            style={{
                                                background: 'rgba(232,201,0,0.18)',
                                                color: '#7a6200',
                                            }}
                                        >
                                            <Sparkles className="h-3 w-3" />
                                            Terpilih
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>

                        <button
                            onClick={handleProceed}
                            disabled={!selectedTemplate || loading}
                            className="flex w-full items-center justify-center gap-2 rounded-[1rem] py-3.5 text-base font-black text-black transition-all active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                            style={{ background: YELLOW }}
                        >
                            {loading ? 'Memproses...' : 'Lanjut Ambil Foto'}
                            <ChevronRight className="h-5 w-5" strokeWidth={2.5} />
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile sticky bar */}
            <div className="fixed inset-x-0 bottom-0 z-30 border-t border-white/60 bg-white/85 px-4 py-3 backdrop-blur-md xl:hidden">
                <div className="flex items-center justify-between gap-3">
                    <div className="min-w-0">
                        <p className="text-[10px] font-bold tracking-wider text-zinc-400 uppercase">
                            Frame
                        </p>
                        <p className="truncate text-sm font-black text-zinc-900">
                            {selectedTemplate?.name ?? 'Pilih frame'}
                        </p>
                    </div>
                    <button
                        onClick={handleProceed}
                        disabled={!selectedTemplate || loading}
                        className="inline-flex shrink-0 items-center gap-2 rounded-2xl px-5 py-3 text-sm font-black text-black transition-all active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                        style={{ background: YELLOW }}
                    >
                        {loading ? 'Memproses...' : 'Lanjut'}
                        <ChevronRight className="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    );
}
