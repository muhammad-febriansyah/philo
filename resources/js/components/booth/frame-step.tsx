import { CheckCircle, ImageOff, Loader2 } from 'lucide-react';
import { useState } from 'react';

interface Template {
    id: number;
    name: string;
    thumbnail_path: string | null;
    frame_path: string | null;
    photo_slots: number;
    print_size: string;
}

interface Props {
    templates: Template[];
    onSelect: (template: Template) => void;
    onSkip: () => void;
    loading: boolean;
}

export default function FrameStep({ templates, onSelect, onSkip, loading }: Props) {
    const [selected, setSelected] = useState<Template | null>(null);

    if (templates.length === 0) {
        return (
            <div className="flex h-full flex-col items-center justify-center gap-6 px-8">
                <div className="flex h-20 w-20 items-center justify-center rounded-full bg-white/5">
                    <ImageOff className="h-10 w-10 text-zinc-600" />
                </div>
                <div className="text-center">
                    <h2 className="text-2xl font-bold text-white">Tidak Ada Frame</h2>
                    <p className="mt-1 text-zinc-500">Belum ada template frame yang tersedia.</p>
                </div>
                <button
                    onClick={onSkip}
                    className="rounded-2xl px-12 py-4 text-lg font-bold text-black transition active:scale-95"
                    style={{ background: '#F5FA0C' }}
                >
                    Lanjut Tanpa Frame →
                </button>
            </div>
        );
    }

    return (
        <div className="flex h-full flex-col px-10 py-8">
            <div className="mb-6">
                <h2 className="text-3xl font-bold text-white">Pilih Frame</h2>
                <p className="text-zinc-500">Pilih bingkai yang sesuai dengan momen Anda</p>
            </div>

            <div className="grid flex-1 grid-cols-4 gap-4 overflow-auto">
                {templates.map((tmpl) => {
                    const isSelected = selected?.id === tmpl.id;

                    return (
                        <button
                            key={tmpl.id}
                            onClick={() => setSelected(tmpl)}
                            className="group relative flex flex-col overflow-hidden rounded-2xl border-2 transition-all duration-200 active:scale-95"
                            style={
                                isSelected
                                    ? { borderColor: '#F5FA0C', boxShadow: '0 0 20px rgba(245,250,12,0.15)' }
                                    : { borderColor: 'rgba(255,255,255,0.08)' }
                            }
                        >
                            <div className="relative aspect-[3/4] w-full bg-zinc-900">
                                {tmpl.thumbnail_path ? (
                                    <img
                                        src={tmpl.thumbnail_path}
                                        alt={tmpl.name}
                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                ) : (
                                    <div className="flex h-full items-center justify-center">
                                        <ImageOff className="h-10 w-10 text-zinc-700" />
                                    </div>
                                )}

                                {isSelected && (
                                    <div className="absolute inset-0 flex items-center justify-center bg-black/40">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full" style={{ background: '#F5FA0C' }}>
                                            <CheckCircle className="h-7 w-7 text-black" />
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div className="px-3 py-2 text-left" style={isSelected ? { background: 'rgba(245,250,12,0.06)' } : { background: '#111' }}>
                                <p className="truncate text-sm font-semibold text-white">{tmpl.name}</p>
                                <p className="text-xs text-zinc-600">{tmpl.print_size} · {tmpl.photo_slots} slot</p>
                            </div>
                        </button>
                    );
                })}
            </div>

            <div className="mt-6 flex gap-3">
                <button
                    onClick={onSkip}
                    className="rounded-2xl border border-white/10 px-6 py-4 text-base font-medium text-zinc-500 transition hover:bg-white/5 active:scale-95"
                >
                    Tanpa Frame
                </button>
                <button
                    onClick={() => selected && onSelect(selected)}
                    disabled={!selected || loading}
                    className="flex flex-1 items-center justify-center gap-2 rounded-2xl py-4 text-lg font-bold text-black shadow-lg transition disabled:cursor-not-allowed disabled:opacity-30 active:scale-95"
                    style={{ background: '#F5FA0C', boxShadow: selected ? '0 0 25px rgba(245,250,12,0.20)' : undefined }}
                >
                    {loading ? <><Loader2 className="h-5 w-5 animate-spin text-black" /> Memproses...</> : 'Gunakan Frame Ini →'}
                </button>
            </div>
        </div>
    );
}
