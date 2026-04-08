import { CheckCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Props {
    onReset: () => void;
}

export default function CompleteStep({ onReset }: Props) {
    const [seconds, setSeconds] = useState(8);

    useEffect(() => {
        const id = setInterval(() => {
            setSeconds((s) => {
                if (s <= 1) { clearInterval(id); onReset(); return 0; }
                return s - 1;
            });
        }, 1000);
        return () => clearInterval(id);
    }, [onReset]);

    return (
        <div className="flex h-full flex-col items-center justify-center gap-8 text-center">
            {/* Icon */}
            <div className="relative">
                <div
                    className="absolute inset-0 animate-ping rounded-full"
                    style={{ background: 'rgba(245,250,12,0.12)', animationDuration: '1.5s' }}
                />
                <div
                    className="relative flex h-32 w-32 items-center justify-center rounded-full"
                    style={{ background: 'rgba(245,250,12,0.12)', border: '2px solid rgba(245,250,12,0.3)' }}
                >
                    <CheckCircle className="h-16 w-16" style={{ color: '#F5FA0C' }} strokeWidth={1.5} />
                </div>
            </div>

            <div className="space-y-3">
                <h2 className="text-4xl font-bold text-white">Foto Sedang Dicetak!</h2>
                <p className="text-xl text-zinc-500">Silakan ambil foto Anda di printer</p>
            </div>

            {/* Printer dots */}
            <div className="flex flex-col items-center gap-3 rounded-3xl border border-white/10 bg-white/5 px-12 py-6">
                <div className="flex gap-1.5">
                    {[0, 1, 2, 3, 4].map((i) => (
                        <div
                            key={i}
                            className="h-2 w-2 animate-bounce rounded-full"
                            style={{ background: '#F5FA0C', animationDelay: `${i * 0.1}s` }}
                        />
                    ))}
                </div>
                <p className="text-sm text-zinc-600">Mencetak...</p>
            </div>

            {/* Countdown */}
            <div className="flex flex-col items-center gap-2">
                <p className="text-zinc-600">Kembali ke layar awal dalam</p>
                <div
                    className="flex h-16 w-16 items-center justify-center rounded-full"
                    style={{ border: '2px solid rgba(245,250,12,0.3)', background: 'rgba(245,250,12,0.08)' }}
                >
                    <span className="text-2xl font-bold" style={{ color: '#F5FA0C' }}>{seconds}</span>
                </div>
            </div>

            <button
                onClick={onReset}
                className="rounded-2xl border border-white/10 px-8 py-3 text-base text-zinc-500 transition hover:bg-white/5 active:scale-95"
            >
                Kembali Sekarang
            </button>
        </div>
    );
}
