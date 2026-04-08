import { Camera, CheckCircle, RefreshCw } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { api } from '@/lib/api';

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

interface Props {
    sessionId: number;
    photoCount: number;
    onComplete: (photos: CapturedPhoto[]) => void;
}

type CaptureState = 'ready' | 'countdown' | 'flash' | 'uploading';

export default function CaptureStep({ sessionId, photoCount, onComplete }: Props) {
    const videoRef = useRef<HTMLVideoElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const streamRef = useRef<MediaStream | null>(null);

    const [captureState, setCaptureState] = useState<CaptureState>('ready');
    const [countdown, setCountdown] = useState(3);
    const [currentOrder, setCurrentOrder] = useState(1);
    const [photos, setPhotos] = useState<CapturedPhoto[]>([]);
    const [lastCapture, setLastCapture] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let active = true;
        navigator.mediaDevices
            .getUserMedia({ video: { width: 1280, height: 720, facingMode: 'user' } })
            .then((stream) => {
                if (!active) { stream.getTracks().forEach((t) => t.stop()); return; }
                streamRef.current = stream;
                if (videoRef.current) videoRef.current.srcObject = stream;
            })
            .catch(() => setError('Kamera tidak dapat diakses. Pastikan izin kamera diberikan.'));

        return () => {
            active = false;
            streamRef.current?.getTracks().forEach((t) => t.stop());
        };
    }, []);

    const captureFrame = useCallback((): string | null => {
        const video = videoRef.current;
        const canvas = canvasRef.current;
        if (!video || !canvas || video.readyState < 2) return null;

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        if (!ctx) return null;

        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);
        return canvas.toDataURL('image/jpeg', 0.92);
    }, []);

    const startCountdown = useCallback(() => {
        if (captureState !== 'ready') return;
        setCaptureState('countdown');
        setCountdown(3);

        let count = 3;
        const id = setInterval(() => {
            count -= 1;
            setCountdown(count);
            if (count <= 0) {
                clearInterval(id);
                setCaptureState('flash');
                setTimeout(() => {
                    const dataUrl = captureFrame();
                    if (dataUrl) {
                        setLastCapture(dataUrl);
                        setCaptureState('uploading');
                        const order = currentOrder;

                        api.post<{ photo_id: number; url: string }>('/booth/photo/capture', {
                            session_id: sessionId,
                            photo_data: dataUrl,
                            order,
                        })
                            .then((res) => {
                                const newPhotos = [...photos, { id: res.photo_id, url: res.url, order }];
                                setPhotos(newPhotos);
                                if (order >= photoCount) {
                                    onComplete(newPhotos);
                                } else {
                                    setCurrentOrder(order + 1);
                                    setLastCapture(null);
                                    setCaptureState('ready');
                                }
                            })
                            .catch(() => {
                                setLastCapture(null);
                                setCaptureState('ready');
                            });
                    } else {
                        setCaptureState('ready');
                    }
                }, 200);
            }
        }, 1000);
    }, [captureState, captureFrame, currentOrder, photos, photoCount, sessionId, onComplete]);

    return (
        <div className="flex h-full flex-col px-6 py-6">
            {/* Header */}
            <div className="mb-4 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-white">Sesi Foto</h2>
                    <p className="text-zinc-500">
                        Foto{' '}
                        <span className="font-bold" style={{ color: '#F5FA0C' }}>{currentOrder}</span>
                        {' '}dari{' '}
                        <span className="font-bold text-white">{photoCount}</span>
                    </p>
                </div>
                <div className="flex gap-2">
                    {Array.from({ length: photoCount }).map((_, i) => (
                        <div
                            key={i}
                            className={`h-3 w-3 rounded-full transition-all ${i < photos.length ? 'bg-green-400' : i === photos.length ? 'scale-125' : 'bg-white/15'}`}
                            style={i === photos.length ? { background: '#F5FA0C' } : undefined}
                        />
                    ))}
                </div>
            </div>

            {/* Camera */}
            <div className="relative flex flex-1 items-center justify-center">
                <div className="relative overflow-hidden rounded-3xl shadow-2xl" style={{ aspectRatio: '16/9', maxHeight: '60vh', width: '100%' }}>
                    <video
                        ref={videoRef}
                        autoPlay
                        playsInline
                        muted
                        className="h-full w-full object-cover"
                        style={{ transform: 'scaleX(-1)', display: lastCapture ? 'none' : 'block' }}
                    />

                    {lastCapture && <img src={lastCapture} alt="Captured" className="h-full w-full object-cover" />}

                    {captureState === 'flash' && (
                        <div className="absolute inset-0 bg-white opacity-80" />
                    )}

                    {captureState === 'countdown' && (
                        <div className="absolute inset-0 flex items-center justify-center bg-black/40">
                            <div
                                className="flex h-40 w-40 items-center justify-center rounded-full shadow-2xl"
                                style={{ background: '#F5FA0C', boxShadow: '0 0 60px rgba(245,250,12,0.5)' }}
                            >
                                <span className="animate-bounce text-8xl font-black text-black">{countdown}</span>
                            </div>
                        </div>
                    )}

                    {captureState === 'uploading' && (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/60">
                            <div className="h-8 w-8 animate-spin rounded-full border-4 border-white/20 border-t-white" />
                            <p className="text-sm font-medium text-white">Menyimpan foto...</p>
                        </div>
                    )}

                    {captureState === 'ready' && !lastCapture && (
                        <>
                            <div className="absolute left-4 top-4 h-8 w-8 rounded-tl-xl border-l-2 border-t-2" style={{ borderColor: '#F5FA0C' }} />
                            <div className="absolute right-4 top-4 h-8 w-8 rounded-tr-xl border-r-2 border-t-2" style={{ borderColor: '#F5FA0C' }} />
                            <div className="absolute bottom-4 left-4 h-8 w-8 rounded-bl-xl border-b-2 border-l-2" style={{ borderColor: '#F5FA0C' }} />
                            <div className="absolute bottom-4 right-4 h-8 w-8 rounded-br-xl border-b-2 border-r-2" style={{ borderColor: '#F5FA0C' }} />
                        </>
                    )}
                </div>
            </div>

            {error && (
                <div className="mt-3 rounded-2xl bg-red-500/10 p-4 text-center text-sm text-red-400">{error}</div>
            )}

            <canvas ref={canvasRef} className="hidden" />

            {/* Thumbnails */}
            {photos.length > 0 && (
                <div className="mt-4 flex gap-2 overflow-x-auto pb-1">
                    {photos.map((p) => (
                        <div key={p.id} className="relative shrink-0">
                            <img src={p.url} alt={`Foto ${p.order}`} className="h-16 w-24 rounded-xl object-cover" />
                            <div className="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500">
                                <CheckCircle className="h-3.5 w-3.5 text-white" />
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Buttons */}
            <div className="mt-4 flex gap-3">
                {lastCapture && captureState !== 'uploading' && (
                    <button
                        onClick={() => { setLastCapture(null); setCaptureState('ready'); }}
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/15 py-4 text-lg font-semibold text-white transition hover:bg-white/5 active:scale-95"
                    >
                        <RefreshCw className="h-5 w-5" /> Ulangi
                    </button>
                )}

                {!lastCapture && (
                    <button
                        onClick={startCountdown}
                        disabled={captureState !== 'ready' || !!error}
                        className="flex flex-1 items-center justify-center gap-2 rounded-2xl py-4 text-lg font-bold text-black shadow-lg transition disabled:cursor-not-allowed disabled:opacity-40 active:scale-95"
                        style={{ background: '#F5FA0C', boxShadow: '0 0 30px rgba(245,250,12,0.25)' }}
                    >
                        <Camera className="h-6 w-6" />
                        {captureState === 'countdown' ? `Siap... ${countdown}` : 'Ambil Foto'}
                    </button>
                )}
            </div>
        </div>
    );
}
