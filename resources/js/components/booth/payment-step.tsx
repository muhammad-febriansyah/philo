import { CheckCircle, Clock, Loader2, QrCode } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { api } from '@/lib/api';

interface Transaction {
    transaction_id: number;
    order_id: string;
    amount: number;
    qr_url: string;
    expired_at: string;
}

interface Props {
    transaction: Transaction;
    onPaid: () => void;
    onExpired: () => void;
}

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price);
}

function calcSeconds(expiredAt: string) {
    return Math.max(
        0,
        Math.floor((new Date(expiredAt).getTime() - Date.now()) / 1000),
    );
}

function useCountdown(expiredAt: string) {
    const [seconds, setSeconds] = useState(() => calcSeconds(expiredAt));

    useEffect(() => {
        const update = () => setSeconds(calcSeconds(expiredAt));
        const id = setInterval(update, 1000);
        return () => clearInterval(id);
    }, [expiredAt]);

    const m = String(Math.floor(seconds / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    return { display: `${m}:${s}`, expired: seconds === 0 };
}

export default function PaymentStep({ transaction, onPaid, onExpired }: Props) {
    const [paid, setPaid] = useState(false);
    const [simulating, setSimulating] = useState(false);
    const { display, expired } = useCountdown(transaction.expired_at);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        if (paid || expired) return;

        intervalRef.current = setInterval(async () => {
            try {
                const data = await api.get<{ paid: boolean }>(
                    `/booth/payment/${transaction.transaction_id}/status`,
                );
                if (data.paid) {
                    clearInterval(intervalRef.current!);
                    setPaid(true);
                    setTimeout(onPaid, 1500);
                }
            } catch {
                /* ignore */
            }
        }, 3000);

        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
    }, [paid, expired, transaction.transaction_id, onPaid]);

    useEffect(() => {
        if (expired && !paid) onExpired();
    }, [expired, paid, onExpired]);

    const handleSimulate = async () => {
        setSimulating(true);
        try {
            await api.post(
                `/booth/payment/${transaction.transaction_id}/simulate`,
                {},
            );
            setPaid(true);
            if (intervalRef.current) clearInterval(intervalRef.current);
            setTimeout(onPaid, 1500);
        } catch {
            setSimulating(false);
        }
    };

    return (
        <div className="flex h-full flex-col items-center justify-center gap-6 px-8">
            <div className="text-center">
                <h2 className="text-3xl font-bold text-white">Scan QRIS</h2>
                <p className="mt-1 text-zinc-500">
                    Bayar dengan e-wallet atau mobile banking
                </p>
            </div>

            {/* QR card */}
            <div
                className="relative rounded-3xl bg-white p-6 shadow-2xl"
                style={{ boxShadow: '0 0 60px rgba(245,250,12,0.12)' }}
            >
                {paid && (
                    <div className="absolute inset-0 flex items-center justify-center rounded-3xl bg-white/95">
                        <div className="flex flex-col items-center gap-2">
                            <CheckCircle className="h-16 w-16 text-green-500" />
                            <p className="text-lg font-bold text-green-600">
                                Pembayaran Berhasil!
                            </p>
                        </div>
                    </div>
                )}
                <img
                    src={transaction.qr_url}
                    alt="QRIS"
                    className="h-64 w-64 object-contain"
                    onError={(e) => {
                        (e.target as HTMLImageElement).style.display = 'none';
                    }}
                />
                {!paid && (
                    <div className="mt-2 flex items-center justify-center gap-1.5 text-xs text-zinc-400">
                        <QrCode className="h-3.5 w-3.5" />
                        <span>Scan menggunakan aplikasi e-wallet</span>
                    </div>
                )}
            </div>

            {/* Stats row */}
            <div className="flex items-center gap-8 rounded-2xl border border-white/10 bg-white/5 px-8 py-4">
                <div className="text-center">
                    <p className="text-xs text-zinc-500">Total</p>
                    <p className="text-xl font-bold text-white">
                        {formatPrice(transaction.amount)}
                    </p>
                </div>
                <div className="h-8 w-px bg-white/10" />
                <div className="text-center">
                    <p className="text-xs text-zinc-500">Berlaku</p>
                    <p
                        className={`flex items-center gap-1 text-xl font-bold tabular-nums ${expired ? 'text-red-400' : 'text-white'}`}
                    >
                        <Clock className="h-4 w-4" />
                        {display}
                    </p>
                </div>
                <div className="h-8 w-px bg-white/10" />
                <div className="text-center">
                    <p className="text-xs text-zinc-500">Status</p>
                    <div className="flex items-center gap-1.5">
                        {paid ? (
                            <CheckCircle className="h-4 w-4 text-green-400" />
                        ) : (
                            <Loader2
                                className="h-4 w-4 animate-spin"
                                style={{ color: '#F5FA0C' }}
                            />
                        )}
                        <span
                            className={`text-sm font-semibold ${paid ? 'text-green-400' : 'text-white'}`}
                        >
                            {paid ? 'Dibayar' : 'Menunggu...'}
                        </span>
                    </div>
                </div>
            </div>

            <p className="text-xs text-zinc-700">ID: {transaction.order_id}</p>

            {/* Simulate button */}
            {!paid && (
                <button
                    onClick={handleSimulate}
                    disabled={simulating || expired}
                    className="rounded-2xl border border-dashed border-white/10 bg-white/5 px-8 py-3 text-sm font-medium text-zinc-500 transition hover:border-white/20 hover:text-white disabled:cursor-not-allowed disabled:opacity-40"
                >
                    {simulating ? (
                        <span className="flex items-center gap-2">
                            <Loader2 className="h-4 w-4 animate-spin" />{' '}
                            Memproses...
                        </span>
                    ) : (
                        '⚡ Simulasi Pembayaran (Testing)'
                    )}
                </button>
            )}
        </div>
    );
}
