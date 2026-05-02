import {
    ArrowLeft,
    Check,
    Image as ImageIcon,
    RotateCcw,
    SlidersHorizontal,
    Smile,
    Sparkles,
    Trash2,
    Type,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

interface Props {
    image: string;
    onBack: () => void;
    onDone: (editedImage: string) => void;
    loading: boolean;
}

type EditorItem = {
    id: string;
    type: 'sticker' | 'text';
    value: string;
    x: number;
    y: number;
    size: number;
    rotation: number;
    color?: string;
};

type FilterPreset = {
    id: string;
    name: string;
    css: string;
};

const YELLOW = '#E8C900';

const FILTERS: FilterPreset[] = [
    { id: 'normal', name: 'Normal', css: 'none' },
    { id: 'beauty', name: 'Beauty', css: 'brightness(1.08) contrast(1.04) saturate(1.12)' },
    { id: 'soft', name: 'Soft', css: 'brightness(1.06) contrast(0.95) saturate(1.08)' },
    { id: 'warm', name: 'Warm', css: 'sepia(0.14) brightness(1.04) saturate(1.14)' },
    { id: 'cool', name: 'Cool', css: 'brightness(1.03) saturate(1.05) hue-rotate(342deg)' },
    { id: 'vintage', name: 'Vintage', css: 'sepia(0.34) contrast(0.96) brightness(1.02)' },
    { id: 'film', name: 'Film', css: 'contrast(1.12) saturate(0.92) brightness(0.98)' },
    { id: 'bw', name: 'B&W', css: 'grayscale(1) contrast(1.08)' },
    { id: 'noir', name: 'Noir', css: 'grayscale(1) contrast(1.28) brightness(0.92)' },
];

const STICKERS = ['✨', '💖', '⭐', '🌈', '🎉', '📸', '😎', '🥰', '👑', '🔥', '🌸', '🫶'];
const TEXT_COLORS = ['#111111', '#ffffff', '#E8C900', '#ef4444', '#22c55e', '#38bdf8'];

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Gagal memuat foto'));
        image.src = src;
    });
}

function nextId() {
    return `item-${Date.now()}-${Math.random().toString(36).slice(2)}`;
}

function drawItem(ctx: CanvasRenderingContext2D, item: EditorItem, selected: boolean) {
    ctx.save();
    ctx.translate(item.x, item.y);
    ctx.rotate((item.rotation * Math.PI) / 180);
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    if (item.type === 'text') {
        ctx.font = `900 ${item.size}px Poppins, Arial, sans-serif`;
        ctx.lineWidth = Math.max(4, item.size * 0.1);
        ctx.strokeStyle = item.color === '#ffffff' ? 'rgba(0,0,0,0.45)' : 'rgba(255,255,255,0.92)';
        ctx.fillStyle = item.color ?? '#ffffff';
        ctx.strokeText(item.value, 0, 0);
        ctx.fillText(item.value, 0, 0);
    } else {
        ctx.font = `${item.size}px Apple Color Emoji, Segoe UI Emoji, Noto Color Emoji, sans-serif`;
        ctx.fillText(item.value, 0, 0);
    }

    if (selected) {
        const width = item.type === 'text'
            ? Math.max(item.size * 2.2, item.value.length * item.size * 0.62)
            : item.size * 1.2;
        const height = item.size * 1.25;

        ctx.strokeStyle = YELLOW;
        ctx.lineWidth = Math.max(3, item.size * 0.04);
        ctx.setLineDash([10, 7]);
        ctx.strokeRect(-width / 2, -height / 2, width, height);
        ctx.setLineDash([]);
    }

    ctx.restore();
}

function hitTest(item: EditorItem, x: number, y: number) {
    const width = item.type === 'text'
        ? Math.max(item.size * 2.2, item.value.length * item.size * 0.62)
        : item.size * 1.35;
    const height = item.size * 1.35;

    return Math.abs(x - item.x) <= width / 2 && Math.abs(y - item.y) <= height / 2;
}

