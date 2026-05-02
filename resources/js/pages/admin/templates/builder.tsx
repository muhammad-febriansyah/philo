import { Head, usePage } from '@inertiajs/react';
import {
    ArrowDown,
    ArrowLeft,
    ArrowUp,
    ChevronDown,
    Copy,
    Image as ImageIcon,
    Info,
    Maximize2,
    Plus,
    QrCode,
    Save,
    Sparkles,
    Square,
    Trash2,
    ZoomIn,
    ZoomOut,
} from 'lucide-react';
import { detectSlotsFromImage, loadImage } from '@/components/booth/template-slot-utils';
import {
    PointerEvent as ReactPointerEvent,
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';

interface PrintSizeOption {
    value: string;
    label: string;
    ratio: number;
    dimensions: string;
}

interface SlotPosition {
    id: string;
    x: number;
    y: number;
    width: number;
    height: number;
    shape: SlotShape;
}

interface ServerSlotPosition {
    x: number;
    y: number;
    width: number;
    height: number;
    shape?: SlotShape;
}

interface ExistingTemplate {
    id: number;
    name: string;
    print_size: string;
    photo_slots: number;
    slot_positions: ServerSlotPosition[] | null;
    is_active: boolean;
    frame_url: string | null;
}

interface Props {
    template: ExistingTemplate | null;
    printSizes: PrintSizeOption[];
}

interface FlashProps {
    flash?: { success?: string; error?: string };
    errors?: Record<string, string>;
    [key: string]: unknown;
}

type Orientation = 'portrait' | 'landscape';
type SlotShape = 'rect' | 'round' | 'circle' | 'qr';
type ResizeHandle = 'nw' | 'n' | 'ne' | 'e' | 'se' | 's' | 'sw' | 'w';

interface TextureItem {
    id: string;
    name: string;
    size: number;
    file: File | null;
    url: string;
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0MB';
    if (bytes < 1024) return `${bytes}B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)}KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
}

const ACCENT = '#3b82f6';
const CANVAS_BG = '#0b1220';
const PAGE_BG = '#0a0f1d';
const GRID_COLOR = 'rgba(59,130,246,0.06)';

const MIN_ZOOM = 0.4;
const MAX_ZOOM = 3;
const ZOOM_STEP = 0.15;
const MAX_TEXTURES = 100;

const LAYOUT_TYPES = ['Kolase', 'Strip', 'Majalah', 'Thermal', 'Flipbook'] as const;
type LayoutType = (typeof LAYOUT_TYPES)[number];

const SLOT_COLORS = [
    '#f59e0b',
    '#ef4444',
    '#10b981',
    '#3b82f6',
    '#06b6d4',
    '#8b5cf6',
    '#ec4899',
    '#f97316',
    '#84cc16',
    '#14b8a6',
];

function nextSlotId() {
    return `slot-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

function clamp(value: number, min: number, max: number) {
    return Math.max(min, Math.min(max, value));
}

function normalizeStoredSlots(slots: ServerSlotPosition[] | null): SlotPosition[] {
    if (!slots || slots.length === 0) return [];

    return slots.map((s) => ({
        id: nextSlotId(),
        x: s.x,
        y: s.y,
        width: s.width,
        height: s.height,
        shape: (s.shape as SlotShape) ?? 'rect',
    }));
}

export default function TemplateBuilder({ template, printSizes }: Props) {
    const { props } = usePage<FlashProps>();
    const errors = props.errors ?? {};

    const [name, setName] = useState(template?.name ?? '');
    const [printSize, setPrintSize] = useState(template?.print_size ?? '4R');
    const [orientation, setOrientation] = useState<Orientation>('portrait');
    const [isActive, setIsActive] = useState(template?.is_active ?? true);
    const [slots, setSlots] = useState<SlotPosition[]>(() =>
        normalizeStoredSlots(template?.slot_positions ?? null),
    );
    const [selectedSlotId, setSelectedSlotId] = useState<string | null>(null);
    const [textures, setTextures] = useState<TextureItem[]>(() =>
        template?.frame_url
            ? [
                  {
                      id: 'existing-frame',
                      name: 'Frame tersimpan',
                      size: 0,
                      file: null,
                      url: template.frame_url,
                  },
              ]
            : [],
    );
    const [activeTextureId, setActiveTextureId] = useState<string | null>(
        template?.frame_url ? 'existing-frame' : null,
    );
    const [missingFrameWarning, setMissingFrameWarning] = useState<string | null>(null);

    // Verify existing frame URL actually loads. If not, drop the texture and warn.
    useEffect(() => {
        if (!template?.frame_url) return;
        const img = new Image();
        img.onerror = () => {
            setMissingFrameWarning(
                'File frame tersimpan tidak ditemukan di server. Upload ulang gambar untuk melanjutkan.',
            );
            setTextures((prev) => prev.filter((t) => t.id !== 'existing-frame'));
            setActiveTextureId((current) => (current === 'existing-frame' ? null : current));
        };
        img.src = template.frame_url;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
    const [logoFile, setLogoFile] = useState<File | null>(null);
    const [logoUrl, setLogoUrl] = useState<string | null>(null);
    const [selectedLayoutTypes, setSelectedLayoutTypes] = useState<Set<LayoutType>>(new Set());
    const [zoom, setZoom] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [validationError, setValidationError] = useState<string | null>(null);

    const canvasRef = useRef<HTMLDivElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);
    const textureInputRef = useRef<HTMLInputElement>(null);

    const printSizeOption = useMemo(
        () => printSizes.find((p) => p.value === printSize) ?? printSizes[1],
        [printSize, printSizes],
    );

    const aspectRatio = useMemo(() => {
        const r = printSizeOption?.ratio ?? 4 / 6;
        return orientation === 'portrait' ? r : 1 / r;
    }, [printSizeOption, orientation]);

    const [canvasSize, setCanvasSize] = useState({ width: 480, height: 720 });

    useEffect(() => {
        const recalc = () => {
            const maxH = Math.max(360, window.innerHeight - 140);
            const maxW = Math.min(720, window.innerWidth - 460);
            const byHeight = { h: maxH, w: maxH * aspectRatio };
            const byWidth = { w: maxW, h: maxW / aspectRatio };
            const fit = byHeight.w <= maxW ? byHeight : byWidth;
            setCanvasSize({
                width: Math.max(280, fit.w),
                height: Math.max(280, fit.h),
            });
        };

        recalc();
        window.addEventListener('resize', recalc);
        return () => window.removeEventListener('resize', recalc);
    }, [aspectRatio]);

    // ── Slot operations ──────────────────────────────────────────────────────

    const addSlot = useCallback((shape: SlotShape) => {
        const newSlot: SlotPosition = {
            id: nextSlotId(),
            x: 0.25,
            y: 0.25,
            width: shape === 'circle' || shape === 'qr' ? 0.25 : 0.5,
            height: shape === 'circle' || shape === 'qr' ? 0.25 : 0.3,
            shape,
        };
        setSlots((prev) => [...prev, newSlot]);
        setSelectedSlotId(newSlot.id);
    }, []);

    const deleteSlot = useCallback((id: string) => {
        setSlots((prev) => prev.filter((s) => s.id !== id));
        setSelectedSlotId((current) => (current === id ? null : current));
    }, []);

    const duplicateSlot = useCallback((id: string) => {
        setSlots((prev) => {
            const target = prev.find((s) => s.id === id);
            if (!target) return prev;
            const copy: SlotPosition = {
                ...target,
                id: nextSlotId(),
                x: clamp(target.x + 0.04, 0, 1 - target.width),
                y: clamp(target.y + 0.04, 0, 1 - target.height),
            };
            setSelectedSlotId(copy.id);
            return [...prev, copy];
        });
    }, []);

    // ── Pointer interactions ──────────────────────────────────────────────────

    const dragStateRef = useRef<{
        slotId: string;
        mode: 'move' | ResizeHandle;
        startX: number;
        startY: number;
        origin: SlotPosition;
    } | null>(null);

    const onSlotPointerDown = useCallback(
        (e: ReactPointerEvent<HTMLDivElement>, slot: SlotPosition, mode: 'move' | ResizeHandle) => {
            e.stopPropagation();
            e.preventDefault();
            (e.target as HTMLElement).setPointerCapture(e.pointerId);

            setSelectedSlotId(slot.id);
            dragStateRef.current = {
                slotId: slot.id,
                mode,
                startX: e.clientX,
                startY: e.clientY,
                origin: { ...slot },
            };
        },
        [],
    );

    const onCanvasPointerMove = useCallback((e: ReactPointerEvent<HTMLDivElement>) => {
        const drag = dragStateRef.current;
        if (!drag || !canvasRef.current) return;

        const rect = canvasRef.current.getBoundingClientRect();
        const dxRel = (e.clientX - drag.startX) / rect.width;
        const dyRel = (e.clientY - drag.startY) / rect.height;

        setSlots((prev) =>
            prev.map((s) => {
                if (s.id !== drag.slotId) return s;
                const o = drag.origin;

                if (drag.mode === 'move') {
                    return {
                        ...s,
                        x: clamp(o.x + dxRel, 0, 1 - o.width),
                        y: clamp(o.y + dyRel, 0, 1 - o.height),
                    };
                }

                let { x, y, width, height } = o;
                const minSize = 0.05;

                if (drag.mode.includes('w')) {
                    const newX = clamp(o.x + dxRel, 0, o.x + o.width - minSize);
                    width = o.width - (newX - o.x);
                    x = newX;
                }
                if (drag.mode.includes('e')) {
                    width = clamp(o.width + dxRel, minSize, 1 - o.x);
                }
                if (drag.mode.includes('n')) {
                    const newY = clamp(o.y + dyRel, 0, o.y + o.height - minSize);
                    height = o.height - (newY - o.y);
                    y = newY;
                }
                if (drag.mode.includes('s')) {
                    height = clamp(o.height + dyRel, minSize, 1 - o.y);
                }

                return { ...s, x, y, width, height };
            }),
        );
    }, []);

    const onCanvasPointerUp = useCallback(() => {
        dragStateRef.current = null;
    }, []);

    // ── Keyboard shortcuts ────────────────────────────────────────────────────

    useEffect(() => {
        const handler = (e: KeyboardEvent) => {
            const target = e.target as HTMLElement;
            if (
                target.tagName === 'INPUT' ||
                target.tagName === 'TEXTAREA' ||
                target.isContentEditable
            ) {
                return;
            }

            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (selectedSlotId) {
                    e.preventDefault();
                    deleteSlot(selectedSlotId);
                }
            }
            if (e.key === '1') {
                e.preventDefault();
                addSlot('rect');
            }
            if (e.key === '2') {
                e.preventDefault();
                addSlot('round');
            }
            if (e.key === '3') {
                e.preventDefault();
                addSlot('circle');
            }
            if ((e.ctrlKey || e.metaKey) && (e.key === 'd' || e.key === 'D')) {
                if (selectedSlotId) {
                    e.preventDefault();
                    duplicateSlot(selectedSlotId);
                }
            }
        };

        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [selectedSlotId, addSlot, deleteSlot, duplicateSlot]);

    // ── Texture / frame upload ────────────────────────────────────────────────

    const activeTexture = useMemo(
        () => textures.find((t) => t.id === activeTextureId) ?? null,
        [textures, activeTextureId],
    );

    const frameUrl = activeTexture?.url ?? null;

    const onTextureSelect = useCallback(
        (files: FileList | null) => {
            if (!files) return;
            const remaining = MAX_TEXTURES - textures.length;
            const toAdd = Array.from(files).slice(0, remaining);

            const next: TextureItem[] = toAdd.map((f) => ({
                id: `tex-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
                name: f.name,
                size: f.size,
                file: f,
                url: URL.createObjectURL(f),
            }));

            setTextures((prev) => [...prev, ...next]);
            // Auto-activate the first newly uploaded texture as the canvas background
            setActiveTextureId((current) => current ?? next[0]?.id ?? null);
        },
        [textures.length],
    );

    const removeTexture = useCallback(
        (id: string) => {
            setTextures((prev) => {
                const target = prev.find((t) => t.id === id);
                if (target?.file) URL.revokeObjectURL(target.url);
                return prev.filter((t) => t.id !== id);
            });
            setActiveTextureId((current) => (current === id ? null : current));
        },
        [],
    );

    const clearBackground = useCallback(() => {
        setActiveTextureId(null);
    }, []);

    const onLogoSelect = useCallback(
        (file: File) => {
            if (logoUrl) URL.revokeObjectURL(logoUrl);
            setLogoFile(file);
            setLogoUrl(URL.createObjectURL(file));
        },
        [logoUrl],
    );

    const removeLogo = useCallback(() => {
        if (logoUrl) URL.revokeObjectURL(logoUrl);
        setLogoFile(null);
        setLogoUrl(null);
    }, [logoUrl]);

    // ── Auto-detect slots from frame transparency ─────────────────────────────

    const [detecting, setDetecting] = useState(false);

    const autoDetectSlots = useCallback(
        async (replaceExisting = true) => {
            if (!frameUrl) return;
            setDetecting(true);

            try {
                const image = await loadImage(frameUrl);
                const detected = detectSlotsFromImage(image, 50);

                if (detected.length === 0) {
                    return;
                }

                const w = image.naturalWidth || image.width;
                const h = image.naturalHeight || image.height;

                const newSlots: SlotPosition[] = detected.map((d) => ({
                    id: nextSlotId(),
                    x: d.x / w,
                    y: d.y / h,
                    width: d.width / w,
                    height: d.height / h,
                    shape: 'rect',
                }));

                setSlots((prev) => (replaceExisting ? newSlots : [...prev, ...newSlots]));
            } catch (err) {
                console.error('Auto-detect failed', err);
            } finally {
                setDetecting(false);
            }
        },
        [frameUrl],
    );

    // Auto-run detection when active texture changes (only if no slots yet)
    const lastDetectedTextureRef = useRef<string | null>(null);

    useEffect(() => {
        if (!activeTextureId) return;
        if (lastDetectedTextureRef.current === activeTextureId) return;
        if (slots.length > 0) {
            lastDetectedTextureRef.current = activeTextureId;
            return;
        }

        lastDetectedTextureRef.current = activeTextureId;
        autoDetectSlots(true);
    }, [activeTextureId, slots.length, autoDetectSlots]);

    const moveSlot = useCallback((id: string, direction: 'up' | 'down') => {
        setSlots((prev) => {
            const idx = prev.findIndex((s) => s.id === id);
            if (idx === -1) return prev;
            const target = direction === 'up' ? idx - 1 : idx + 1;
            if (target < 0 || target >= prev.length) return prev;
            const next = [...prev];
            [next[idx], next[target]] = [next[target], next[idx]];
            return next;
        });
    }, []);

    // ── Save ──────────────────────────────────────────────────────────────────

    const frameFile = activeTexture?.file ?? null;
    const hasFrame = !!activeTexture;

    const handleSave = useCallback(async () => {
        setValidationError(null);

        if (!name.trim()) {
            setValidationError('Nama layout wajib diisi.');
            return;
        }
        if (slots.length === 0) {
            setValidationError('Tambahkan minimal satu slot foto.');
            return;
        }
        if (!template && !frameFile) {
            setValidationError('Upload gambar frame/background terlebih dahulu.');
            return;
        }

        const formData = new FormData();
        formData.append('name', name.trim());
        formData.append('print_size', printSize);
        formData.append('photo_slots', String(slots.length));
        formData.append('is_active', isActive ? '1' : '0');
        formData.append(
            'slot_positions',
            JSON.stringify(
                slots.map((s) => ({
                    x: s.x,
                    y: s.y,
                    width: s.width,
                    height: s.height,
                    shape: s.shape,
                })),
            ),
        );

        if (frameFile) {
            formData.append('thumbnail', frameFile);
        }

        if (template) {
            formData.append('_method', 'PUT');
        }

        const url = template ? `/templates/${template.id}` : '/templates';
        const csrf = (
            document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null
        )?.content ?? '';

        setSubmitting(true);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (response.status === 422) {
                const data = await response.json().catch(() => ({}));
                const errs = data.errors ?? {};
                const first = Object.values(errs)[0] as string[] | string | undefined;
                setValidationError(
                    Array.isArray(first) ? first[0] : (first ?? 'Validasi gagal.'),
                );
                setSubmitting(false);
                return;
            }

            if (!response.ok && response.status !== 302 && response.status !== 200) {
                setValidationError(`Gagal menyimpan (HTTP ${response.status}).`);
                setSubmitting(false);
                return;
            }

            // Hard navigate to templates index (Blade page)
            window.location.href = '/templates';
        } catch (err) {
            setValidationError(err instanceof Error ? err.message : 'Gagal menyimpan.');
            setSubmitting(false);
        }
    }, [name, printSize, slots, isActive, frameFile, template]);

    const selectedSlot = useMemo(
        () => slots.find((s) => s.id === selectedSlotId) ?? null,
        [slots, selectedSlotId],
    );

    const layoutLabel = `${printSizeOption?.label} ${orientation === 'portrait' ? 'Portrait' : 'Landscape'}`;

    const validationMessages: string[] = [];
    if (!name.trim()) validationMessages.push('Nama layout wajib diisi');
    if (selectedLayoutTypes.size === 0) validationMessages.push('Pilih minimal satu jenis layout');
    if (slots.length === 0) validationMessages.push('Tambahkan minimal satu slot');
    if (!template && !frameFile) validationMessages.push('Upload minimal satu frame/texture');

    return (
        <>
            <Head title={template ? `Edit ${template.name}` : 'Layout Builder'} />

            <div
                className="fixed inset-0 flex"
                style={{
                    background: PAGE_BG,
                    fontFamily: "'Inter', 'Poppins', sans-serif",
                }}
            >
                {/* ── Canvas area ── */}
                <div className="relative flex flex-1 items-center justify-center overflow-hidden">
                    {/* Grid */}
                    <div
                        className="pointer-events-none absolute inset-0"
                        style={{
                            backgroundImage: `linear-gradient(${GRID_COLOR} 1px, transparent 1px), linear-gradient(90deg, ${GRID_COLOR} 1px, transparent 1px)`,
                            backgroundSize: '32px 32px',
                        }}
                    />

                    {/* Shortcuts panel */}
                    <div className="absolute top-5 left-5 z-30 rounded-xl bg-white px-4 py-3 text-xs text-slate-700 shadow-lg">
                        <p className="mb-2 text-[11px] font-bold tracking-wider text-blue-500">
                            Shortcuts
                        </p>
                        <pre className="font-mono text-[11px] leading-[1.5] text-slate-600">
{`Ctrl+C/V/D  Copy/Paste/Dup
1/2/3       Add Rect/Round/Circle
Del         Delete slot
Wheel       Zoom in/out
W/A/S/D     Pan camera`}
                        </pre>
                    </div>

                    {/* Top center: Back link */}
                    <a
                        href="/templates"
                        className="absolute top-5 left-1/2 z-30 -translate-x-1/2 flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-lg transition hover:bg-slate-50"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Kembali ke Template
                    </a>

                    {/* Canvas */}
                    <div
                        ref={canvasRef}
                        onPointerMove={onCanvasPointerMove}
                        onPointerUp={onCanvasPointerUp}
                        onPointerCancel={onCanvasPointerUp}
                        onPointerDown={() => setSelectedSlotId(null)}
                        onWheel={(e) => {
                            const dir = e.deltaY > 0 ? -1 : 1;
                            setZoom((z) => clamp(z + dir * ZOOM_STEP, MIN_ZOOM, MAX_ZOOM));
                        }}
                        className="relative"
                        style={{
                            width: canvasSize.width,
                            height: canvasSize.height,
                            transform: `scale(${zoom})`,
                            transformOrigin: 'center center',
                            background: CANVAS_BG,
                            border: `2px solid ${ACCENT}`,
                            borderRadius: 4,
                            transition: dragStateRef.current ? 'none' : 'transform 0.15s ease',
                        }}
                    >
                        {frameUrl && (
                            <img
                                src={frameUrl}
                                alt=""
                                className="pointer-events-none absolute inset-0 h-full w-full object-fill opacity-90"
                                draggable={false}
                            />
                        )}

                        {!frameUrl && slots.length === 0 && (
                            <div className="pointer-events-none absolute inset-0 flex items-center justify-center text-sm font-bold text-blue-400">
                                {layoutLabel}
                            </div>
                        )}

                        {slots.map((slot, index) => (
                            <SlotBox
                                key={slot.id}
                                slot={slot}
                                index={index + 1}
                                color={SLOT_COLORS[index % SLOT_COLORS.length]}
                                selected={selectedSlotId === slot.id}
                                onPointerDown={onSlotPointerDown}
                            />
                        ))}
                    </div>

                    {/* Zoom controls bottom-left */}
                    <div className="absolute bottom-5 left-5 z-30 flex gap-1.5">
                        <ZoomBtn
                            icon={<ZoomOut className="h-4 w-4" />}
                            onClick={() => setZoom((z) => clamp(z - ZOOM_STEP, MIN_ZOOM, MAX_ZOOM))}
                            label="Zoom out"
                        />
                        <ZoomBtn
                            icon={<ZoomIn className="h-4 w-4" />}
                            onClick={() => setZoom((z) => clamp(z + ZOOM_STEP, MIN_ZOOM, MAX_ZOOM))}
                            label="Zoom in"
                        />
                        <ZoomBtn
                            icon={<Maximize2 className="h-4 w-4" />}
                            onClick={() => setZoom(1)}
                            label="Reset zoom"
                        />
                        <ZoomBtn
                            icon={<Info className="h-4 w-4" />}
                            onClick={() => {}}
                            label="Info"
                            active
                        />
                    </div>
                </div>

                {/* ── Right sidebar (light card) ── */}
                <aside className="relative z-20 flex h-full w-[400px] shrink-0 flex-col p-4">
                    <div className="flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div className="flex-1 space-y-6 overflow-y-auto px-6 pt-6 pb-4">
                            <div>
                                <h1 className="text-2xl font-bold text-slate-900">Layout Builder</h1>
                                <p className="mt-1 text-sm text-slate-500">
                                    Layout: {layoutLabel}
                                </p>
                            </div>

                            {/* Ukuran Foto */}
                            <Section title="Ukuran Foto">
                                <div className="flex gap-2">
                                    <div className="relative flex-1">
                                        <select
                                            value={printSize}
                                            onChange={(e) => setPrintSize(e.target.value)}
                                            className="w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 py-3 pr-9 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-400"
                                        >
                                            {printSizes.map((opt) => (
                                                <option key={opt.value} value={opt.value}>
                                                    {opt.label}
                                                </option>
                                            ))}
                                        </select>
                                        <ChevronDown className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    </div>
                                    <OrientationToggle
                                        active={orientation === 'portrait'}
                                        onClick={() => setOrientation('portrait')}
                                        icon={<RectIcon vertical />}
                                    />
                                    <OrientationToggle
                                        active={orientation === 'landscape'}
                                        onClick={() => setOrientation('landscape')}
                                        icon={<RectIcon />}
                                    />
                                </div>

                                <div className="mt-3 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900">
                                            {layoutLabel}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {printSizeOption?.dimensions}
                                        </p>
                                    </div>
                                    <p className="text-xs font-semibold text-slate-400">
                                        {printSizeOption?.dimensions.split(/[×x]/)[0].trim()}
                                    </p>
                                </div>
                            </Section>

                            {/* Background Textures */}
                            <Section
                                title="Background Textures"
                                badge={`${textures.length}/${MAX_TEXTURES}`}
                                badgeClass="bg-emerald-100 text-emerald-700"
                            >
                                {missingFrameWarning && (
                                    <div className="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                                        <span className="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[10px] font-bold">!</span>
                                        <span>{missingFrameWarning}</span>
                                    </div>
                                )}
                                <input
                                    ref={textureInputRef}
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    className="hidden"
                                    onChange={(e) => {
                                        onTextureSelect(e.target.files);
                                        e.target.value = '';
                                    }}
                                />
                                <button
                                    type="button"
                                    onClick={() => textureInputRef.current?.click()}
                                    disabled={textures.length >= MAX_TEXTURES}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-blue-400 bg-blue-50/50 py-3.5 text-sm font-semibold text-blue-600 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Plus className="h-4 w-4" />
                                    Upload Images (max 5MB)
                                </button>

                                {textures.length === 0 ? (
                                    <p className="mt-3 text-center text-sm text-slate-400">
                                        Belum ada gambar yang diupload
                                    </p>
                                ) : (
                                    <div className="mt-3 space-y-2">
                                        {textures.map((tex) => {
                                            const active = activeTextureId === tex.id;
                                            return (
                                                <button
                                                    key={tex.id}
                                                    type="button"
                                                    onClick={() => setActiveTextureId(tex.id)}
                                                    className={`flex w-full items-center gap-3 rounded-xl border-2 p-2 text-left transition ${
                                                        active
                                                            ? 'border-blue-500 bg-blue-50/50'
                                                            : 'border-slate-200 bg-white hover:border-blue-300'
                                                    }`}
                                                >
                                                    <div className="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                                        <img
                                                            src={tex.url}
                                                            alt={tex.name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-xs font-bold text-slate-900">
                                                            {tex.name}
                                                        </p>
                                                        <p className="text-[11px] text-slate-500">
                                                            {tex.size > 0 ? formatBytes(tex.size) : 'tersimpan'}
                                                        </p>
                                                    </div>
                                                    <span
                                                        role="button"
                                                        tabIndex={0}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            removeTexture(tex.id);
                                                        }}
                                                        onKeyDown={(e) => {
                                                            if (e.key === 'Enter' || e.key === ' ') {
                                                                e.stopPropagation();
                                                                removeTexture(tex.id);
                                                            }
                                                        }}
                                                        className="rounded p-1 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                                        title="Hapus"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}

                                {hasFrame && (
                                    <button
                                        type="button"
                                        onClick={clearBackground}
                                        className="mt-3 w-full rounded-xl bg-slate-100 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"
                                    >
                                        Clear Background
                                    </button>
                                )}
                            </Section>

                            {/* Logo Overlay */}
                            <Section title="Logo Overlay">
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    className="hidden"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0];
                                        if (file) onLogoSelect(file);
                                        e.target.value = '';
                                    }}
                                />
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-orange-400 bg-orange-50/50 py-3.5 text-sm font-semibold text-orange-600 transition hover:bg-orange-50"
                                >
                                    <Plus className="h-4 w-4" />
                                    {logoUrl ? 'Ganti Logo' : 'Upload Logo Baru'}
                                </button>
                                {logoUrl && (
                                    <div className="mt-2 flex items-center gap-3 rounded-xl border-2 border-orange-300 bg-orange-50/30 p-2">
                                        <div className="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-orange-200 bg-white">
                                            <img
                                                src={logoUrl}
                                                alt="Logo"
                                                className="h-full w-full object-contain"
                                            />
                                        </div>
                                        <p className="flex-1 truncate text-xs font-bold text-slate-900">
                                            {logoFile?.name ?? 'Logo'}
                                        </p>
                                        <button
                                            type="button"
                                            onClick={removeLogo}
                                            className="rounded p-1 text-slate-400 transition hover:bg-red-50 hover:text-red-500"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    </div>
                                )}
                            </Section>

                            {/* Photo Slots */}
                            <Section
                                title="Photo Slots"
                                badge={String(slots.length)}
                            >
                                <div className="grid grid-cols-4 gap-2">
                                    <ShapeButton
                                        icon={<Square className="h-5 w-5" strokeWidth={1.8} />}
                                        label="Rect"
                                        onClick={() => addSlot('rect')}
                                    />
                                    <ShapeButton
                                        icon={
                                            <div className="h-3.5 w-5 rounded-md border-[1.8px] border-current" />
                                        }
                                        label="Round"
                                        onClick={() => addSlot('round')}
                                    />
                                    <ShapeButton
                                        icon={
                                            <div className="h-4 w-4 rounded-full border-[1.8px] border-current" />
                                        }
                                        label="Circle"
                                        onClick={() => addSlot('circle')}
                                    />
                                    <ShapeButton
                                        icon={<QrCode className="h-5 w-5" strokeWidth={1.8} />}
                                        label="QR"
                                        onClick={() => addSlot('qr')}
                                    />
                                </div>

                                {hasFrame && (
                                    <button
                                        type="button"
                                        onClick={() => autoDetectSlots(true)}
                                        disabled={detecting}
                                        className="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-blue-300 bg-blue-50/50 py-2.5 text-xs font-bold text-blue-700 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <Sparkles className="h-3.5 w-3.5" />
                                        {detecting ? 'Mendeteksi...' : 'Auto Detect Slots dari Frame'}
                                    </button>
                                )}

                                {slots.length === 0 ? (
                                    <p className="mt-4 text-center text-sm text-slate-400">
                                        No slots added yet
                                    </p>
                                ) : (
                                    <ul className="mt-3 space-y-1.5">
                                        {slots.map((slot, idx) => {
                                            const active = selectedSlotId === slot.id;
                                            const color = SLOT_COLORS[idx % SLOT_COLORS.length];
                                            return (
                                                <li
                                                    key={slot.id}
                                                    className={`flex items-center gap-2 rounded-lg border px-3 py-2.5 text-xs transition ${
                                                        active
                                                            ? 'border-blue-400 bg-blue-50/60'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <span
                                                        className="h-3 w-3 shrink-0 rounded"
                                                        style={{ background: color }}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setSelectedSlotId(slot.id)}
                                                        className="flex flex-1 flex-col text-left"
                                                    >
                                                        <span className="text-[13px] font-bold text-slate-900">
                                                            Slot {idx + 1}{' '}
                                                            <span className="text-[10px] font-bold text-red-500">
                                                                ({slot.shape.toUpperCase()})
                                                            </span>
                                                        </span>
                                                        <span className="text-[11px] text-slate-500">
                                                            {Math.round(slot.width * 100)}%×
                                                            {Math.round(slot.height * 100)}%
                                                        </span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => moveSlot(slot.id, 'down')}
                                                        disabled={idx === slots.length - 1}
                                                        className="rounded p-0.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30"
                                                        title="Turun"
                                                    >
                                                        <ArrowDown className="h-3.5 w-3.5" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => duplicateSlot(slot.id)}
                                                        className="rounded p-0.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                                        title="Duplikat"
                                                    >
                                                        <Copy className="h-3.5 w-3.5" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => moveSlot(slot.id, 'up')}
                                                        disabled={idx === 0}
                                                        className="rounded p-0.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30"
                                                        title="Naik"
                                                    >
                                                        <ArrowUp className="h-3.5 w-3.5" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => deleteSlot(slot.id)}
                                                        className="rounded p-0.5 text-red-400 transition hover:bg-red-50 hover:text-red-600"
                                                        title="Hapus"
                                                    >
                                                        <Trash2 className="h-3.5 w-3.5" />
                                                    </button>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                )}
                            </Section>

                            {/* Selected slot details */}
                            {selectedSlot && (
                                <Section title="Detail Slot">
                                    <div className="grid grid-cols-2 gap-2">
                                        {(['x', 'y', 'width', 'height'] as const).map((key) => (
                                            <NumField
                                                key={key}
                                                label={key === 'width' ? 'W' : key === 'height' ? 'H' : key.toUpperCase()}
                                                value={selectedSlot[key]}
                                                onChange={(v) =>
                                                    setSlots((prev) =>
                                                        prev.map((s) => {
                                                            if (s.id !== selectedSlot.id) return s;
                                                            const next = { ...s, [key]: v };
                                                            if (key === 'x')
                                                                next.x = clamp(v, 0, 1 - s.width);
                                                            if (key === 'y')
                                                                next.y = clamp(v, 0, 1 - s.height);
                                                            if (key === 'width')
                                                                next.width = clamp(v, 0.05, 1 - s.x);
                                                            if (key === 'height')
                                                                next.height = clamp(v, 0.05, 1 - s.y);
                                                            return next;
                                                        }),
                                                    )
                                                }
                                            />
                                        ))}
                                    </div>
                                </Section>
                            )}

                            {/* Layout Types */}
                            <Section title="Layout Types" required>
                                <div className="flex flex-wrap gap-2">
                                    {LAYOUT_TYPES.map((type) => {
                                        const active = selectedLayoutTypes.has(type);
                                        return (
                                            <button
                                                key={type}
                                                type="button"
                                                onClick={() => {
                                                    setSelectedLayoutTypes((prev) => {
                                                        const next = new Set(prev);
                                                        if (next.has(type)) next.delete(type);
                                                        else next.add(type);
                                                        return next;
                                                    });
                                                }}
                                                className={`rounded-full border px-4 py-1.5 text-xs font-semibold transition ${
                                                    active
                                                        ? 'border-blue-500 bg-blue-500 text-white'
                                                        : 'border-slate-200 bg-white text-slate-700 hover:border-blue-300 hover:bg-blue-50'
                                                }`}
                                            >
                                                {type}
                                            </button>
                                        );
                                    })}
                                </div>
                                {selectedLayoutTypes.size === 0 && (
                                    <p className="mt-2 text-xs font-semibold text-red-500">
                                        Pilih minimal satu jenis layout
                                    </p>
                                )}
                            </Section>

                            {/* Layout Name */}
                            <Section title="Layout Name" required>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    placeholder="Enter layout name..."
                                    className="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-400 placeholder:text-slate-400 placeholder:font-normal"
                                />
                            </Section>

                            {/* Active toggle */}
                            <label className="flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50/60 px-4 py-3">
                                <input
                                    type="checkbox"
                                    checked={isActive}
                                    onChange={(e) => setIsActive(e.target.checked)}
                                    className="mt-0.5 h-4 w-4 rounded accent-blue-500"
                                />
                                <div className="flex-1">
                                    <p className="text-sm font-semibold text-slate-900">
                                        Layout Aktif
                                    </p>
                                    <p className="mt-0.5 text-xs text-slate-500">
                                        Tampilkan layout ini di booth photobooth.
                                    </p>
                                </div>
                            </label>

                            {/* Validation messages */}
                            {validationMessages.length > 0 && (
                                <ul className="space-y-1 text-xs font-semibold text-red-500">
                                    {validationMessages.map((m, i) => (
                                        <li key={i}>• {m}</li>
                                    ))}
                                </ul>
                            )}

                            {validationError && (
                                <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600">
                                    <Info className="mr-1 inline h-3.5 w-3.5" />
                                    {validationError}
                                </div>
                            )}

                            {Object.entries(errors).map(([key, value]) => (
                                <div
                                    key={key}
                                    className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"
                                >
                                    {String(value)}
                                </div>
                            ))}
                        </div>

                        {/* Sticky save area */}
                        <div className="border-t border-slate-200 bg-slate-50 p-4">
                            <button
                                type="button"
                                onClick={handleSave}
                                disabled={submitting || validationMessages.length > 0}
                                className="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-200 py-3.5 text-sm font-bold text-slate-500 transition hover:bg-slate-300 disabled:cursor-not-allowed enabled:bg-blue-500 enabled:text-white enabled:hover:bg-blue-600"
                            >
                                <Save className="h-4 w-4" />
                                {submitting
                                    ? 'Menyimpan...'
                                    : template
                                      ? 'Update Layout'
                                      : 'Simpan Layout'}
                            </button>

                            <a
                                href="/templates"
                                className="mt-2 block w-full rounded-xl border border-slate-200 bg-white py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            >
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </>
    );
}

// ── Subcomponents ────────────────────────────────────────────────────────────

function SlotBox({
    slot,
    index,
    color,
    selected,
    onPointerDown,
}: {
    slot: SlotPosition;
    index: number;
    color: string;
    selected: boolean;
    onPointerDown: (
        e: ReactPointerEvent<HTMLDivElement>,
        slot: SlotPosition,
        mode: 'move' | ResizeHandle,
    ) => void;
}) {
    const isCircle = slot.shape === 'circle';
    const isRound = slot.shape === 'round';
    const isQR = slot.shape === 'qr';

    return (
        <div
            onPointerDown={(e) => onPointerDown(e, slot, 'move')}
            className="absolute"
            style={{
                left: `${slot.x * 100}%`,
                top: `${slot.y * 100}%`,
                width: `${slot.width * 100}%`,
                height: `${slot.height * 100}%`,
                background: `${color}${selected ? '40' : '24'}`,
                border: `2px ${selected ? 'solid' : 'dashed'} ${color}`,
                borderRadius: isCircle ? '50%' : isRound ? 16 : 2,
                cursor: 'move',
                zIndex: selected ? 20 : 10,
            }}
        >
            <div
                className="absolute top-1 left-1 flex h-5 min-w-5 items-center justify-center gap-0.5 rounded px-1 text-[10px] font-black text-white"
                style={{ background: color }}
            >
                {isQR && <QrCode className="h-2.5 w-2.5" />}
                {index}
            </div>

            {selected && (
                <>
                    {(['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'] as ResizeHandle[]).map((h) => (
                        <ResizeHandleDot
                            key={h}
                            handle={h}
                            onPointerDown={(e) => onPointerDown(e, slot, h)}
                        />
                    ))}
                </>
            )}
        </div>
    );
}

function ResizeHandleDot({
    handle,
    onPointerDown,
}: {
    handle: ResizeHandle;
    onPointerDown: (e: ReactPointerEvent<HTMLDivElement>) => void;
}) {
    const positions: Record<ResizeHandle, { top: string; left: string; cursor: string }> = {
        nw: { top: '-5px', left: '-5px', cursor: 'nwse-resize' },
        n: { top: '-5px', left: 'calc(50% - 5px)', cursor: 'ns-resize' },
        ne: { top: '-5px', left: 'calc(100% - 5px)', cursor: 'nesw-resize' },
        e: { top: 'calc(50% - 5px)', left: 'calc(100% - 5px)', cursor: 'ew-resize' },
        se: { top: 'calc(100% - 5px)', left: 'calc(100% - 5px)', cursor: 'nwse-resize' },
        s: { top: 'calc(100% - 5px)', left: 'calc(50% - 5px)', cursor: 'ns-resize' },
        sw: { top: 'calc(100% - 5px)', left: '-5px', cursor: 'nesw-resize' },
        w: { top: 'calc(50% - 5px)', left: '-5px', cursor: 'ew-resize' },
    };

    const pos = positions[handle];

    return (
        <div
            onPointerDown={onPointerDown}
            className="absolute z-30 h-2.5 w-2.5 rounded-sm border-[1.5px] border-blue-400 bg-white"
            style={{ top: pos.top, left: pos.left, cursor: pos.cursor }}
        />
    );
}

function Section({
    title,
    badge,
    badgeClass,
    required,
    children,
}: {
    title: string;
    badge?: string;
    badgeClass?: string;
    required?: boolean;
    children: React.ReactNode;
}) {
    return (
        <div>
            <div className="mb-3 flex items-center justify-between">
                <h3 className="text-base font-bold text-slate-900">
                    {title}
                    {required && <span className="ml-1 text-red-500">*</span>}
                </h3>
                {badge && (
                    <span
                        className={`rounded-full px-2.5 py-0.5 text-[11px] font-bold ${
                            badgeClass ?? 'bg-slate-100 text-slate-700'
                        }`}
                    >
                        {badge}
                    </span>
                )}
            </div>
            {children}
        </div>
    );
}

function OrientationToggle({
    active,
    onClick,
    icon,
}: {
    active: boolean;
    onClick: () => void;
    icon: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="flex h-12 w-12 items-center justify-center rounded-xl transition"
            style={{
                background: active ? ACCENT : '#f1f5f9',
                color: active ? '#fff' : '#64748b',
            }}
        >
            {icon}
        </button>
    );
}

function RectIcon({ vertical }: { vertical?: boolean }) {
    return (
        <div
            className="rounded border-[1.8px] border-current"
            style={{
                width: vertical ? 12 : 18,
                height: vertical ? 18 : 12,
            }}
        />
    );
}

function ShapeButton({
    icon,
    label,
    onClick,
}: {
    icon: React.ReactNode;
    label: string;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="flex flex-col items-center gap-1.5 rounded-xl border border-slate-200 bg-white py-3 text-xs font-semibold text-slate-700 transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600"
        >
            <span className="text-slate-500">{icon}</span>
            {label}
        </button>
    );
}

function NumField({
    label,
    value,
    onChange,
}: {
    label: string;
    value: number;
    onChange: (v: number) => void;
}) {
    return (
        <div className="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5">
            <span className="text-[10px] font-bold text-slate-400 uppercase">{label}</span>
            <input
                type="number"
                step="0.01"
                min={0}
                max={1}
                value={value.toFixed(2)}
                onChange={(e) => onChange(parseFloat(e.target.value) || 0)}
                className="w-full bg-transparent text-xs font-semibold text-slate-900 outline-none"
            />
        </div>
    );
}

function ZoomBtn({
    icon,
    onClick,
    label,
    active,
}: {
    icon: React.ReactNode;
    onClick: () => void;
    label: string;
    active?: boolean;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            title={label}
            className="flex h-9 w-9 items-center justify-center rounded-lg shadow-md transition"
            style={{
                background: active ? ACCENT : '#fff',
                color: active ? '#fff' : '#475569',
            }}
        >
            {icon}
        </button>
    );
}
