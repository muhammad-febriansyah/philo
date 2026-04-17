import { Camera, CheckCircle, Expand, Minimize, RefreshCw, Sparkles } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { api } from '@/lib/api';

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

interface Template {
    id: number;
    name: string;
    frame_path: string | null;
    thumbnail_path: string | null;
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
    sessionId: number;
    photoCount: number;
    countdownSeconds: number;
    template: Template | null;
    onComplete: (photos: CapturedPhoto[]) => void;
}

type CaptureState = 'ready' | 'countdown' | 'flash' | 'uploading' | 'review';
type FilterKey =
    | 'none'
    | 'beauty'
    | 'soft'
    | 'bright'
    | 'warm'
    | 'rose'
    | 'peach'
    | 'cool'
    | 'aqua'
    | 'vintage'
    | 'film'
    | 'cinema'
    | 'moody'
    | 'fade'
    | 'bw'
    | 'noir'
    | 'dramatic'
    | 'pop';

const YELLOW = '#E8C900';
const REVIEW_SECONDS = 5;

const FILTERS: Record<FilterKey, { label: string; css: string }> = {
    none: { label: 'Normal', css: 'none' },
    beauty: {
        label: 'Beauty',
        css: 'brightness(1.08) contrast(0.95) saturate(1.08)',
    },
    soft: {
        label: 'Soft',
        css: 'brightness(1.06) contrast(0.9) saturate(0.96)',
    },
    bright: {
        label: 'Bright',
        css: 'brightness(1.14) contrast(1.02) saturate(1.04)',
    },
    warm: {
        label: 'Warm',
        css: 'sepia(0.22) saturate(1.15) hue-rotate(-10deg) brightness(1.04)',
    },
    rose: {
        label: 'Rose',
        css: 'sepia(0.12) saturate(1.2) hue-rotate(-18deg) brightness(1.05)',
    },
    peach: {
        label: 'Peach',
        css: 'sepia(0.28) saturate(1.18) hue-rotate(-14deg) brightness(1.08) contrast(0.96)',
    },
    cool: {
        label: 'Cool',
        css: 'saturate(1.08) hue-rotate(12deg) brightness(1.03)',
    },
    aqua: {
        label: 'Aqua',
        css: 'saturate(1.18) hue-rotate(24deg) brightness(1.02) contrast(1.04)',
    },
    vintage: {
        label: 'Vintage',
        css: 'sepia(0.45) contrast(0.95) saturate(0.82) brightness(1.02)',
    },
    film: {
        label: 'Film',
        css: 'sepia(0.18) contrast(1.08) saturate(0.9) brightness(0.98)',
    },
    cinema: {
        label: 'Cinema',
        css: 'contrast(1.18) saturate(0.82) brightness(0.94)',
    },
    moody: {
        label: 'Moody',
        css: 'contrast(1.12) saturate(0.78) brightness(0.9)',
    },
    fade: {
        label: 'Fade',
        css: 'contrast(0.88) saturate(0.78) brightness(1.08)',
    },
    bw: { label: 'B&W', css: 'grayscale(1) contrast(1.08)' },
    noir: {
        label: 'Noir',
        css: 'grayscale(1) contrast(1.32) brightness(0.9)',
    },
    dramatic: {
        label: 'Dramatic',
        css: 'contrast(1.24) saturate(1.16) brightness(0.96)',
    },
    pop: {
        label: 'Pop',
        css: 'contrast(1.1) saturate(1.34) brightness(1.04)',
    },
};

function playBeep(frequency: number, duration: number, volume = 0.35) {
    try {
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.value = frequency;
        gain.gain.setValueAtTime(volume, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(
            0.001,
            ctx.currentTime + duration,
        );
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + duration);
        osc.onended = () => ctx.close();
    } catch {
        // Ignore audio API failures.
    }
}

