import { Camera, CheckCircle, Expand, Eye, EyeOff, Image as ImageIcon, Minimize, RefreshCw, Sparkles } from 'lucide-react';
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

const FILTERS: Record<FilterKey, { label: string; css: string; preview: string }> = {
    none: {
        label: 'Normal',
        css: 'none',
        preview: 'linear-gradient(135deg, #f9c784 0%, #e8a87c 40%, #c4e0f9 100%)',
    },
    beauty: {
        label: 'Beauty',
        css: 'brightness(1.08) contrast(0.95) saturate(1.08)',
        preview: 'linear-gradient(135deg, #fde8c8 0%, #f7c5a8 40%, #ffd6e0 100%)',
    },
    soft: {
        label: 'Soft',
        css: 'brightness(1.06) contrast(0.9) saturate(0.96)',
        preview: 'linear-gradient(135deg, #fef0e8 0%, #fad8cc 40%, #e8e4f0 100%)',
    },
    bright: {
        label: 'Bright',
        css: 'brightness(1.14) contrast(1.02) saturate(1.04)',
        preview: 'linear-gradient(135deg, #fff8d6 0%, #ffe896 40%, #e0f4ff 100%)',
    },
    warm: {
        label: 'Warm',
        css: 'sepia(0.22) saturate(1.15) hue-rotate(-10deg) brightness(1.04)',
        preview: 'linear-gradient(135deg, #ffd580 0%, #e89c4a 40%, #c8a060 100%)',
    },
    rose: {
        label: 'Rose',
        css: 'sepia(0.12) saturate(1.2) hue-rotate(-18deg) brightness(1.05)',
        preview: 'linear-gradient(135deg, #ffd6e0 0%, #f4a0b0 40%, #ffb3c1 100%)',
    },
    peach: {
        label: 'Peach',
        css: 'sepia(0.28) saturate(1.18) hue-rotate(-14deg) brightness(1.08) contrast(0.96)',
        preview: 'linear-gradient(135deg, #ffc8a0 0%, #f0a878 40%, #ffe0c8 100%)',
    },
    cool: {
        label: 'Cool',
        css: 'saturate(1.08) hue-rotate(12deg) brightness(1.03)',
        preview: 'linear-gradient(135deg, #c8e8ff 0%, #a0c8f0 40%, #d8f0e8 100%)',
    },
    aqua: {
        label: 'Aqua',
        css: 'saturate(1.18) hue-rotate(24deg) brightness(1.02) contrast(1.04)',
        preview: 'linear-gradient(135deg, #a0f0e8 0%, #60d0c8 40%, #b8f0ff 100%)',
    },
    vintage: {
        label: 'Vintage',
        css: 'sepia(0.45) contrast(0.95) saturate(0.82) brightness(1.02)',
        preview: 'linear-gradient(135deg, #d4b896 0%, #b89060 40%, #c8a878 100%)',
    },
    film: {
        label: 'Film',
        css: 'sepia(0.18) contrast(1.08) saturate(0.9) brightness(0.98)',
        preview: 'linear-gradient(135deg, #c8b898 0%, #a89878 40%, #b8a888 100%)',
    },
    cinema: {
        label: 'Cinema',
        css: 'contrast(1.18) saturate(0.82) brightness(0.94)',
        preview: 'linear-gradient(135deg, #786858 0%, #584838 40%, #908070 100%)',
    },
    moody: {
        label: 'Moody',
        css: 'contrast(1.12) saturate(0.78) brightness(0.9)',
        preview: 'linear-gradient(135deg, #605850 0%, #403830 40%, #706860 100%)',
    },
    fade: {
        label: 'Fade',
        css: 'contrast(0.88) saturate(0.78) brightness(1.08)',
        preview: 'linear-gradient(135deg, #e8e0d8 0%, #d0c8c0 40%, #e0d8d0 100%)',
    },
    bw: {
        label: 'B&W',
        css: 'grayscale(1) contrast(1.08)',
        preview: 'linear-gradient(135deg, #d8d8d8 0%, #909090 40%, #c0c0c0 100%)',
    },
    noir: {
        label: 'Noir',
        css: 'grayscale(1) contrast(1.32) brightness(0.9)',
        preview: 'linear-gradient(135deg, #b0b0b0 0%, #484848 40%, #808080 100%)',
    },
    dramatic: {
        label: 'Dramatic',
        css: 'contrast(1.24) saturate(1.16) brightness(0.96)',
        preview: 'linear-gradient(135deg, #c87848 0%, #803828 40%, #b06840 100%)',
    },
    pop: {
        label: 'Pop',
        css: 'contrast(1.1) saturate(1.34) brightness(1.04)',
        preview: 'linear-gradient(135deg, #ff9060 0%, #f060a0 40%, #60c0ff 100%)',
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
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + duration);
        osc.onended = () => ctx.close();
    } catch {
        // Ignore audio API failures.
    }
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
    const filterThumbRefs = useRef<Record<string, HTMLCanvasElement | null>>({});

    const [captureState, setCaptureState] = useState<CaptureState>('ready');
    const [countdown, setCountdown] = useState(countdownSeconds);
    const [currentOrder, setCurrentOrder] = useState(1);
    const [photos, setPhotos] = useState<CapturedPhoto[]>([]);
    const [reviewPhoto, setReviewPhoto] = useState<CapturedPhoto | null>(null);
    const [reviewSeconds, setReviewSeconds] = useState(REVIEW_SECONDS);
    const [selectedFilter, setSelectedFilter] = useState<FilterKey>('none');
    const [filtersVisible, setFiltersVisible] = useState(true);
    const [videoReady, setVideoReady] = useState(false);
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

    const filterStyle = useMemo(() => FILTERS[selectedFilter].css, [selectedFilter]);

    useEffect(() => {
        let active = true;

        navigator.mediaDevices
            .getUserMedia({ video: { width: 1280, height: 720, facingMode: 'user' } })
            .then((stream) => {
                if (!active) {
                    stream.getTracks().forEach((track) => track.stop());
                    return;
                }
                streamRef.current = stream;
                const video = videoRef.current;
                if (video) {
                    video.srcObject = stream;
                    const onReady = () => {
                        if (video.videoWidth > 0) setVideoReady(true);
                    };
                    video.addEventListener('loadeddata', onReady);
                    video.addEventListener('playing', onReady);
                }
            })
            .catch(() => setError('Kamera tidak dapat diakses. Pastikan izin kamera diberikan.'));

        return () => {
            active = false;
            streamRef.current?.getTracks().forEach((track) => track.stop());
        };
    }, []);

    // Live filter thumbnail preview — render the user's face onto each
    // filter chip so they can see the effect before picking, like a
    // professional photobooth app.
    useEffect(() => {
        if (!videoReady) return;
        if (captureState !== 'ready') return;
        if (!filtersVisible) return;

        let rafId = 0;
        let lastDraw = 0;
        const intervalMs = 120; // ~8fps is enough for thumbnails

        const draw = (now: number) => {
            rafId = requestAnimationFrame(draw);
            if (now - lastDraw < intervalMs) return;
            lastDraw = now;

            const video = videoRef.current;
            if (!video || video.readyState < 2 || video.videoWidth === 0) return;

            const sw = video.videoWidth;
            const sh = video.videoHeight;
            const size = Math.min(sw, sh);
            const sx = (sw - size) / 2;
            const sy = (sh - size) / 2;

            for (const key of Object.keys(FILTERS) as FilterKey[]) {
                const canvas = filterThumbRefs.current[key];
                if (!canvas) continue;
                const ctx = canvas.getContext('2d');
                if (!ctx) continue;

                const dw = canvas.width;
                const dh = canvas.height;

                ctx.save();
                ctx.filter = FILTERS[key].css;
                ctx.translate(dw, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, sx, sy, size, size, 0, 0, dw, dh);
                ctx.restore();
            }
        };

        rafId = requestAnimationFrame(draw);
        return () => cancelAnimationFrame(rafId);
    }, [videoReady, captureState, filtersVisible]);


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
                            if (!current) return current;
                            if (current.order >= photoCount) {
                                onComplete([...photos].sort((a, b) => a.order - b.order));
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
            const next = previous.filter((photo) => photo.order !== incoming.order);
            next.push(incoming);
            return next.sort((a, b) => a.order - b.order);
        });
    }, []);

    const captureFrame = useCallback((): string | null => {
        const video = videoRef.current;
        const canvas = canvasRef.current;

        if (!video || !canvas || video.readyState < 2) return null;

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');

        if (!ctx) return null;

        ctx.save();
        ctx.filter = filterStyle;
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.restore();

        return canvas.toDataURL('image/jpeg', 0.92);
    }, [filterStyle]);

    const goToNextPhoto = useCallback(() => {
        if (!reviewPhoto) return;

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
        if (!reviewPhoto) return;
        setPhotos((previous) => previous.filter((photo) => photo.order !== reviewPhoto.order));
        setReviewPhoto(null);
        setReviewSeconds(REVIEW_SECONDS);
        setCaptureState('ready');
    }, [reviewPhoto]);

    const startCountdown = useCallback(() => {
        if (captureState !== 'ready') return;

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

                    api.post<{ photo_id: number; url: string }>('/booth/photo/capture', {
                        session_id: sessionId,
                        photo_data: dataUrl,
                        order: currentOrder,
                    })
                        .then((response) => {
                            const captured = { id: response.photo_id, url: response.url, order: currentOrder };
                            replacePhoto(captured);
                            setReviewSeconds(REVIEW_SECONDS);
                            setReviewPhoto(captured);
                            setCaptureState('review');
                        })
                        .catch(() => setCaptureState('ready'));
                }, 180);
            }
        }, 1000);
    }, [captureFrame, captureState, countdownSeconds, currentOrder, replacePhoto, sessionId]);

    const isLive = captureState !== 'review';
    const currentPreview = reviewPhoto?.url ?? null;
    const progressLabel =
        captureState === 'countdown'
            ? 'Bersiap difoto'
            : captureState === 'uploading'
              ? 'Menyimpan...'
              : captureState === 'review'
                ? 'Review cepat'
                : 'Siap ambil foto';

    const statusColor =
        captureState === 'review'
            ? '#22c55e'
            : captureState === 'countdown'
              ? YELLOW
              : captureState === 'uploading'
                ? '#f59e0b'
                : '#a1a1aa';

    const buttonLabel =
        captureState === 'countdown'
            ? `Bersiap... ${countdown}`
            : captureState === 'uploading'
              ? 'Menyimpan...'
              : 'Ambil Foto';

    return (
        <div className="relative flex min-h-screen flex-col overflow-hidden px-4 py-4 md:px-6 md:py-5">
            {/* Subtle decorations */}
            <div
                className="pointer-events-none absolute -top-24 left-[-4rem] h-72 w-72 rounded-full blur-3xl"
                style={{ background: 'rgba(232,201,0,0.20)' }}
            />
            <div
                className="pointer-events-none absolute right-[-5rem] bottom-1/4 h-72 w-72 rounded-full blur-3xl"
                style={{ background: 'rgba(24,24,27,0.06)' }}
            />

            <div className="relative z-10 mx-auto flex w-full max-w-5xl flex-1 flex-col gap-3">
                {/* Compact top bar */}
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-[1.6rem] border border-white/70 bg-white/75 px-4 py-3 backdrop-blur-xl">
                    <div className="flex items-center gap-3">
                        <div
                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-md"
                            style={{ background: YELLOW }}
                        >
                            <Camera className="h-5 w-5 text-black" strokeWidth={2.5} />
                        </div>
                        <div>
                            <p className="text-[10px] font-bold tracking-[0.22em] text-zinc-400 uppercase">
                                Sesi Foto
                            </p>
                            <p className="text-base font-extrabold text-zinc-950">
                                Foto {currentOrder}{' '}
                                <span className="text-zinc-400">dari {photoCount}</span>
                            </p>
                        </div>
                    </div>

                    {/* Progress dots */}
                    <div className="flex items-center gap-1.5">
                        {Array.from({ length: photoCount }).map((_, index) => {
                            const done = photos.some((p) => p.order === index + 1);
                            const current = index + 1 === currentOrder;
                            return (
                                <div
                                    key={index}
                                    className="h-2 rounded-full transition-all duration-300"
                                    style={{
                                        width: current ? 28 : 10,
                                        background: done
                                            ? '#22c55e'
                                            : current
                                              ? YELLOW
                                              : '#e4e4e7',
                                    }}
                                />
                            );
                        })}
                    </div>

                    {/* Frame chip */}
                    {template && (
                        <div className="flex items-center gap-2 rounded-full bg-zinc-950 px-3 py-1.5 text-xs font-semibold text-white">
                            <ImageIcon className="h-3.5 w-3.5" style={{ color: YELLOW }} />
                            <span className="max-w-[140px] truncate">{template.name}</span>
                        </div>
                    )}
                </div>

                {/* Camera viewport */}
                <div
                    ref={cameraWrapRef}
                    className="relative flex-1 overflow-hidden rounded-[1.8rem] bg-zinc-950 shadow-2xl"
                    style={{ minHeight: 420 }}
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
                            display: currentPreview ? 'none' : 'block',
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

                    {/* Top gradient + overlays */}
                    <div className="pointer-events-none absolute inset-x-0 top-0 z-20 h-20 bg-gradient-to-b from-black/45 to-transparent" />

                    {/* Top-left: LIVE badge */}
                    {captureState === 'ready' && (
                        <div
                            className="absolute top-4 left-4 z-30 flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-black tracking-[0.2em] text-black uppercase shadow-lg"
                            style={{ background: YELLOW }}
                        >
                            <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-black" />
                            Live
                        </div>
                    )}

                    {/* Top-right: filter toggle + fullscreen */}
                    <div className="absolute top-4 right-4 z-40 flex items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setFiltersVisible((v) => !v)}
                            className="flex h-10 items-center gap-1.5 rounded-full bg-black/55 px-3.5 text-xs font-bold text-white backdrop-blur-md transition hover:bg-black/75 active:scale-95"
                            title={filtersVisible ? 'Sembunyikan Filter' : 'Tampilkan Filter'}
                        >
                            {filtersVisible ? (
                                <EyeOff className="h-4 w-4" />
                            ) : (
                                <Eye className="h-4 w-4" />
                            )}
                            <span className="hidden sm:inline">Filter</span>
                        </button>
                        <button
                            type="button"
                            onClick={toggleFullscreen}
                            className="flex h-10 w-10 items-center justify-center rounded-full bg-black/55 text-white backdrop-blur-md transition hover:bg-black/75 active:scale-95"
                            title={isFullscreen ? 'Keluar Fullscreen' : 'Fullscreen'}
                        >
                            {isFullscreen ? (
                                <Minimize className="h-4 w-4" />
                            ) : (
                                <Expand className="h-4 w-4" />
                            )}
                        </button>
                    </div>

                    {/* Active filter chip */}
                    {captureState === 'ready' && selectedFilter !== 'none' && (
                        <div className="absolute bottom-32 left-4 z-30 flex items-center gap-2 rounded-full bg-black/55 px-3 py-1.5 text-xs font-bold text-white backdrop-blur-md sm:bottom-28">
                            <Sparkles className="h-3 w-3" style={{ color: YELLOW }} />
                            {FILTERS[selectedFilter].label}
                        </div>
                    )}

                    {/* Flash */}
                    {captureState === 'flash' && (
                        <div
                            className="absolute inset-0 z-30 animate-pulse bg-white"
                            style={{ opacity: 0.92 }}
                        />
                    )}

                    {/* Uploading */}
                    {captureState === 'uploading' && (
                        <div className="absolute inset-0 z-30 flex flex-col items-center justify-center gap-3 bg-black/45 backdrop-blur-sm">
                            <div className="h-10 w-10 animate-spin rounded-full border-4 border-white/30 border-t-white" />
                            <p className="text-base font-semibold text-white">
                                Menyimpan foto...
                            </p>
                        </div>
                    )}

                    {/* Countdown */}
                    {captureState === 'countdown' && (
                        <div className="absolute inset-0 z-30 flex items-center justify-center bg-black/45 backdrop-blur-[2px]">
                            <div className="relative flex items-center justify-center">
                                <div
                                    className="absolute animate-ping rounded-full"
                                    style={{
                                        width: 220,
                                        height: 220,
                                        background: 'rgba(232,201,0,0.16)',
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

                    {/* Review */}
                    {captureState === 'review' && reviewPhoto && (
                        <div className="absolute inset-x-0 bottom-0 z-30 px-5 pt-12 pb-5">
                            <div className="rounded-[1.4rem] border border-white/15 bg-black/55 p-4 backdrop-blur-md">
                                <div className="flex items-end justify-between gap-3">
                                    <div>
                                        <p className="text-[10px] tracking-[0.3em] text-white/60 uppercase">
                                            Hasil Cepat
                                        </p>
                                        <p className="mt-1 text-xl font-bold text-white">
                                            Foto {reviewPhoto.order} tersimpan
                                        </p>
                                        <p className="text-sm text-white/70">
                                            Ulangi atau lanjut otomatis dalam{' '}
                                            {reviewSeconds}s
                                        </p>
                                    </div>
                                    <div
                                        className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-xl font-black text-black shadow-lg"
                                        style={{ background: YELLOW }}
                                    >
                                        {reviewSeconds}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Filter strip overlay */}
                    {captureState === 'ready' && filtersVisible && (
                        <div
                            className={`absolute inset-x-0 z-[25] ${isFullscreen ? 'bottom-24' : 'bottom-3'}`}
                        >
                            <div
                                className="flex gap-3 overflow-x-auto px-4 py-3"
                                style={{
                                    scrollbarWidth: 'none',
                                    background:
                                        'linear-gradient(to top, rgba(0,0,0,0.55) 20%, transparent 100%)',
                                }}
                            >
                                {Object.entries(FILTERS).map(([key, filter]) => {
                                    const isSelected = selectedFilter === key;
                                    return (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() => setSelectedFilter(key as FilterKey)}
                                            className="flex shrink-0 flex-col items-center gap-1 transition-all duration-150 active:scale-90"
                                        >
                                            <div
                                                className="relative h-12 w-12 overflow-hidden rounded-full transition-all duration-200"
                                                style={{
                                                    background: filter.preview,
                                                    boxShadow: isSelected
                                                        ? `0 0 0 2.5px ${YELLOW}, 0 0 0 5px rgba(232,201,0,0.35)`
                                                        : '0 0 0 1.5px rgba(255,255,255,0.25)',
                                                    transform: isSelected ? 'scale(1.12)' : 'scale(1)',
                                                }}
                                            >
                                                <canvas
                                                    ref={(el) => {
                                                        filterThumbRefs.current[key] = el;
                                                    }}
                                                    width={96}
                                                    height={96}
                                                    className="h-full w-full"
                                                    style={{
                                                        opacity: videoReady ? 1 : 0,
                                                        transition: 'opacity 0.2s',
                                                    }}
                                                />
                                            </div>
                                            <p
                                                className="text-[9px] font-bold drop-shadow-sm transition-colors duration-150"
                                                style={{
                                                    color: isSelected
                                                        ? YELLOW
                                                        : 'rgba(255,255,255,0.85)',
                                                }}
                                            >
                                                {filter.label}
                                            </p>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Fullscreen action button (overlay) */}
                    {isFullscreen && captureState !== 'review' && (
                        <div className="absolute inset-x-0 bottom-4 z-40 flex justify-center px-5">
                            <button
                                type="button"
                                onClick={startCountdown}
                                disabled={captureState !== 'ready' || !!error}
                                className="flex min-h-16 min-w-[210px] items-center justify-center gap-3 rounded-full px-8 text-lg font-black text-black shadow-2xl transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                style={{
                                    background:
                                        captureState === 'countdown'
                                            ? 'rgba(232,201,0,0.75)'
                                            : YELLOW,
                                    boxShadow:
                                        captureState === 'ready' && !error
                                            ? '0 8px 36px rgba(232,201,0,0.52)'
                                            : 'none',
                                }}
                            >
                                <Camera className="h-6 w-6" strokeWidth={2.5} />
                                {buttonLabel}
                            </button>
                        </div>
                    )}

                    {isFullscreen && captureState === 'review' && reviewPhoto && (
                        <div className="absolute inset-x-0 bottom-4 z-40 flex gap-3 px-5">
                            <button
                                type="button"
                                onClick={retakeCurrentPhoto}
                                className="flex min-h-14 flex-1 items-center justify-center gap-2 rounded-full border border-white/20 bg-black/55 px-5 text-sm font-bold text-white backdrop-blur-md active:scale-95"
                            >
                                <RefreshCw className="h-5 w-5" />
                                Ulangi
                            </button>
                            <button
                                type="button"
                                onClick={goToNextPhoto}
                                className="flex min-h-14 flex-[1.5] items-center justify-center gap-2 rounded-full px-5 text-sm font-black text-black shadow-2xl active:scale-95"
                                style={{ background: YELLOW }}
                            >
                                <CheckCircle className="h-5 w-5" />
                                {reviewPhoto.order >= photoCount ? 'Selesai' : 'Berikutnya'}
                            </button>
                        </div>
                    )}

                    {/* Error */}
                    {error && (
                        <div className="absolute inset-0 z-30 flex items-center justify-center bg-zinc-950/85">
                            <div className="mx-8 rounded-2xl bg-white p-6 text-center shadow-xl">
                                <p className="font-semibold text-red-500">{error}</p>
                            </div>
                        </div>
                    )}
                </div>

                {/* Action button below camera */}
                {captureState === 'review' && reviewPhoto ? (
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-[auto_1fr]">
                        <button
                            onClick={retakeCurrentPhoto}
                            className="flex items-center justify-center gap-2 rounded-[1.3rem] border border-zinc-200 bg-white/85 px-6 py-4 text-base font-bold text-zinc-700 backdrop-blur-sm transition hover:bg-white active:scale-95"
                        >
                            <RefreshCw className="h-5 w-5" />
                            Ulangi
                        </button>
                        <button
                            onClick={goToNextPhoto}
                            className="flex items-center justify-center gap-2 rounded-[1.3rem] py-4 text-lg font-black text-black transition-all duration-200 active:scale-[0.98]"
                            style={{
                                background: YELLOW,
                                boxShadow: '0 6px 28px rgba(232,201,0,0.40)',
                            }}
                        >
                            <CheckCircle className="h-6 w-6" strokeWidth={2.5} />
                            {reviewPhoto.order >= photoCount
                                ? 'Selesai & Lihat Hasil'
                                : 'Foto Berikutnya'}
                        </button>
                    </div>
                ) : (
                    <button
                        onClick={startCountdown}
                        disabled={captureState !== 'ready' || !!error}
                        className="flex w-full cursor-pointer items-center justify-center gap-3 rounded-[1.3rem] py-5 text-lg font-black text-black transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                        style={{
                            background:
                                captureState === 'countdown'
                                    ? 'rgba(232,201,0,0.65)'
                                    : YELLOW,
                            boxShadow:
                                captureState === 'ready' && !error
                                    ? '0 8px 36px rgba(232,201,0,0.45)'
                                    : 'none',
                        }}
                    >
                        <Camera className="h-6 w-6" strokeWidth={2.5} />
                        {buttonLabel}
                    </button>
                )}
            </div>

            <canvas ref={canvasRef} className="hidden" />
        </div>
    );
}