export default function PhotoEditorStep({ image, onBack, onDone, loading }: Props) {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const imageRef = useRef<HTMLImageElement | null>(null);
    const dragRef = useRef<{ id: string; dx: number; dy: number } | null>(null);
    const [items, setItems] = useState<EditorItem[]>([]);
    const [selectedId, setSelectedId] = useState<string | null>(null);
    const [activeFilter, setActiveFilter] = useState<FilterPreset>(FILTERS[0]);
    const [textValue, setTextValue] = useState('PHILO');
    const [textColor, setTextColor] = useState('#ffffff');
    const [ready, setReady] = useState(false);

    const selectedItem = useMemo(
        () => items.find((item) => item.id === selectedId) ?? null,
        [items, selectedId],
    );

    const renderCanvas = useCallback((withSelection = true) => {
        const canvas = canvasRef.current;
        const baseImage = imageRef.current;

        if (!canvas || !baseImage) {
            return;
        }

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.filter = activeFilter.css;
        ctx.drawImage(baseImage, 0, 0, canvas.width, canvas.height);
        ctx.filter = 'none';

        items.forEach((item) => drawItem(ctx, item, withSelection && item.id === selectedId));
    }, [activeFilter.css, items, selectedId]);

    useEffect(() => {
        let cancelled = false;

        setReady(false);
        loadImage(image).then((loadedImage) => {
            if (cancelled) {
                return;
            }

            const canvas = canvasRef.current;

            if (!canvas) {
                return;
            }

            imageRef.current = loadedImage;
            canvas.width = loadedImage.naturalWidth || loadedImage.width;
            canvas.height = loadedImage.naturalHeight || loadedImage.height;
            setReady(true);
        });

        return () => {
            cancelled = true;
        };
    }, [image]);

    useEffect(() => {
        renderCanvas();
    }, [renderCanvas, ready]);

    const canvasPoint = (event: React.PointerEvent<HTMLCanvasElement>) => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return { x: 0, y: 0 };
        }

        const rect = canvas.getBoundingClientRect();

        return {
            x: ((event.clientX - rect.left) / rect.width) * canvas.width,
            y: ((event.clientY - rect.top) / rect.height) * canvas.height,
        };
    };

    const addSticker = (value: string) => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        const item: EditorItem = {
            id: nextId(),
            type: 'sticker',
            value,
            x: canvas.width / 2,
            y: canvas.height / 2,
            size: Math.max(72, canvas.width * 0.09),
            rotation: 0,
        };

        setItems((current) => [...current, item]);
        setSelectedId(item.id);
    };

    const addText = () => {
        const canvas = canvasRef.current;
        const value = textValue.trim();

        if (!canvas || !value) {
            return;
        }

        const item: EditorItem = {
            id: nextId(),
            type: 'text',
            value,
            color: textColor,
            x: canvas.width / 2,
            y: canvas.height * 0.18,
            size: Math.max(54, canvas.width * 0.07),
            rotation: 0,
        };

        setItems((current) => [...current, item]);
        setSelectedId(item.id);
    };

    const updateSelected = (patch: Partial<EditorItem>) => {
        if (!selectedId) {
            return;
        }

        setItems((current) =>
            current.map((item) => (item.id === selectedId ? { ...item, ...patch } : item)),
        );
    };

    const deleteSelected = () => {
        if (!selectedId) {
            return;
        }

        setItems((current) => current.filter((item) => item.id !== selectedId));
        setSelectedId(null);
    };

    const handlePointerDown = (event: React.PointerEvent<HTMLCanvasElement>) => {
        const point = canvasPoint(event);
        const hit = [...items].reverse().find((item) => hitTest(item, point.x, point.y));

        if (!hit) {
            setSelectedId(null);
            return;
        }

        setSelectedId(hit.id);
        dragRef.current = {
            id: hit.id,
            dx: point.x - hit.x,
            dy: point.y - hit.y,
        };
        event.currentTarget.setPointerCapture(event.pointerId);
    };

    const handlePointerMove = (event: React.PointerEvent<HTMLCanvasElement>) => {
        const drag = dragRef.current;

        if (!drag) {
            return;
        }

        const point = canvasPoint(event);
        setItems((current) =>
            current.map((item) =>
                item.id === drag.id
                    ? { ...item, x: point.x - drag.dx, y: point.y - drag.dy }
                    : item,
            ),
        );
    };

    const handlePointerUp = (event: React.PointerEvent<HTMLCanvasElement>) => {
        dragRef.current = null;

        if (event.currentTarget.hasPointerCapture(event.pointerId)) {
            event.currentTarget.releasePointerCapture(event.pointerId);
        }
    };

    const exportImage = () => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        renderCanvas(false);
        const output = canvas.toDataURL('image/jpeg', 0.95);
        renderCanvas(true);
        onDone(output);
    };

    const resetEditor = () => {
        setItems([]);
        setSelectedId(null);
        setActiveFilter(FILTERS[0]);
    };

    return (
        <div
            className="relative flex min-h-screen flex-col"
            style={{
                background: 'linear-gradient(180deg, #fafaf5 0%, #f7f3e8 100%)',
                fontFamily: "'Poppins', sans-serif",
            }}
        >
            <div
                className="pointer-events-none absolute inset-0 z-0 opacity-[0.035]"
                style={{
                    backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                }}
            />

            <div className="relative z-10 flex min-h-screen flex-col gap-4 p-4 md:p-6">
                <div className="flex items-center justify-between gap-4 rounded-[2rem] border border-white/70 bg-white/78 px-5 py-4 backdrop-blur-xl">
                    <button
                        type="button"
                        onClick={onBack}
                        className="flex h-12 items-center gap-2 rounded-[1.2rem] border border-zinc-200 bg-white px-4 text-sm font-bold text-zinc-700 active:scale-95"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Preview
                    </button>
                    <div className="text-center">
                        <p className="text-[10px] font-semibold tracking-[0.28em] text-zinc-400 uppercase">
                            Edit Hasil
                        </p>
                        <h2 className="text-2xl font-extrabold text-zinc-950 md:text-3xl">
                            Filter, stiker, dan teks
                        </h2>
                    </div>
                    <button
                        type="button"
                        onClick={exportImage}
                        disabled={!ready || loading}
                        className="flex h-12 items-center gap-2 rounded-[1.2rem] px-5 text-sm font-black text-black active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                        style={{ background: YELLOW }}
                    >
                        <Check className="h-4 w-4" />
                        Cetak
                    </button>
                </div>

                <div className="grid flex-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div className="flex min-h-[520px] items-center justify-center rounded-[2.2rem] border border-white/70 bg-white/78 p-4 backdrop-blur-xl">
                        {!ready && (
                            <div className="flex flex-col items-center gap-3 text-zinc-500">
                                <ImageIcon className="h-8 w-8" />
                                <p className="text-sm font-semibold">Menyiapkan editor...</p>
                            </div>
                        )}
                        <canvas
                            ref={canvasRef}
                            onPointerDown={handlePointerDown}
                            onPointerMove={handlePointerMove}
                            onPointerUp={handlePointerUp}
                            onPointerCancel={handlePointerUp}
                            className={`max-h-[calc(100vh-220px)] max-w-full touch-none rounded-[1.5rem] shadow-2xl ring-1 ring-black/10 ${ready ? 'block' : 'hidden'}`}
                        />
                    </div>

                    <div className="flex flex-col gap-3">
                        <div className="rounded-[1.75rem] border border-white/70 bg-white/82 p-4 backdrop-blur-xl">
                            <div className="mb-3 flex items-center gap-2">
                                <SlidersHorizontal className="h-4 w-4 text-zinc-400" />
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">
                                    Filter
                                </p>
                            </div>
                            <div className="grid grid-cols-3 gap-2">
                                {FILTERS.map((filter) => {
                                    const isActive = activeFilter.id === filter.id;

                                    return (
                                        <button
                                            key={filter.id}
                                            type="button"
                                            onClick={() => setActiveFilter(filter)}
                                            className="rounded-[1rem] border px-3 py-2 text-xs font-bold active:scale-95"
                                            style={{
                                                borderColor: isActive ? YELLOW : '#e4e4e7',
                                                background: isActive ? 'rgba(232,201,0,0.14)' : '#ffffff',
                                                color: isActive ? '#665500' : '#52525b',
                                            }}
                                        >
                                            {filter.name}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/70 bg-white/82 p-4 backdrop-blur-xl">
                            <div className="mb-3 flex items-center gap-2">
                                <Smile className="h-4 w-4 text-zinc-400" />
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">
                                    Stiker
                                </p>
                            </div>
                            <div className="grid grid-cols-6 gap-2">
                                {STICKERS.map((sticker) => (
                                    <button
                                        key={sticker}
                                        type="button"
                                        onClick={() => addSticker(sticker)}
                                        className="flex aspect-square items-center justify-center rounded-[1rem] border border-zinc-200 bg-white text-2xl active:scale-95"
                                    >
                                        {sticker}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/70 bg-white/82 p-4 backdrop-blur-xl">
                            <div className="mb-3 flex items-center gap-2">
                                <Type className="h-4 w-4 text-zinc-400" />
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">
                                    Teks
                                </p>
                            </div>
                            <input
                                value={textValue}
                                onChange={(event) => setTextValue(event.target.value)}
                                maxLength={24}
                                className="h-11 w-full rounded-[1rem] border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 outline-none focus:border-yellow-400"
                                placeholder="Tulis teks..."
                            />
                            <div className="mt-3 flex items-center justify-between gap-3">
                                <div className="flex gap-1.5">
                                    {TEXT_COLORS.map((color) => (
                                        <button
                                            key={color}
                                            type="button"
                                            onClick={() => setTextColor(color)}
                                            className="h-7 w-7 rounded-full border-2"
                                            style={{
                                                background: color,
                                                borderColor: textColor === color ? YELLOW : '#e4e4e7',
                                            }}
                                        />
                                    ))}
                                </div>
                                <button
                                    type="button"
                                    onClick={addText}
                                    className="rounded-[1rem] px-4 py-2 text-sm font-black text-black active:scale-95"
                                    style={{ background: YELLOW }}
                                >
                                    Tambah
                                </button>
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/70 bg-white/82 p-4 backdrop-blur-xl">
                            <div className="mb-3 flex items-center gap-2">
                                <Sparkles className="h-4 w-4 text-zinc-400" />
                                <p className="text-[10px] font-semibold tracking-[0.22em] text-zinc-400 uppercase">
                                    Object Aktif
                                </p>
                            </div>

                            {selectedItem ? (
                                <div className="space-y-3">
                                    <label className="block">
                                        <span className="text-xs font-bold text-zinc-500">Ukuran</span>
                                        <input
                                            type="range"
                                            min="28"
                                            max="220"
                                            value={selectedItem.size}
                                            onChange={(event) => updateSelected({ size: Number(event.target.value) })}
                                            className="mt-2 w-full accent-[#E8C900]"
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="text-xs font-bold text-zinc-500">Rotasi</span>
                                        <input
                                            type="range"
                                            min="-45"
                                            max="45"
                                            value={selectedItem.rotation}
                                            onChange={(event) => updateSelected({ rotation: Number(event.target.value) })}
                                            className="mt-2 w-full accent-[#E8C900]"
                                        />
                                    </label>
                                    {selectedItem.type === 'text' && (
                                        <input
                                            value={selectedItem.value}
                                            onChange={(event) => updateSelected({ value: event.target.value })}
                                            className="h-10 w-full rounded-[1rem] border border-zinc-200 bg-white px-3 text-sm font-bold text-zinc-900 outline-none"
                                        />
                                    )}
                                    <button
                                        type="button"
                                        onClick={deleteSelected}
                                        className="flex h-11 w-full items-center justify-center gap-2 rounded-[1rem] bg-red-50 text-sm font-bold text-red-600 active:scale-95"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                        Hapus object
                                    </button>
                                </div>
                            ) : (
                                <p className="rounded-[1rem] bg-zinc-50 px-3 py-3 text-sm leading-relaxed text-zinc-500">
                                    Ketuk stiker atau teks di foto untuk mengatur ukuran, rotasi, atau menghapusnya.
                                </p>
                            )}
                        </div>

                        <button
                            type="button"
                            onClick={resetEditor}
                            className="flex h-12 items-center justify-center gap-2 rounded-[1.2rem] border border-zinc-200 bg-white text-sm font-bold text-zinc-600 active:scale-95"
                        >
                            <RotateCcw className="h-4 w-4" />
                            Reset Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
