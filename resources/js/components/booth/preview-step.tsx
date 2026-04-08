import { Loader2, Mail, Printer } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface SlotPosition {
    x: number;
    y: number;
    width: number;
    height: number;
}

interface Template {
    id: number;
    name: string;
    frame_path: string | null;
    photo_slots: number;
    slot_positions: SlotPosition[] | null;
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

function generateDefaultSlots(count: number, width: number, height: number): SlotPosition[] {
    const padding = 30;
    const gap = 12;
    const cols = count <= 2 ? count : Math.ceil(Math.sqrt(count));
    const rows = Math.ceil(count / cols);
    const slotW = (width - padding * 2 - gap * (cols - 1)) / cols;
    const slotH = (height - padding * 2 - gap * (rows - 1)) / rows;

    return Array.from({ length: count }, (_, i) => ({
        x: padding + (i % cols) * (slotW + gap),
        y: padding + Math.floor(i / cols) * (slotH + gap),
        width: slotW,
        height: slotH,
    }));
}

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error(`Failed: ${src}`));
        img.src = src;
    });
}

async function composeCanvas(photos: CapturedPhoto[], template: Template | null): Promise<string> {
    const W = 1200;
    const H = 800;
    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;
    const ctx = canvas.getContext('2d')!;

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);

    const slots = template?.slot_positions ?? generateDefaultSlots(photos.length, W, H);
    const sorted = [...photos].sort((a, b) => a.order - b.order);

    await Promise.all(
        sorted.map(async (photo, i) => {
            if (i >= slots.length) return;
            const slot = slots[i];
            try {
                const img = await loadImage(photo.url);
                ctx.save();
                ctx.beginPath();
                ctx.rect(slot.x, slot.y, slot.width, slot.height);
                ctx.clip();
                const scale = Math.max(slot.width / img.width, slot.height / img.height);
                const sw = img.width * scale;
                const sh = img.height * scale;
                ctx.drawImage(img, slot.x + (slot.width - sw) / 2, slot.y + (slot.height - sh) / 2, sw, sh);
                ctx.restore();
            } catch {
                ctx.fillStyle = '#f0f0f0';
                ctx.fillRect(slot.x, slot.y, slot.width, slot.height);
            }
        }),
    );

    if (template?.frame_path) {
        try {
            const frameImg = await loadImage(template.frame_path);
            ctx.drawImage(frameImg, 0, 0, W, H);
        } catch { /* skip */ }
    }

    return canvas.toDataURL('image/jpeg', 0.92);
}

export default function PreviewStep({ photos, template, onPrint, loading }: Props) {
    const [finalImage, setFinalImage] = useState<string | null>(null);
    const [composing, setComposing] = useState(true);
    const [email, setEmail] = useState('');
    const [showEmail, setShowEmail] = useState(false);
    const composedRef = useRef(false);

    useEffect(() => {
        if (composedRef.current) return;
        composedRef.current = true;

        composeCanvas(photos, template)
            .then(setFinalImage)
            .finally(() => setComposing(false));
    }, [photos, template]);

    return (
        <div className="flex h-full flex-col px-8 py-6">
            <div className="mb-4">
                <h2 className="text-3xl font-bold text-white">Preview Hasil</h2>
                <p className="text-zinc-500">Lihat hasil foto Anda sebelum dicetak</p>
            </div>

            {/* Preview */}
            <div className="flex flex-1 items-center justify-center">
                {composing ? (
                    <div className="flex flex-col items-center gap-4 text-zinc-500">
                        <Loader2 className="h-12 w-12 animate-spin" style={{ color: '#F5FA0C' }} />
                        <p className="text-lg">Menyusun foto...</p>
                    </div>
                ) : finalImage ? (
                    <img
                        src={finalImage}
                        alt="Preview"
                        className="max-h-full max-w-full rounded-2xl object-contain shadow-2xl"
                    />
                ) : (
                    <p className="text-zinc-600">Gagal membuat preview</p>
                )}
            </div>

            {/* Email input */}
            {showEmail && (
                <div className="mt-4">
                    <div className="flex gap-2 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <Mail className="mt-0.5 h-5 w-5 shrink-0 text-zinc-500" />
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="Masukkan email untuk menerima foto..."
                            className="flex-1 bg-transparent text-base text-white placeholder-zinc-700 outline-none"
                            autoFocus
                        />
                    </div>
                </div>
            )}

            {/* Actions */}
            <div className="mt-4 flex gap-3">
                <button
                    onClick={() => setShowEmail(!showEmail)}
                    className="flex items-center gap-2 rounded-2xl border border-white/10 px-5 py-4 text-base font-medium text-zinc-400 transition hover:bg-white/5 active:scale-95"
                >
                    <Mail className="h-5 w-5" />
                    {showEmail ? 'Tutup' : 'Kirim Email'}
                </button>

                <button
                    onClick={() => finalImage && onPrint(finalImage, showEmail && email ? email : undefined)}
                    disabled={composing || !finalImage || loading}
                    className="flex flex-1 items-center justify-center gap-2 rounded-2xl py-4 text-xl font-bold text-black shadow-lg transition disabled:cursor-not-allowed disabled:opacity-30 active:scale-95"
                    style={{ background: '#F5FA0C', boxShadow: '0 0 30px rgba(245,250,12,0.20)' }}
                >
                    {loading
                        ? <><Loader2 className="h-5 w-5 animate-spin text-black" /> Memproses...</>
                        : <><Printer className="h-6 w-6" /> Cetak Foto</>
                    }
                </button>
            </div>
        </div>
    );
}
