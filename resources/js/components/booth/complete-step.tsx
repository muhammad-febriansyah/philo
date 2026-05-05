import {
    CheckCircle,
    Download,
    Loader2,
    Mail,
    PartyPopper,
    Printer,
    QrCode,
    RotateCcw,
    Send,
    Sparkles,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { composeFinalImage } from '@/components/booth/template-slot-utils';
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
    slot_positions: Array<{ x: number; y: number; width: number; height: number }> | null;
    print_size: string;
}

interface Props {
    sessionId: number;
    photos: CapturedPhoto[];
    template: Template | null;
    initialImage: string | null;
    printEnabled: boolean;
    printAutoPrint: boolean;
    printCopies: number;
    printPaperSize: string;
    onEdit: (image: string) => void;
    onReset: () => void;
}

interface CompleteResponse {
    success: boolean;
    final_image_url: string | null;
    download_qr_svg: string | null;
}

const YELLOW = '#E8C900';
const AUTO_RESET_SECONDS = 45;

const PAGE_SIZES: Record<string, string> = {
    strip: '2in 6in',
    '4R': '4in 6in',
    A4: 'A4',
    A3: 'A3',
};

function openPrintWindow(dataUrl: string, paperSize: string, copies: number) {
    const size = PAGE_SIZES[paperSize] ?? 'A4';
    const printCopies = Math.max(1, Math.floor(Number(copies) || 1));

    const win = window.open('', '_blank');
    if (!win) {
        return;
    }

    win.document.write(`<!DOCTYPE html>
<html>
<head>
<title>Cetak Foto</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  @page { size: ${size}; margin: 0; }
  html, body { width: 100%; height: 100%; background: #fff; }
  .page { display: flex; align-items: center; justify-content: center;
          width: 100%; height: 100vh; page-break-after: always; }
  img { max-width: 100%; max-height: 100%; object-fit: contain;
        -webkit-print-color-adjust: exact; print-color-adjust: exact; }
</style>
</head>
<body>
${Array.from({ length: printCopies }, () => `<div class="page"><img src="${dataUrl}" /></div>`).join('')}
<script>
  window.onload = function() {
    setTimeout(function() { window.print(); window.close(); }, 400);
  };
</script>
</body>
</html>`);
    win.document.close();
}

function CountdownRing({ seconds, total }: { seconds: number; total: number }) {
    const radius = 22;
    const circumference = 2 * Math.PI * radius;
    const progress = (seconds / total) * circumference;

    return (
        <div className="relative flex h-14 w-14 items-center justify-center">
            <svg className="absolute inset-0 -rotate-90" width="56" height="56">
                <circle
                    cx="28"
                    cy="28"
                    r={radius}
                    fill="none"
                    stroke="rgba(232,201,0,0.15)"
                    strokeWidth="4"
                />
                <circle
                    cx="28"
                    cy="28"
                    r={radius}
                    fill="none"
                    stroke={YELLOW}
                    strokeWidth="4"
                    strokeLinecap="round"
                    strokeDasharray={circumference}
                    strokeDashoffset={circumference - progress}
                    style={{ transition: 'stroke-dashoffset 1s linear' }}
                />
            </svg>
            <span className="text-base font-black text-zinc-950">{seconds}</span>
        </div>
    );
}