function TemplatePreviewCard({
    template,
    currentOrder,
    photoCount,
}: {
    template: Template | null;
    currentOrder: number;
    photoCount: number;
}) {
    const previewSrc = template?.thumbnail_path ?? template?.frame_path ?? null;

    return (
        <div className="rounded-[1.75rem] border border-white/10 bg-zinc-950 p-4 text-white">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-[11px] font-semibold tracking-[0.22em] text-white/45 uppercase">
                        Layout Aktif
                    </p>
                    <p className="mt-1 text-lg font-bold">
                        {template?.name ?? 'Tanpa Frame'}
                    </p>
                </div>
                <div className="rounded-full bg-white/8 px-3 py-1 text-xs font-semibold text-white/70">
                    {currentOrder}/{photoCount}
                </div>
            </div>

            <div className="mt-4 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5">
                {previewSrc ? (
                    <img
                        src={previewSrc}
                        alt={template?.name ?? 'Template aktif'}
                        className="aspect-[3/4] w-full object-cover"
                    />
                ) : (
                    <div className="flex aspect-[3/4] items-center justify-center bg-[linear-gradient(180deg,_rgba(232,201,0,0.12),_rgba(255,255,255,0.02))]">
                        <div className="flex flex-col items-center gap-3 text-center text-white/70">
                            <div
                                className="flex h-14 w-14 items-center justify-center rounded-full"
                                style={{ background: 'rgba(232,201,0,0.18)' }}
                            >
                                <Camera
                                    className="h-7 w-7"
                                    style={{ color: YELLOW }}
                                />
                            </div>
                            <p className="max-w-[11rem] text-sm">
                                Foto akan tampil clean tanpa overlay frame besar
                                di live preview.
                            </p>
                        </div>
                    </div>
                )}
            </div>

            <div className="mt-4 rounded-[1.35rem] border border-white/8 bg-white/5 px-4 py-3">
                <p className="text-sm text-white/65">
                    Preview kamera dibuat lebih clean supaya wajah tidak ketutup
                    kotak panduan. Template tetap terlihat di panel ini.
                </p>
            </div>
        </div>
    );
}

