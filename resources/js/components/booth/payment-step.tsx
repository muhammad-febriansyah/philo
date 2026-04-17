import { CheckCircle2, Clock, Loader2, ShieldCheck, Smartphone } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { api } from '@/lib/api';

interface Transaction {
    transaction_id: number;
    order_id: string;
    amount: number;
    qr_url: string | null;
    expired_at: string;
    payment_provider?: 'doku' | 'duitku';
    payment_method_code?: string | null;
    gateway_reference?: string | null;
    payment_url?: string | null;
    is_simulation?: boolean;
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
    return Math.max(0, Math.floor((new Date(expiredAt).getTime() - Date.now()) / 1000));
}

function useCountdown(expiredAt: string) {
    const [seconds, setSeconds] = useState(() => calcSeconds(expiredAt));
    useEffect(() => {
        const id = setInterval(() => setSeconds(calcSeconds(expiredAt)), 1000);
        return () => clearInterval(id);
    }, [expiredAt]);
    const m = String(Math.floor(seconds / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    return { display: `${m}:${s}`, seconds, expired: seconds === 0 };
}

const YELLOW = '#E8C900';

export default function PaymentStep({ transaction, onPaid, onExpired }: Props) {
    const [paid, setPaid]             = useState(false);
    const [simulating, setSimulating] = useState(false);
    const { display, seconds, expired } = useCountdown(transaction.expired_at);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        if (paid || expired) return;
        intervalRef.current = setInterval(async () => {
            try {
                const data = await api.get<{ paid: boolean }>(`/booth/payment/${transaction.transaction_id}/status`);
                if (data.paid) {
                    clearInterval(intervalRef.current!);
                    setPaid(true);
                    setTimeout(onPaid, 1800);
                }
            } catch { /* ignore */ }
        }, 3000);
        return () => { if (intervalRef.current) clearInterval(intervalRef.current); };
    }, [paid, expired, transaction.transaction_id, onPaid]);

    useEffect(() => {
        if (expired && !paid) onExpired();
    }, [expired, paid, onExpired]);

    const handleSimulate = async () => {
        setSimulating(true);
        try {
            await api.post(`/booth/payment/${transaction.transaction_id}/simulate`, {});
            setPaid(true);
            if (intervalRef.current) clearInterval(intervalRef.current);
            setTimeout(onPaid, 1800);
        } catch {
            setSimulating(false);
        }
    };

    const isUrgent   = seconds <= 60 && !expired;
    const timerColor = expired ? '#ef4444' : isUrgent ? '#f97316' : '#18181b';
    const hasQr = Boolean(transaction.qr_url);

    return (
        <div className="flex min-h-screen items-center justify-center px-6 py-10" style={{ background: '#FAFAF5' }}>
            <div className="flex w-full max-w-sm flex-col items-center gap-6">

                {/* Header */}
                <div className="text-center">
                    <div
                        className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl"
                        style={{ background: YELLOW, boxShadow: '0 4px 20px rgba(232,201,0,0.35)' }}
                    >
                        <Smartphone className="h-6 w-6 text-black" />
                    </div>
                    <h2 className="text-3xl font-black tracking-tight text-zinc-900">
                        {hasQr ? 'Scan QRIS' : 'Selesaikan Pembayaran'}
                    </h2>
                    <p className="mt-1 text-sm text-zinc-500">
                        {hasQr
                            ? 'Bayar dengan e-wallet atau mobile banking'
                            : 'Lanjutkan pembayaran di halaman gateway'}
                    </p>
                </div>

                {/* QR Card */}
                <div
                    className="relative w-full overflow-hidden rounded-3xl bg-white"
                    style={{ boxShadow: '0 8px 40px rgba(232,201,0,0.18)' }}
                >
                    {/* Yellow corner accents */}
                    <div className="absolute left-0 top-0 h-10 w-10 rounded-br-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute right-0 top-0 h-10 w-10 rounded-bl-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute bottom-0 left-0 h-10 w-10 rounded-tr-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute bottom-0 right-0 h-10 w-10 rounded-tl-2xl" style={{ background: YELLOW, opacity: 0.18 }} />

                    <div className="flex flex-col items-center px-8 py-7">
                        <div className="relative flex min-h-64 w-full items-center justify-center rounded-2xl bg-white p-3">
                            {hasQr ? (
                                <img
                                    src={transaction.qr_url ?? ''}
                                    alt="QRIS"
                                    className="h-56 w-56 object-contain"
                                    onError={(e) => { (e.target as HTMLImageElement).style.display = 'none'; }}
                                />
                            ) : (
                                <a
                                    href={transaction.payment_url ?? '#'}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-bold uppercase tracking-wider text-white"
                                >
                                    Buka Halaman Gateway
                                </a>
                            )}
                            {paid && (
                                <div className="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-2xl bg-white/95">
                                    <CheckCircle2 className="h-16 w-16 text-green-500" strokeWidth={1.5} />
                                    <p className="text-base font-bold text-green-600">Pembayaran Berhasil!</p>
                                </div>
                            )}
                        </div>
                        <p className="mt-3 text-xs text-zinc-400">
                            {hasQr
                                ? 'Scan menggunakan aplikasi e-wallet'
                                : 'Klik tombol untuk lanjutkan pembayaran'}
                        </p>
                    </div>
                </div>

                {/* Info strip */}
                <div className="grid w-full grid-cols-3 rounded-2xl bg-white/70 backdrop-blur-sm">
                    <div className="flex flex-col items-center gap-1 px-4 py-4">
                        <p className="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Total</p>
                        <p className="text-base font-black text-zinc-900">{formatPrice(transaction.amount)}</p>
                    </div>
                    <div className="flex flex-col items-center gap-1 border-x border-white/40 px-4 py-4">
                        <p className="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Berlaku</p>
                        <p
                            className="flex items-center gap-1 text-lg font-black tabular-nums transition-colors duration-500"
                            style={{ color: timerColor }}
                        >
                            <Clock className="h-4 w-4" />
                            {display}
                        </p>
                    </div>
                    <div className="flex flex-col items-center gap-1 px-4 py-4">
                        <p className="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Status</p>
                        <div className="flex items-center gap-1.5">
                            {paid ? (
                                <CheckCircle2 className="h-4 w-4 text-green-500" />
                            ) : (
                                <Loader2 className="h-4 w-4 animate-spin text-zinc-400" />
                            )}
                            <span className={`text-sm font-bold ${paid ? 'text-green-500' : 'text-zinc-700'}`}>
                                {paid ? 'Lunas' : 'Menunggu'}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Order ID */}
                <p className="text-[11px] tracking-widest text-zinc-400">ID: {transaction.order_id}</p>
                <p className="text-[11px] uppercase tracking-widest text-zinc-400">
                    Gateway: {(transaction.payment_provider ?? 'doku').toUpperCase()}
                </p>
                {transaction.gateway_reference && (
                    <p className="text-[11px] tracking-widest text-zinc-400">REF: {transaction.gateway_reference}</p>
                )}
                {transaction.payment_method_code && (
                    <p className="text-[11px] tracking-widest text-zinc-400">
                        METHOD: {transaction.payment_method_code}
                    </p>
                )}
                {transaction.payment_provider === 'duitku' && transaction.payment_url && (
                    <a
                        href={transaction.payment_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-[11px] font-semibold uppercase tracking-widest text-blue-600 underline"
                    >
                        Buka Halaman Gateway
                    </a>
                )}
                {transaction.is_simulation && (
                    <p className="rounded-md bg-orange-100 px-2 py-1 text-[11px] font-semibold uppercase tracking-widest text-orange-700">
                        Mode Simulasi
                    </p>
                )}

                {/* Trust badge */}
                <div className="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 backdrop-blur-sm">
                    <ShieldCheck className="h-4 w-4 text-green-500" />
                    <p className="text-xs text-zinc-500">Transaksi aman &amp; terenkripsi</p>
                </div>

                {/* Simulate button */}
                {!paid && (
                    <button
                        onClick={handleSimulate}
                        disabled={simulating || expired}
                        className="w-full rounded-2xl border border-dashed border-white/50 bg-white/60 px-8 py-3 text-sm font-medium text-zinc-500 transition hover:bg-white/80 hover:text-zinc-700 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        {simulating ? (
                            <span className="flex items-center justify-center gap-2">
                                <Loader2 className="h-4 w-4 animate-spin" /> Memproses...
                            </span>
                        ) : (
                            '⚡ Simulasi Pembayaran (Testing)'
                        )}
                    </button>
                )}
            </div>
        </div>
    );
}
