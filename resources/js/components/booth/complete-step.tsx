import { CheckCircle, Download, Printer, QrCode } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

interface Props {
    onReset: () => void;
    finalImage: string | null;
    downloadUrl: string | null;
    downloadQrSvg: string | null;
}

function handleDownload(url: string, fallbackDataUrl: string | null) {
    const anchor = document.createElement('a');
    anchor.href = url || fallbackDataUrl || '#';
    anchor.download = `philo-photobooth-${Date.now()}.jpg`;
    anchor.target = '_blank';
    anchor.rel = 'noopener noreferrer';
    anchor.click();
}

function handlePrint(dataUrl: string) {
    const win = window.open('', '_blank');

    if (!win) {
        return;
    }

    win.document.write(`
        <html><head><title>Cetak Foto</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #fff; }
            img { max-width: 100%; max-height: 100vh; object-fit: contain; }
            @media print { body { margin: 0; } img { width: 100%; height: auto; } }
        </style></head>
        <body><img src="${dataUrl}" onload="window.print(); window.close();" /></body>
        </html>
    `);
    win.document.close();
}

export default function CompleteStep({
    onReset,
    finalImage,
    downloadUrl,
    downloadQrSvg,
}: Props) {
    const [seconds, setSeconds] = useState(30);

    const download = useCallback(() => {
        if (!downloadUrl && !finalImage) {
            return;
        }

        handleDownload(downloadUrl ?? '', finalImage);
    }, [downloadUrl, finalImage]);

    const print = useCallback(() => {
        if (!finalImage) {
            return;
        }

        handlePrint(finalImage);
    }, [finalImage]);

    useEffect(() => {
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
    }, [onReset]);

    return (
        <div className="flex h-full flex-col items-center justify-center gap-8 px-8 text-center">
            <div className="relative">
                <div
                    className="absolute inset-0 animate-ping rounded-full"
                    style={{
                        background: 'rgba(232,201,0,0.18)',
                        animationDuration: '1.5s',
                    }}
                />
                <div
                    className="relative flex h-32 w-32 items-center justify-center rounded-full shadow-lg"
                    style={{
                        background: '#E8C900',
                        border: '2px solid rgba(232,201,0,0.4)',
                    }}
                >
                    <CheckCircle
                        className="h-16 w-16 text-black"
                        strokeWidth={1.5}
                    />
                </div>
            </div>

            <div className="space-y-3">
                <h2 className="text-4xl font-bold text-zinc-900">Selesai!</h2>
                <p className="text-xl text-zinc-500">
                    Foto siap dicetak dan bisa langsung diunduh ke HP
                </p>
            </div>

            <div className="grid w-full max-w-4xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div className="flex flex-col items-center gap-4 rounded-[2rem] border border-zinc-200 bg-white px-8 py-8 shadow-sm">
                    <div className="flex items-center gap-2 rounded-full bg-zinc-100 px-4 py-2 text-sm font-semibold text-zinc-600">
                        <QrCode className="h-4 w-4" />
                        Download Digital
                    </div>

                    {downloadQrSvg ? (
                        <img
                            src={downloadQrSvg}
                            alt="QR download foto"
                            className="h-64 w-64 rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm"
                        />
                    ) : (
                        <div className="flex h-64 w-64 items-center justify-center rounded-3xl border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400">
                            QR belum tersedia
                        </div>
                    )}

                    <p className="max-w-sm text-sm text-zinc-500">
                        Scan QR ini untuk membuka file digital. Lebih praktis
                        daripada men-download langsung dari mesin booth.
                    </p>
                </div>

                <div className="flex flex-col items-center justify-between gap-5 rounded-[2rem] border border-zinc-200 bg-white px-8 py-8 shadow-sm">
                    <div className="space-y-3">
                        <div className="mx-auto flex w-fit gap-1.5 rounded-full bg-[rgba(232,201,0,0.10)] px-4 py-2">
                            {[0, 1, 2, 3, 4].map((index) => (
                                <div
                                    key={index}
                                    className="h-2 w-2 animate-bounce rounded-full"
                                    style={{
                                        background: '#E8C900',
                                        animationDelay: `${index * 0.1}s`,
                                    }}
                                />
                            ))}
                        </div>
                        <h3 className="text-2xl font-bold text-zinc-900">
                            Printer Sedang Bekerja
                        </h3>
                        <p className="text-zinc-500">
                            Silakan ambil hasil cetak setelah keluar dari
                            printer booth.
                        </p>
                    </div>

                    <div className="flex flex-col items-center gap-2">
                        <p className="text-zinc-400">Auto reset dalam</p>
                        <div
                            className="flex h-16 w-16 items-center justify-center rounded-full border-2"
                            style={{
                                borderColor: 'rgba(232,201,0,0.4)',
                                background: 'rgba(232,201,0,0.10)',
                            }}
                        >
                            <span className="text-2xl font-bold text-zinc-900">
                                {seconds}
                            </span>
                        </div>
                    </div>

                    <div className="flex w-full gap-3">
                        <button
                            onClick={download}
                            disabled={!downloadUrl && !finalImage}
                            className="flex flex-1 items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-white px-6 py-3 text-base font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <Download className="h-5 w-5" />
                            Download
                        </button>
                        <button
                            onClick={print}
                            disabled={!finalImage}
                            className="flex flex-1 items-center justify-center gap-2 rounded-2xl px-6 py-3 text-base font-semibold text-black shadow-lg transition disabled:cursor-not-allowed disabled:opacity-40"
                            style={{
                                background: '#E8C900',
                                boxShadow: '0 4px 16px rgba(232,201,0,0.30)',
                            }}
                        >
                            <Printer className="h-5 w-5" />
                            Cetak Ulang
                        </button>
                    </div>
                </div>
            </div>

            <button
                onClick={onReset}
                className="rounded-2xl border border-zinc-200 bg-white px-8 py-3 text-base text-zinc-500 shadow-sm transition hover:bg-zinc-50 active:scale-95"
            >
                Kembali Sekarang
            </button>
        </div>
    );
}