export default function CaptureStep({
    sessionId,
    photoCount,
    countdownSeconds,
    template,
    onComplete,
}: Props) {
    const videoRef = useRef<HTMLVideoElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const streamRef = useRef<MediaStream | null>(null);

    const [captureState, setCaptureState] = useState<CaptureState>('ready');
    const [countdown, setCountdown] = useState(countdownSeconds);
    const [currentOrder, setCurrentOrder] = useState(1);
    const [photos, setPhotos] = useState<CapturedPhoto[]>([]);
    const [reviewPhoto, setReviewPhoto] = useState<CapturedPhoto | null>(null);
    const [reviewSeconds, setReviewSeconds] = useState(REVIEW_SECONDS);
    const [selectedFilter, setSelectedFilter] = useState<FilterKey>('none');
    const [isFilterPanelOpen, setIsFilterPanelOpen] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isFullscreen, setIsFullscreen] = useState(false);
    const cameraWrapRef = useRef<HTMLDivElement>(null);

    const toggleFullscreen = useCallback(async () => {
        if (!document.fullscreenElement) {
            await cameraWrapRef.current?.requestFullscreen();
        } else {
            await document.exitFullscreen();
        }
    }, []);

    useEffect(() => {
        const onChange = () => setIsFullscreen(!!document.fullscreenElement);
        document.addEventListener('fullscreenchange', onChange);
        return () => document.removeEventListener('fullscreenchange', onChange);
    }, []);

    const filterStyle = useMemo(
        () => FILTERS[selectedFilter].css,
        [selectedFilter],
    );

    useEffect(() => {
        let active = true;

        navigator.mediaDevices
            .getUserMedia({
                video: { width: 1280, height: 720, facingMode: 'user' },
            })
            .then((stream) => {
                if (!active) {
                    stream.getTracks().forEach((track) => track.stop());

                    return;
                }

                streamRef.current = stream;

                if (videoRef.current) {
                    videoRef.current.srcObject = stream;
                }
            })
            .catch(() =>
                setError(
                    'Kamera tidak dapat diakses. Pastikan izin kamera diberikan.',
                ),
            );

        return () => {
            active = false;
            streamRef.current?.getTracks().forEach((track) => track.stop());
        };
    }, []);

    useEffect(() => {
        if (captureState !== 'review' || !reviewPhoto) {
            return;
        }

        const id = setInterval(() => {
            setReviewSeconds((seconds) => {
                if (seconds <= 1) {
                    clearInterval(id);
                    window.setTimeout(() => {
                        setReviewPhoto((current) => {
                            if (!current) {
                                return current;
                            }

                            if (current.order >= photoCount) {
                                onComplete(
                                    [...photos].sort(
                                        (a, b) => a.order - b.order,
                                    ),
                                );
                            } else {
                                setCurrentOrder(current.order + 1);
                                setCaptureState('ready');
                            }

                            return null;
                        });
                    }, 0);

                    return 0;
                }

                return seconds - 1;
            });
        }, 1000);

        return () => clearInterval(id);
    }, [captureState, onComplete, photoCount, photos, reviewPhoto]);

    const replacePhoto = useCallback((incoming: CapturedPhoto) => {
        setPhotos((previous) => {
            const next = previous.filter(
                (photo) => photo.order !== incoming.order,
            );
            next.push(incoming);

            return next.sort((a, b) => a.order - b.order);
        });
    }, []);

    const captureFrame = useCallback((): string | null => {
        const video = videoRef.current;
        const canvas = canvasRef.current;

        if (!video || !canvas || video.readyState < 2) {
            return null;
        }

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return null;
        }

        ctx.save();
        ctx.filter = filterStyle;
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();

        return canvas.toDataURL('image/jpeg', 0.92);
    }, [filterStyle]);

    const goToNextPhoto = useCallback(() => {
        if (!reviewPhoto) {
            return;
        }

        if (reviewPhoto.order >= photoCount) {
            onComplete([...photos].sort((a, b) => a.order - b.order));
        } else {
            setCurrentOrder(reviewPhoto.order + 1);
            setCaptureState('ready');
        }

        setReviewPhoto(null);
        setReviewSeconds(REVIEW_SECONDS);
    }, [onComplete, photoCount, photos, reviewPhoto]);

    const retakeCurrentPhoto = useCallback(() => {
        if (!reviewPhoto) {
            return;
        }

        setPhotos((previous) =>
            previous.filter((photo) => photo.order !== reviewPhoto.order),
        );
        setReviewPhoto(null);
        setReviewSeconds(REVIEW_SECONDS);
        setCaptureState('ready');
    }, [reviewPhoto]);

    const startCountdown = useCallback(() => {
        if (captureState !== 'ready') {
            return;
        }

        setCaptureState('countdown');
        setCountdown(countdownSeconds);
        playBeep(880, 0.12);

        let remaining = countdownSeconds;
        const id = setInterval(() => {
            remaining -= 1;
            setCountdown(remaining);

            if (remaining > 0) {
                playBeep(880, 0.12);
            }

            if (remaining <= 0) {
                clearInterval(id);
                playBeep(1200, 0.18);
                setCaptureState('flash');

                setTimeout(() => {
                    const dataUrl = captureFrame();

                    if (!dataUrl) {
                        setCaptureState('ready');

                        return;
                    }

                    setCaptureState('uploading');

                    api.post<{ photo_id: number; url: string }>(
                        '/booth/photo/capture',
                        {
                            session_id: sessionId,
                            photo_data: dataUrl,
                            order: currentOrder,
                        },
                    )
                        .then((response) => {
                            const captured = {
                                id: response.photo_id,
                                url: response.url,
                                order: currentOrder,
                            };
                            replacePhoto(captured);
                            setReviewSeconds(REVIEW_SECONDS);
                            setReviewPhoto(captured);
                            setCaptureState('review');
                        })
                        .catch(() => {
                            setCaptureState('ready');
                        });
                }, 180);
            }
        }, 1000);
    }, [
        captureFrame,
        captureState,
        countdownSeconds,
        currentOrder,
        replacePhoto,
        sessionId,
    ]);

    const isLive = captureState !== 'review';
    const currentPreview = reviewPhoto?.url ?? null;
    const progressLabel =
        captureState === 'countdown'
            ? 'Bersiap difoto'
            : captureState === 'uploading'
              ? 'Menyimpan hasil'
              : captureState === 'review'
                ? 'Review cepat'
                : 'Siap ambil foto';

    return (
        <div className="relative min-h-screen overflow-hidden px-4 py-4 md:px-6 md:py-6">
            <div
                className="pointer-events-none absolute -top-24 left-[-4rem] h-72 w-72 rounded-full blur-3xl"
                style={{ background: 'rgba(232,201,0,0.24)' }}
            />
            <div
                className="pointer-events-none absolute top-1/3 right-[-5rem] h-80 w-80 rounded-full blur-3xl"
                style={{ background: 'rgba(24,24,27,0.08)' }}
            />

            <div className="relative z-10 flex min-h-[calc(100vh-3rem)] flex-col gap-5">
                <div className="rounded-[2rem] border border-white/70 bg-white/72 px-5 py-5 backdrop-blur-xl">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <div className="inline-flex items-center gap-2 rounded-full bg-zinc-950 px-4 py-2 text-[11px] font-semibold tracking-[0.24em] text-white uppercase shadow-lg">
                                <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-400" />
                                Live Camera
                            </div>
                            <h2
                                className="mt-4 text-3xl leading-tight font-extrabold text-zinc-950 md:text-4xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Sesi Foto
                            </h2>
                            <p className="mt-2 max-w-2xl text-sm text-zinc-500 md:text-base">
                                Preview kamera dibuat lebih bersih dan fokus ke
                                wajah. Frame tetap aktif, tapi panduan kotak
                                besar dihilangkan supaya hasil terasa lebih
                                premium.
                            </p>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3">
                            <div className="rounded-[1.4rem] bg-white/60 px-4 py-3">
                                <p className="text-xs text-zinc-400">
                                    Countdown
                                </p>
                                <p className="mt-1 text-xl font-bold text-zinc-950">
                                    {countdownSeconds} detik
                                </p>
                            </div>
                            <div className="rounded-[1.4rem] bg-white/60 px-4 py-3">
                                <p className="text-xs text-zinc-400">
                                    Frame Aktif
                                </p>
                                <p className="mt-1 line-clamp-1 text-xl font-bold text-zinc-950">
                                    {template?.name ?? 'Tanpa frame'}
                                </p>
                            </div>
                            <div className="rounded-[1.4rem] bg-[linear-gradient(180deg,_rgba(232,201,0,0.14),_rgba(255,255,255,0.95))] px-4 py-3">
                                <p className="text-xs text-zinc-500">Status</p>
                                <p className="mt-1 text-xl font-bold text-zinc-950">
                                    {progressLabel}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="mt-5 flex flex-wrap gap-2">
                        {Array.from({ length: photoCount }).map((_, index) => {
                            const done = photos.some(
                                (photo) => photo.order === index + 1,
                            );
                            const current = index + 1 === currentOrder;

                            return (
                                <div
                                    key={index}
                                    className="rounded-full border px-3 py-2 text-sm font-semibold transition-all duration-200"
                                    style={
                                        done
                                            ? {
                                                  borderColor:
                                                      'rgba(34,197,94,0.26)',
                                                  background:
                                                      'rgba(34,197,94,0.10)',
                                                  color: '#15803d',
                                              }
                                            : current
                                              ? {
                                                    borderColor:
                                                        'rgba(232,201,0,0.5)',
                                                    background:
                                                        'rgba(232,201,0,0.16)',
                                                    color: '#18181b',
                                                }
                                              : {
                                                    borderColor: '#e4e4e7',
                                                    background: '#ffffff',
                                                    color: '#71717a',
                                                }
                                    }
                                >
                                    {done
                                        ? `Foto ${index + 1} selesai`
                                        : `Foto ${index + 1}`}
                                </div>
                            );
                        })}
                    </div>
                </div>

                <div className="grid flex-1 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div className="flex min-h-[520px] flex-col rounded-[2.2rem] border border-white/70 bg-white/78 p-4 backdrop-blur-xl md:p-5">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p className="text-xs font-semibold tracking-[0.22em] text-zinc-400 uppercase">
                                    Filter Kamera
                                </p>
                                <div className="mt-3 flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setIsFilterPanelOpen(
                                                (current) => !current,
                                            )
                                        }
                                        className="inline-flex items-center gap-2 rounded-full bg-zinc-900 px-4 py-2 text-sm font-semibold text-white"
                                    >
                                        <Sparkles className="h-4 w-4" />
                                        {isFilterPanelOpen
                                            ? 'Tutup Filter'
                                            : 'Buka Filter'}
                                    </button>
                                    <div className="rounded-full bg-white px-3 py-2 text-sm font-semibold text-zinc-700">
                                        Filter aktif: {FILTERS[selectedFilter].label}
                                    </div>
                                </div>
                                {isFilterPanelOpen && (
                                    <div className="mt-3 rounded-[1.1rem] border border-zinc-200 bg-white p-3">
                                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
                                            {Object.entries(FILTERS).map(
                                                ([key, filter]) => {
                                                    const isSelected =
                                                        selectedFilter === key;

                                                    return (
                                                        <button
                                                            key={key}
                                                            type="button"
                                                            onClick={() =>
                                                                setSelectedFilter(
                                                                    key as FilterKey,
                                                                )
                                                            }
                                                            className="rounded-lg border p-2 text-left transition"
                                                            style={
                                                                isSelected
                                                                    ? {
                                                                          borderColor:
                                                                              '#E8C900',
                                                                          background:
                                                                              'rgba(232,201,0,0.12)',
                                                                      }
                                                                    : {
                                                                          borderColor:
                                                                              '#E4E4E7',
                                                                          background:
                                                                              '#fff',
                                                                      }
                                                            }
                                                        >
                                                            <div
                                                                className="h-12 w-full rounded-md"
                                                                style={{
                                                                    background:
                                                                        'linear-gradient(135deg, #f6d8c5 0%, #a8ccff 55%, #d8f8de 100%)',
                                                                    filter: filter.css,
                                                                }}
                                                            />
                                                            <p className="mt-2 text-xs font-semibold text-zinc-700">
                                                                {filter.label}
                                                            </p>
                                                        </button>
                                                    );
                                                },
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div className="inline-flex items-center gap-2 rounded-full bg-white/60 px-4 py-2 text-sm font-semibold text-zinc-700">
                                <span
                                    className="h-2.5 w-2.5 rounded-full"
                                    style={{
                                        background:
                                            captureState === 'review'
                                                ? '#22c55e'
                                                : captureState === 'countdown'
                                                  ? '#f59e0b'
                                                  : '#18181b',
                                    }}
                                />
                                {progressLabel}
                            </div>
                        </div>

                        <div className="relative mt-4 min-h-[380px] flex-1">
                            <div
                                ref={cameraWrapRef}
                                className="relative h-full overflow-hidden rounded-[1.9rem] border border-[#ece2b6] bg-[#f4efe2]"
                                style={{
                                    outline:
                                        captureState === 'ready'
                                            ? '2px solid rgba(232,201,0,0.22)'
                                            : 'none',
                                    outlineOffset: '4px',
                                }}
                            >
                                <video
                                    ref={videoRef}
                                    autoPlay
                                    playsInline
                                    muted
                                    className="h-full w-full object-cover"
                                    style={{
                                        transform: 'scaleX(-1)',
                                        filter: isLive ? filterStyle : 'none',
                                        display: currentPreview
                                            ? 'none'
                                            : 'block',
                                    }}
                                />

                                {currentPreview && (
                                    <img
                                        src={currentPreview}
                                        alt="Captured"
                                        className="h-full w-full object-cover"
                                        style={{ filter: filterStyle }}
                                    />
                                )}

                                <div className="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-black/35 to-transparent" />
                                <div className="pointer-events-none absolute inset-x-0 bottom-0 h-36 bg-gradient-to-t from-black/45 to-transparent" />

                                <button
                                    onClick={toggleFullscreen}
                                    className="absolute top-3 right-3 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-sm transition hover:bg-black/70 active:scale-95"
                                    title={isFullscreen ? 'Keluar Fullscreen' : 'Fullscreen'}
                                >
                                    {isFullscreen ? (
                                        <Minimize className="h-4 w-4" />
                                    ) : (
                                        <Expand className="h-4 w-4" />
                                    )}
                                </button>

                                {captureState === 'flash' && (
                                    <div
                                        className="absolute inset-0 animate-pulse bg-white"
                                        style={{ opacity: 0.92 }}
                                    />
                                )}

                                {captureState === 'uploading' && (
                                    <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/40 backdrop-blur-sm">
                                        <div className="h-10 w-10 animate-spin rounded-full border-4 border-white/30 border-t-white" />
                                        <p className="text-base font-semibold text-white">
                                            Menyimpan foto...
                                        </p>
                                    </div>
                                )}

                                {captureState === 'countdown' && (
                                    <div className="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[2px]">
                                        <div className="relative flex items-center justify-center">
                                            <div
                                                className="absolute animate-ping rounded-full"
                                                style={{
                                                    width: 220,
                                                    height: 220,
                                                    background:
                                                        'rgba(232,201,0,0.16)',
                                                }}
                                            />
                                            <div
                                                className="absolute rounded-full"
                                                style={{
                                                    width: 178,
                                                    height: 178,
                                                    border: '3px solid rgba(232,201,0,0.45)',
                                                }}
                                            />
                                            <div
                                                className="relative flex h-[8.5rem] w-[8.5rem] items-center justify-center rounded-full shadow-2xl"
                                                style={{ background: YELLOW }}
                                            >
                                                <span className="text-7xl leading-none font-black text-black">
                                                    {countdown}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {captureState === 'review' && reviewPhoto && (
                                    <div className="absolute inset-x-0 bottom-0 px-6 pt-16 pb-6 text-white">
                                        <div className="rounded-[1.6rem] border border-white/15 bg-black/45 p-5 backdrop-blur-md">
                                            <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                                                <div>
                                                    <p className="text-sm tracking-[0.3em] text-white/70 uppercase">
                                                        Preview Foto
                                                    </p>
                                                    <p className="mt-2 text-2xl font-bold">
                                                        Foto {reviewPhoto.order}{' '}
                                                        sudah tersimpan
                                                    </p>
                                                    <p className="text-sm text-white/80">
                                                        Ulangi jika kurang pas,
                                                        atau lanjut otomatis
                                                        dalam {reviewSeconds}{' '}
                                                        detik.
                                                    </p>
                                                </div>
                                                <div className="rounded-full border border-white/30 bg-white/10 px-4 py-2 text-lg font-bold">
                                                    {reviewSeconds}s
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {captureState === 'ready' && (
                                    <>
                                        <div
                                            className="absolute top-5 left-5 flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold tracking-[0.24em] text-black uppercase shadow-lg"
                                            style={{ background: YELLOW }}
                                        >
                                            <span className="h-2 w-2 animate-pulse rounded-full bg-black" />
                                            Live
                                        </div>

                                        <div className="absolute top-5 right-5 rounded-full bg-white/88 px-4 py-2 text-sm font-semibold text-zinc-800 shadow-lg backdrop-blur-sm">
                                            Foto {currentOrder} dari{' '}
                                            {photoCount}
                                        </div>

                                        <div className="absolute bottom-5 left-5 rounded-full bg-black/50 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                                            {template
                                                ? 'Frame tidak tampil di live preview. Frame diterapkan saat hasil akhir.'
                                                : 'Tersenyum, atur pose, lalu ambil foto'}
                                        </div>

                                        {template && (
                                            <div className="absolute right-5 bottom-5 rounded-[1.2rem] border border-white/30 bg-white/18 px-4 py-3 text-right text-white shadow-lg backdrop-blur-md">
                                                <p className="text-[11px] font-semibold tracking-[0.2em] text-white/70 uppercase">
                                                    Layout
                                                </p>
                                                <p className="mt-1 text-base font-bold">
                                                    {template.name}
                                                </p>
                                                <p className="text-sm text-white/80">
                                                    Slot {currentOrder}/
                                                    {photoCount}
                                                </p>
                                            </div>
                                        )}
                                    </>
                                )}

                                {error && (
                                    <div className="absolute inset-0 flex items-center justify-center bg-zinc-950/80">
                                        <div className="mx-8 rounded-2xl bg-white p-6 text-center shadow-xl">
                                            <p className="font-semibold text-red-500">
                                                {error}
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col gap-4">
                        <TemplatePreviewCard
                            template={template}
                            currentOrder={currentOrder}
                            photoCount={photoCount}
                        />

                        <div className="rounded-[1.75rem] border border-white/70 bg-white/80 p-4 backdrop-blur-xl">
                            <p className="text-xs font-semibold tracking-[0.2em] text-zinc-400 uppercase">
                                Progress
                            </p>
                            <div className="mt-4 grid grid-cols-2 gap-3">
                                {Array.from({ length: photoCount }).map(
                                    (_, index) => {
                                        const done = photos.some(
                                            (photo) =>
                                                photo.order === index + 1,
                                        );
                                        const current =
                                            index + 1 === currentOrder;

                                        return (
                                            <div
                                                key={index}
                                                className="rounded-[1.2rem] border px-3 py-3 text-center"
                                                style={
                                                    done
                                                        ? {
                                                              borderColor:
                                                                  'rgba(34,197,94,0.24)',
                                                              background:
                                                                  'rgba(34,197,94,0.08)',
                                                          }
                                                        : current
                                                          ? {
                                                                borderColor:
                                                                    'rgba(232,201,0,0.4)',
                                                                background:
                                                                    'rgba(232,201,0,0.12)',
                                                            }
                                                          : {
                                                                borderColor:
                                                                    '#e4e4e7',
                                                                background:
                                                                    '#fff',
                                                            }
                                                }
                                            >
                                                <p className="text-xs text-zinc-400">
                                                    Slot
                                                </p>
                                                <p className="mt-1 text-xl font-bold text-zinc-950">
                                                    {index + 1}
                                                </p>
                                                <p
                                                    className="mt-1 text-xs font-semibold"
                                                    style={{
                                                        color: done
                                                            ? '#15803d'
                                                            : current
                                                              ? '#18181b'
                                                              : '#71717a',
                                                    }}
                                                >
                                                    {done
                                                        ? 'Selesai'
                                                        : current
                                                          ? 'Aktif'
                                                          : 'Menunggu'}
                                                </p>
                                            </div>
                                        );
                                    },
                                )}
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/70 bg-[linear-gradient(180deg,_rgba(232,201,0,0.14),_rgba(255,255,255,0.92))] p-4 backdrop-blur-xl">
                            <p className="text-xs font-semibold tracking-[0.2em] text-zinc-500 uppercase">
                                Tips Pose
                            </p>
                            <div className="mt-3 space-y-3 text-sm text-zinc-700">
                                <p>Jaga wajah tetap di tengah frame kamera.</p>
                                <p>
                                    Cahaya depan lebih bagus daripada lampu dari
                                    belakang.
                                </p>
                                <p>
                                    Ubah filter dulu kalau ingin tone yang lebih
                                    hangat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="rounded-[2rem] border border-white/70 bg-white/78 p-3 backdrop-blur-xl">
                    <div className="flex flex-col gap-3 md:flex-row">
                        {captureState === 'review' && reviewPhoto ? (
                            <>
                                <button
                                    onClick={retakeCurrentPhoto}
                                    className="flex items-center justify-center gap-2 rounded-[1.4rem] bg-white/70 px-6 py-4 text-base font-semibold text-zinc-700 transition hover:bg-white/90 active:scale-95"
                                >
                                    <RefreshCw className="h-5 w-5" />
                                    Ulangi Foto Ini
                                </button>
                                <button
                                    onClick={goToNextPhoto}
                                    className="flex flex-1 items-center justify-center gap-3 rounded-[1.4rem] py-4 text-lg font-black text-black shadow-lg transition-all duration-200 active:scale-[0.98]"
                                    style={{
                                        background: YELLOW,
                                        boxShadow:
                                            '0 6px 28px rgba(232,201,0,0.35)',
                                    }}
                                >
                                    <CheckCircle
                                        className="h-6 w-6"
                                        strokeWidth={2.5}
                                    />
                                    {reviewPhoto.order >= photoCount
                                        ? 'Lanjut ke Preview Hasil'
                                        : 'Lanjut ke Foto Berikutnya'}
                                </button>
                            </>
                        ) : (
                            <button
                                onClick={startCountdown}
                                disabled={captureState !== 'ready' || !!error}
                                className="flex flex-1 cursor-pointer items-center justify-center gap-3 rounded-[1.4rem] py-4 text-lg font-black text-black shadow-lg transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                                style={{
                                    background:
                                        captureState === 'countdown'
                                            ? 'rgba(232,201,0,0.7)'
                                            : YELLOW,
                                    boxShadow:
                                        '0 6px 28px rgba(232,201,0,0.35)',
                                }}
                            >
                                <Camera className="h-6 w-6" strokeWidth={2.5} />
                                {captureState === 'countdown'
                                    ? `Bersiap... ${countdown}`
                                    : 'Ambil Foto'}
                            </button>
                        )}
                    </div>
                </div>
            </div>

            <canvas ref={canvasRef} className="hidden" />
        </div>
    );
}
