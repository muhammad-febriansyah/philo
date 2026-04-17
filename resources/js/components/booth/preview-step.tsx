import { Loader2, Mail, Printer, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { loadImage, resolveTemplateLayout } from '@/components/booth/template-slot-utils';

interface Template {
    id: number;
    name: string;
    frame_path: string | null;
    thumbnail_path: string | null;
    photo_slots: number;
    slot_positions: Array<{ x: number; y: number; width: number; height: number }> | null;
    print_size: string;
}

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

interface Props {
    photos: CapturedPhoto[];
    template: Template | null;
    onPrint: (finalImage: string, email?: string) => void;
    loading: boolean;
}

const YELLOW = '#E8C900';

async function composeCanvas(photos: CapturedPhoto[], template: Template | null): Promise<string> {
    const { width, height, slots, frameAsset } = await resolveTemplateLayout(template, photos.length);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    if (!ctx) {
        throw new Error('Canvas tidak tersedia');
    }

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    const sortedPhotos = [...photos].sort((a, b) => a.order - b.order);

    for (let index = 0; index < sortedPhotos.length; index += 1) {
        const photo = sortedPhotos[index];
        const slot = slots[index];

        if (!slot) {
            continue;
        }

        try {
            const image = await loadImage(photo.url);
            ctx.save();
            ctx.beginPath();
            ctx.rect(slot.x, slot.y, slot.width, slot.height);
            ctx.clip();

            const scale = Math.max(slot.width / image.width, slot.height / image.height);
            const drawWidth = image.width * scale;
            const drawHeight = image.height * scale;

            ctx.drawImage(
                image,
                slot.x + (slot.width - drawWidth) / 2,
                slot.y + (slot.height - drawHeight) / 2,
                drawWidth,
                drawHeight,
            );
            ctx.restore();
        } catch {
            ctx.fillStyle = '#e5e7eb';
            ctx.fillRect(slot.x, slot.y, slot.width, slot.height);
        }
    }

    if (frameAsset) {
        ctx.drawImage(frameAsset, 0, 0, width, height);
    }

    return canvas.toDataURL('image/jpeg', 0.95);
}

export default function PreviewStep({ photos, template, onPrint, loading }: Props) {
    const [finalImage, setFinalImage] = useState<string | null>(null);
    const [composing, setComposing] = useState(true);
    const [email, setEmail] = useState('');
    const [showEmail, setShowEmail] = useState(false);
    const composedRef = useRef(false);

    useEffect(() => {
        if (composedRef.current) {
            return;
        }

        composedRef.current = true;

        composeCanvas(photos, template)
            .then(setFinalImage)
            .finally(() => setComposing(false));
    }, [photos, template]);

    const handlePrint = () => {
        if (!finalImage) {
            return;
        }

        onPrint(finalImage, showEmail && email.trim() ? email.trim() : undefined);
    };

    return (
        <div className="relative flex h-full flex-col" style={{ background: '#FAFAF5' }}>
            <div
                className="pointer-events-none absolute inset-0 z-0 opacity-[0.04]"
                style={{
                    backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                }}
            />

            <div className="relative z-10 flex items-start justify-between px-8 pt-6 pb-3">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-400">Preview Hasil</p>
                    <h2 className="mt-1 text-4xl font-bold tracking-tight text-zinc-900">Cek sebelum dicetak</h2>
                </div>

                {template && (
                    <div className="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-white px-3 py-2 shadow-sm">
                        {template.thumbnail_path && (
                            <img src={template.thumbnail_path} alt={template.name} className="h-10 w-8 rounded-lg object-cover" />
                        )}
                        <div>
                            <p className="text-sm font-semibold text-zinc-900">{template.name}</p>
                            <p className="text-xs text-zinc-500">{template.print_size.toUpperCase()}</p>
                        </div>
                    </div>
                )}
            </div>

            <div className="relative z-10 flex flex-1 items-center justify-center px-6 py-4">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(232,201,0,0.10),_transparent_62%)]" />

                {composing ? (
                    <div className="relative flex flex-col items-center gap-5">
                        <div className="relative flex items-center justify-center">
                            <div className="absolute h-24 w-24 animate-ping rounded-full opacity-20" style={{ background: YELLOW }} />
                            <div className="absolute h-16 w-16 animate-pulse rounded-full opacity-30" style={{ background: YELLOW }} />
                            <div className="relative flex h-12 w-12 items-center justify-center rounded-full" style={{ background: YELLOW }}>
                                <Loader2 className="h-6 w-6 animate-spin text-black" />
                            </div>
                        </div>
                        <div className="text-center">
                            <p className="text-lg font-bold text-zinc-900">Menyusun foto...</p>
                            <p className="text-sm text-zinc-500">Template akan dicocokkan otomatis dengan frame</p>
                        </div>
                    </div>
                ) : finalImage ? (
                    <div className="relative">
                        <div
                            className="absolute inset-0 rounded-[1.75rem]"
                            style={{
                                boxShadow: '0 30px 90px rgba(24,24,27,0.18), 0 10px 24px rgba(24,24,27,0.10)',
                            }}
                        />
                        <img
                            src={finalImage}
                            alt="Preview hasil foto"
                            className="relative block max-h-[calc(100vh-290px)] max-w-full rounded-[1.75rem] object-contain"
                        />
                    </div>
                ) : (
                    <div className="flex flex-col items-center gap-3 text-zinc-400">
                        <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100">
                            <Printer className="h-8 w-8 text-zinc-300" />
                        </div>
                        <p className="font-medium">Gagal membuat preview</p>
                    </div>
                )}
            </div>

            <div className="relative z-10 mx-4 mb-4 rounded-[2rem] border border-zinc-200 bg-white/90 px-5 py-4 shadow-sm backdrop-blur-sm">
                {showEmail && (
                    <div className="mb-3 flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <Mail className="h-5 w-5 shrink-0 text-zinc-400" />
                        <input
                            type="email"
                            value={email}
                            onChange={(event) => setEmail(event.target.value)}
                            placeholder="Email untuk menerima foto digital..."
                            className="flex-1 bg-transparent text-base text-zinc-900 placeholder-zinc-400 outline-none"
                            autoFocus
                        />
                        <button
                            type="button"
                            onClick={() => {
                                setShowEmail(false);
                                setEmail('');
                            }}
                            className="rounded-full p-1 text-zinc-400 transition hover:text-zinc-700"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    </div>
                )}

                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={() => setShowEmail(!showEmail)}
                        className={`flex h-14 items-center gap-2 rounded-2xl border px-5 text-sm font-semibold transition-all duration-200 ${
                            showEmail
                                ? 'border-yellow-400/50 bg-yellow-50 text-yellow-700'
                                : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-800'
                        }`}
                    >
                        <Mail className="h-4 w-4" />
                        {showEmail ? 'Batal' : 'Email'}
                    </button>

                    <button
                        type="button"
                        onClick={handlePrint}
                        disabled={composing || !finalImage || loading}
                        className="flex h-14 flex-1 items-center justify-center gap-3 rounded-2xl text-xl font-black text-black transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                        style={{
                            background: YELLOW,
                            boxShadow: composing || !finalImage || loading ? 'none' : '0 8px 24px rgba(232,201,0,0.28)',
                        }}
                    >
                        {loading ? (
                            <>
                                <Loader2 className="h-5 w-5 animate-spin" />
                                <span>Memproses...</span>
                            </>
                        ) : composing ? (
                            <>
                                <Loader2 className="h-5 w-5 animate-spin" />
                                <span>Menyiapkan...</span>
                            </>
                        ) : (
                            <>
                                <Printer className="h-6 w-6" />
                                <span>Cetak &amp; Selesai</span>
                            </>
                        )}
                    </button>
                </div>

                <p className="mt-3 text-center text-sm text-zinc-500">
                    {finalImage && !composing
                        ? `${photos.length} foto · ${template?.name ?? 'Tanpa frame'} · ${template?.print_size?.toUpperCase() ?? ''}`
                        : 'Harap tunggu preview selesai disiapkan'}
                </p>
            </div>
        </div>
    );
}
