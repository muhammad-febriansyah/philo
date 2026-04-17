import { ArrowLeft, CheckCircle, ImageOff, Loader2 } from 'lucide-react';
import { useState } from 'react';

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

interface PackageSummary {
    name: string;
    photo_count: number;
    print_size: string;
}

interface Props {
    templates: Template[];
    selectedPackage: PackageSummary | null;
    onSelect: (template: Template) => void;
    onSkip: () => void;
    onBack: () => void;
    loading: boolean;
}

export default function FrameStep({
    templates,
    selectedPackage,
    onSelect,
    onSkip,
    onBack,
    loading,
}: Props) {
    const [selected, setSelected] = useState<Template | null>(null);

    if (templates.length === 0) {
        return (
            <div className="flex h-full flex-col items-center justify-center gap-6 px-8">
                <div className="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-100">
                    <ImageOff className="h-10 w-10 text-zinc-400" />
                </div>
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-zinc-900">
                        Tidak Ada Frame
                    </h2>
                    <p className="mt-1 text-zinc-500">
                        Lanjut tanpa frame untuk sesi ini.
                    </p>
                </div>
                <div className="flex gap-3">
                    <button
                        onClick={onBack}
                        className="rounded-2xl border border-zinc-200 bg-white px-6 py-4 text-base font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50"
                    >
                        Kembali
                    </button>
                    <button
                        onClick={onSkip}
                        className="rounded-2xl px-12 py-4 text-lg font-bold text-black shadow-lg transition active:scale-95"
                        style={{ background: '#E8C900' }}
                    >
                        Lanjut ke Kamera
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="flex h-full flex-col px-10 py-8">
            <div className="mb-6 flex items-start justify-between gap-4">
                <div className="flex items-start gap-4">
                    <button
                        onClick={onBack}
                        className="flex h-11 w-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-50 active:scale-95"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </button>
                    <div>
                        <h2 className="text-3xl font-bold text-zinc-900">
                            Pilih Frame
                        </h2>
                        <p className="text-zinc-500">
                            Frame akan tampil langsung sebagai overlay saat
                            kamera aktif
                        </p>
                    </div>
                </div>

                {selectedPackage && (
                    <div className="rounded-2xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
                        <p className="text-xs text-zinc-400">Paket aktif</p>
                        <p className="text-sm font-semibold text-zinc-900">
                            {selectedPackage.name}
                        </p>
                        <p className="text-xs text-zinc-500">
                            {selectedPackage.photo_count} foto ·{' '}
                            {selectedPackage.print_size}
                        </p>
                    </div>
                )}
            </div>

            <div className="grid flex-1 grid-cols-4 gap-4 overflow-auto">
                {templates.map((template) => {
                    const isSelected = selected?.id === template.id;

                    return (
                        <button
                            key={template.id}
                            onClick={() => setSelected(template)}
                            className="group relative flex flex-col overflow-hidden rounded-2xl border-2 bg-white shadow-sm transition-all duration-200 active:scale-95"
                            style={
                                isSelected
                                    ? {
                                          borderColor: '#E8C900',
                                          boxShadow:
                                              '0 4px 20px rgba(232,201,0,0.20)',
                                      }
                                    : {
                                          borderColor: '#E5E5E5',
                                          boxShadow:
                                              '0 1px 4px rgba(0,0,0,0.06)',
                                      }
                            }
                        >
                            <div className="relative aspect-[3/4] w-full bg-zinc-100">
                                {template.thumbnail_path ? (
                                    <img
                                        src={template.thumbnail_path}
                                        alt={template.name}
                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                ) : (
                                    <div className="flex h-full items-center justify-center">
                                        <ImageOff className="h-10 w-10 text-zinc-300" />
                                    </div>
                                )}

                                {isSelected && (
                                    <div className="absolute inset-0 flex items-center justify-center bg-black/20">
                                        <div
                                            className="flex h-12 w-12 items-center justify-center rounded-full"
                                            style={{ background: '#E8C900' }}
                                        >
                                            <CheckCircle className="h-7 w-7 text-black" />
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div
                                className="px-3 py-2 text-left"
                                style={
                                    isSelected
                                        ? { background: 'rgba(232,201,0,0.06)' }
                                        : { background: '#fff' }
                                }
                            >
                                <p className="truncate text-sm font-semibold text-zinc-900">
                                    {template.name}
                                </p>
                                <p className="text-xs text-zinc-400">
                                    {template.print_size} ·{' '}
                                    {template.photo_slots} slot
                                </p>
                            </div>
                        </button>
                    );
                })}
            </div>

            <div className="mt-6 flex gap-3">
                <button
                    onClick={onSkip}
                    className="rounded-2xl border border-zinc-200 bg-white px-6 py-4 text-base font-medium text-zinc-500 shadow-sm transition hover:bg-zinc-50 active:scale-95"
                >
                    Tanpa Frame
                </button>
                <button
                    onClick={() => selected && onSelect(selected)}
                    disabled={!selected || loading}
                    className="flex flex-1 items-center justify-center gap-2 rounded-2xl py-4 text-lg font-bold text-black shadow-lg transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-30"
                    style={{
                        background: '#E8C900',
                        boxShadow: selected
                            ? '0 4px 20px rgba(232,201,0,0.25)'
                            : undefined,
                    }}
                >
                    {loading ? (
                        <>
                            <Loader2 className="h-5 w-5 animate-spin text-black" />{' '}
                            Menyimpan frame...
                        </>
                    ) : (
                        'Gunakan Frame Ini'
                    )}
                </button>
            </div>
        </div>
    );
}