export default function CompleteStep({
    sessionId,
    photos,
    template,
    initialImage,
    printEnabled,
    printAutoPrint,
    printCopies,
    printPaperSize,
    onEdit,
    onReset,
}: Props) {
    const [composedImage, setComposedImage] = useState<string | null>(initialImage);
    const [downloadUrl, setDownloadUrl] = useState<string | null>(null);
    const [downloadQrSvg, setDownloadQrSvg] = useState<string | null>(null);
    const [phase, setPhase] = useState<'composing' | 'uploading' | 'ready' | 'error'>(
        initialImage ? 'uploading' : 'composing',
    );
    const [seconds, setSeconds] = useState(AUTO_RESET_SECONDS);
    const [printing, setPrinting] = useState(false);
    const [emailOpen, setEmailOpen] = useState(false);
    const [emailValue, setEmailValue] = useState('');
    const [emailSending, setEmailSending] = useState(false);
    const [emailFeedback, setEmailFeedback] = useState<{
        kind: 'success' | 'error';
        message: string;
    } | null>(null);
    const autoPrintFiredRef = useRef(false);
    const composeRequestedRef = useRef(false);
    const uploadedHashRef = useRef<string | null>(null);

    // Compose locally when no initialImage is provided.
    useEffect(() => {
        if (initialImage) {
            setComposedImage(initialImage);
            setPhase('uploading');
            return;
        }

        if (composeRequestedRef.current) {
            return;
        }

        composeRequestedRef.current = true;

        composeFinalImage(photos, template)
            .then((dataUrl) => {
                setComposedImage(dataUrl);
                setPhase('uploading');
            })
            .catch(() => setPhase('error'));
    }, [initialImage, photos, template]);

    // Upload composed image to server and get download URL + QR.
    useEffect(() => {
        if (!composedImage || phase !== 'uploading') {
            return;
        }

        const hash = `${composedImage.length}-${composedImage.slice(-32)}`;
        if (uploadedHashRef.current === hash) {
            return;
        }
        uploadedHashRef.current = hash;

        let cancelled = false;

        api.post<CompleteResponse>('/booth/session/complete', {
            session_id: sessionId,
            final_image_data: composedImage,
            customer_email: null,
        })
            .then((response) => {
                if (cancelled) return;
                setDownloadUrl(response.final_image_url);
                setDownloadQrSvg(response.download_qr_svg);
                setPhase('ready');
            })
            .catch(() => {
                if (cancelled) return;
                setPhase('error');
            });

        return () => {
            cancelled = true;
        };
    }, [composedImage, phase, sessionId]);

    const doPrint = useCallback(() => {
        if (!composedImage || !printEnabled) {
            return;
        }
        setPrinting(true);
        openPrintWindow(composedImage, printPaperSize, printCopies);
        setTimeout(() => setPrinting(false), 3000);
    }, [composedImage, printEnabled, printPaperSize, printCopies]);

    const handleDownload = useCallback(() => {
        const target = downloadUrl || composedImage;
        if (!target) return;
        const anchor = document.createElement('a');
        anchor.href = target;
        anchor.download = `philo-photobooth-${Date.now()}.jpg`;
        anchor.target = '_blank';
        anchor.rel = 'noopener noreferrer';
        anchor.click();
    }, [downloadUrl, composedImage]);

    const handleEditClick = useCallback(() => {
        if (!composedImage) return;
        onEdit(composedImage);
    }, [composedImage, onEdit]);

    const openEmailDialog = useCallback(() => {
        setEmailFeedback(null);
        setEmailOpen(true);
    }, []);

    const closeEmailDialog = useCallback(() => {
        if (emailSending) return;
        setEmailOpen(false);
    }, [emailSending]);

    const handleSendEmail = useCallback(
        async (event: React.FormEvent<HTMLFormElement>) => {
            event.preventDefault();
            const trimmed = emailValue.trim();
            if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                setEmailFeedback({
                    kind: 'error',
                    message: 'Alamat email tidak valid.',
                });
                return;
            }

            setEmailSending(true);
            setEmailFeedback(null);

            try {
                const response = await api.post<{ success: boolean; message: string }>(
                    '/booth/session/email',
                    { session_id: sessionId, email: trimmed },
                );
                setEmailFeedback({
                    kind: 'success',
                    message: response.message ?? 'Foto terkirim ke email kamu.',
                });
                setEmailValue('');
            } catch (error) {
                const message =
                    error instanceof Error && error.message
                        ? error.message
                        : 'Gagal kirim email. Coba lagi.';
                setEmailFeedback({ kind: 'error', message });
            } finally {
                setEmailSending(false);
            }
        },
        [emailValue, sessionId],
    );

    // Auto-print once when ready
    useEffect(() => {
        if (
            phase === 'ready' &&
            printEnabled &&
            printAutoPrint &&
            composedImage &&
            !autoPrintFiredRef.current
        ) {
            autoPrintFiredRef.current = true;
            const id = setTimeout(() => doPrint(), 800);
            return () => clearTimeout(id);
        }
    }, [phase, printEnabled, printAutoPrint, composedImage, doPrint]);

    // Auto-reset countdown — only when ready.
    useEffect(() => {
        if (phase !== 'ready') {
            setSeconds(AUTO_RESET_SECONDS);
            return;
        }

        const id = setInterval(() => {
            setSeconds((current) => {
                if (current <= 1) {
                    clearInterval(id);
                    onReset();
                    return 0;
                }
                return current - 1;
            });
        }, 1000);

        return () => clearInterval(id);
    }, [phase, onReset]);

    // Reset countdown on user activity.
    useEffect(() => {
        if (phase !== 'ready') return;
        const reset = () => setSeconds(AUTO_RESET_SECONDS);
        const events: Array<keyof WindowEventMap> = ['pointerdown', 'keydown'];
        events.forEach((e) => window.addEventListener(e, reset, { passive: true }));
        return () => events.forEach((e) => window.removeEventListener(e, reset));
    }, [phase]);

    const isLoading = phase === 'composing' || phase === 'uploading';

    const headlineText =
        phase === 'ready'
            ? 'Foto kamu siap!'
            : phase === 'error'
              ? 'Gagal menyimpan foto'
              : phase === 'composing'
                ? 'Menyusun foto...'
                : 'Mengunggah foto...';

    const subText =
        phase === 'ready'
            ? 'Scan QR untuk simpan ke HP, atau cetak langsung.'
            : phase === 'error'
              ? 'Coba ulangi sesi atau hubungi petugas.'
              : 'Tunggu sebentar, ya...';

    return (
        <div className="relative flex min-h-screen flex-col px-4 py-4 md:px-6 md:py-5">
            {/* Decorations */}
            <div
                className="pointer-events-none absolute top-[-6rem] left-1/2 z-0 h-96 w-96 -translate-x-1/2 rounded-full blur-3xl"
                style={{ background: 'rgba(232,201,0,0.16)' }}
            />
            <div
                className="pointer-events-none absolute right-[-4rem] bottom-[-4rem] z-0 h-60 w-60 rounded-full blur-3xl"
                style={{ background: 'rgba(24,24,27,0.06)' }}
            />

            <div className="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col gap-3">
                {/* Compact horizontal header */}
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-[1.5rem] border border-white/70 bg-white/80 px-4 py-3 backdrop-blur-xl">
                    <div className="flex items-center gap-3">
                        <div className="relative">
                            {phase === 'ready' && (
                                <div
                                    className="absolute inset-0 animate-ping rounded-full opacity-30"
                                    style={{ background: YELLOW, animationDuration: '2.4s' }}
                                />
                            )}
                            <div
                                className="relative flex h-11 w-11 items-center justify-center rounded-full shadow-md"
                                style={{ background: YELLOW }}
                            >
                                {phase === 'ready' ? (
                                    <CheckCircle className="h-5 w-5 text-black" strokeWidth={2.4} />
                                ) : phase === 'error' ? (
                                    <Sparkles className="h-5 w-5 text-black" strokeWidth={2.4} />
                                ) : (
                                    <Loader2 className="h-5 w-5 animate-spin text-black" strokeWidth={2.5} />
                                )}
                            </div>
                        </div>
                        <div>
                            <div className="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 px-2.5 py-0.5 text-[9px] font-semibold tracking-[0.22em] text-white uppercase">
                                <PartyPopper className="h-2.5 w-2.5" style={{ color: YELLOW }} />
                                {phase === 'ready'
                                    ? 'Sesi selesai'
                                    : phase === 'error'
                                      ? 'Ada kendala'
                                      : 'Hampir selesai'}
                            </div>
                            <h2 className="mt-0.5 text-xl leading-tight font-extrabold text-zinc-950 md:text-2xl">
                                {headlineText}
                            </h2>
                        </div>
                    </div>
                    <p className="hidden max-w-md text-right text-sm text-zinc-500 sm:block">
                        {subText}
                    </p>
                </div>

                {/* Main grid: image + sidebar */}
                <div className="grid flex-1 gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,340px)]">
                    {/* Image preview */}
                    <div className="relative flex items-center justify-center rounded-[1.75rem] border border-white/70 bg-white/80 p-3 shadow-sm backdrop-blur-xl md:p-4">
                        {composedImage ? (
                            <div className="relative flex items-center justify-center">
                                <img
                                    src={composedImage}
                                    alt="Foto hasil"
                                    className="block max-h-[calc(100vh-220px)] max-w-full rounded-[1.4rem] object-contain shadow-2xl ring-1 ring-black/10"
                                />
                                {phase === 'uploading' && (
                                    <div className="absolute top-2 right-2 flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-md">
                                        <Loader2 className="h-3 w-3 animate-spin" />
                                        Menyimpan
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center gap-4 py-8">
                                <div className="relative">
                                    <div
                                        className="absolute h-24 w-24 animate-ping rounded-full opacity-15"
                                        style={{ background: YELLOW }}
                                    />
                                    <div
                                        className="relative flex h-16 w-16 items-center justify-center rounded-full shadow-xl"
                                        style={{ background: YELLOW }}
                                    >
                                        <Loader2 className="h-8 w-8 animate-spin text-black" />
                                    </div>
                                </div>
                                <div className="text-center">
                                    <p className="font-bold text-zinc-900">
                                        Menggabungkan {photos.length} foto…
                                    </p>
                                    <p className="mt-1 text-sm text-zinc-500">
                                        Frame &amp; layout sedang disusun
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="flex flex-col gap-2.5">
                        {/* QR card */}
                        <div className="flex flex-col items-center gap-2.5 rounded-[1.5rem] border border-white/70 bg-white/85 p-3 shadow-sm backdrop-blur-xl">
                            <div className="flex w-full items-center gap-2">
                                <div
                                    className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                    style={{ background: YELLOW }}
                                >
                                    <QrCode className="h-4 w-4 text-black" />
                                </div>
                                <div className="min-w-0 text-left">
                                    <p className="text-sm font-bold text-zinc-900">
                                        Scan untuk simpan
                                    </p>
                                    <p className="truncate text-[11px] text-zinc-400">
                                        Buka kamera HP, arahkan ke QR
                                    </p>
                                </div>
                            </div>

                            {downloadQrSvg ? (
                                <div className="rounded-[1rem] border border-zinc-200 bg-white p-2 shadow-sm">
                                    <img
                                        src={downloadQrSvg}
                                        alt="QR download foto"
                                        className="h-40 w-40 rounded-md"
                                    />
                                </div>
                            ) : (
                                <div className="flex h-40 w-40 items-center justify-center rounded-[1rem] border border-dashed border-zinc-300 bg-zinc-50 text-xs font-semibold text-zinc-400">
                                    {isLoading ? (
                                        <span className="flex items-center gap-2">
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Menyiapkan
                                        </span>
                                    ) : (
                                        'QR belum tersedia'
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Print status */}
                        {printEnabled && (
                            <div className="flex items-center gap-2.5 rounded-[1.2rem] border border-white/70 bg-white/85 p-2.5 shadow-sm backdrop-blur-xl">
                                <div
                                    className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                                    style={{ background: 'rgba(232,201,0,0.16)' }}
                                >
                                    {printing ? (
                                        <Loader2
                                            className="h-4 w-4 animate-spin"
                                            style={{ color: YELLOW }}
                                        />
                                    ) : (
                                        <Printer className="h-4 w-4" style={{ color: '#7a6200' }} />
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-[13px] font-bold text-zinc-900">
                                        {printing ? 'Mencetak…' : 'Printer siap'}
                                    </p>
                                    <p className="truncate text-[10px] text-zinc-500">
                                        {printCopies}× · {printPaperSize.toUpperCase()}
                                    </p>
                                </div>
                            </div>
                        )}

                        {/* Action buttons */}
                        <div className="grid grid-cols-3 gap-2">
                            <button
                                onClick={handleDownload}
                                disabled={!downloadUrl && !composedImage}
                                className="flex items-center justify-center gap-1.5 rounded-[1rem] border border-zinc-200 bg-white px-2 py-2.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <Download className="h-3.5 w-3.5" />
                                Download
                            </button>
                            <button
                                onClick={openEmailDialog}
                                disabled={!downloadUrl || phase !== 'ready'}
                                className="flex items-center justify-center gap-1.5 rounded-[1rem] border border-zinc-200 bg-white px-2 py-2.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <Mail className="h-3.5 w-3.5" />
                                Email
                            </button>
                            <button
                                onClick={handleEditClick}
                                disabled={!composedImage || phase !== 'ready'}
                                className="flex items-center justify-center gap-1.5 rounded-[1rem] border border-zinc-200 bg-white px-2 py-2.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <Sparkles className="h-3.5 w-3.5" />
                                Edit Foto
                            </button>
                        </div>

                        {printEnabled && (
                            <button
                                onClick={doPrint}
                                disabled={!composedImage || printing}
                                className="flex items-center justify-center gap-2 rounded-[1.2rem] py-3 text-sm font-black text-black transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                                style={{
                                    background: YELLOW,
                                    boxShadow: '0 4px 20px rgba(232,201,0,0.40)',
                                }}
                            >
                                {printing ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    <Printer className="h-4 w-4" strokeWidth={2.5} />
                                )}
                                {printing ? 'Mencetak…' : 'Cetak Sekarang'}
                            </button>
                        )}
                    </div>
                </div>

                {/* Footer: auto-reset + manual reset */}
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-[1.5rem] border border-white/70 bg-white/80 px-4 py-2.5 backdrop-blur-md">
                    {phase === 'ready' ? (
                        <div className="flex items-center gap-3">
                            <CountdownRing seconds={seconds} total={AUTO_RESET_SECONDS} />
                            <div className="text-left">
                                <p className="text-[10px] font-semibold tracking-widest text-zinc-400 uppercase">
                                    Reset otomatis
                                </p>
                                <p className="text-sm font-bold text-zinc-900">
                                    {seconds} detik tersisa
                                </p>
                            </div>
                        </div>
                    ) : (
                        <p className="text-sm text-zinc-500">{subText}</p>
                    )}

                    <button
                        onClick={onReset}
                        className="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 active:scale-95"
                    >
                        <RotateCcw className="h-4 w-4" />
                        Selesai &amp; Mulai Ulang
                    </button>
                </div>
            </div>

            {emailOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center px-4"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="email-dialog-title"
                >
                    <button
                        type="button"
                        aria-label="Tutup"
                        onClick={closeEmailDialog}
                        className="absolute inset-0 cursor-default bg-zinc-950/55 backdrop-blur-sm"
                    />
                    <div className="relative w-full max-w-md overflow-hidden rounded-[1.6rem] border border-white/70 bg-white shadow-2xl">
                        <div className="flex items-start justify-between gap-3 border-b border-zinc-100 px-5 py-4">
                            <div className="flex items-center gap-3">
                                <div
                                    className="flex h-10 w-10 items-center justify-center rounded-xl"
                                    style={{ background: YELLOW }}
                                >
                                    <Mail className="h-5 w-5 text-black" strokeWidth={2.4} />
                                </div>
                                <div>
                                    <h3
                                        id="email-dialog-title"
                                        className="text-base font-extrabold text-zinc-950"
                                    >
                                        Kirim foto ke email
                                    </h3>
                                    <p className="mt-0.5 text-xs text-zinc-500">
                                        Kami kirim file foto + link unduh ke alamat
                                        email kamu.
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={closeEmailDialog}
                                disabled={emailSending}
                                className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 disabled:opacity-40"
                                aria-label="Tutup dialog"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        <form onSubmit={handleSendEmail} className="px-5 pt-4 pb-5">
                            <label
                                htmlFor="email-dialog-input"
                                className="text-[11px] font-bold tracking-widest text-zinc-500 uppercase"
                            >
                                Alamat email
                            </label>
                            <input
                                id="email-dialog-input"
                                type="email"
                                inputMode="email"
                                autoComplete="email"
                                autoFocus
                                required
                                value={emailValue}
                                onChange={(event) => {
                                    setEmailValue(event.target.value);
                                    if (emailFeedback) setEmailFeedback(null);
                                }}
                                placeholder="nama@email.com"
                                disabled={emailSending}
                                className="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-900 outline-none transition focus:border-zinc-900 focus:ring-4 focus:ring-zinc-900/10 disabled:opacity-50"
                            />

                            {emailFeedback && (
                                <p
                                    className={`mt-3 rounded-lg px-3 py-2 text-xs font-semibold ${
                                        emailFeedback.kind === 'success'
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-red-50 text-red-700'
                                    }`}
                                    role={
                                        emailFeedback.kind === 'error'
                                            ? 'alert'
                                            : 'status'
                                    }
                                >
                                    {emailFeedback.message}
                                </p>
                            )}

                            <div className="mt-5 flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={closeEmailDialog}
                                    disabled={emailSending}
                                    className="flex-1 rounded-xl border border-zinc-200 bg-white py-2.5 text-sm font-bold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Tutup
                                </button>
                                <button
                                    type="submit"
                                    disabled={emailSending || !downloadUrl}
                                    className="flex flex-1 items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-black text-black transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    style={{
                                        background: YELLOW,
                                        boxShadow: '0 4px 16px rgba(232,201,0,0.35)',
                                    }}
                                >
                                    {emailSending ? (
                                        <Loader2 className="h-4 w-4 animate-spin" />
                                    ) : (
                                        <Send className="h-4 w-4" strokeWidth={2.4} />
                                    )}
                                    {emailSending ? 'Mengirim…' : 'Kirim'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
