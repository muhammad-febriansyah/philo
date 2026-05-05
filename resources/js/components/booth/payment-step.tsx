import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    ExternalLink,
    Loader2,
    ShieldCheck,
    Smartphone,
    TicketPercent,
    UserCheck,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { api } from '@/lib/api';

interface Transaction {
    transaction_id: number;
    order_id: string;
    amount: number;
    qr_url: string | null;
    expired_at: string;
    payment_provider?: 'doku' | 'duitku' | 'manual';
    payment_method_code?: string | null;
    gateway_reference?: string | null;
    payment_url?: string | null;
    is_simulation?: boolean;
    manual_qris_image_url?: string | null;
}

interface AppliedVoucherInfo {
    code: string;
    discount_amount: number;
    original_amount: number;
}

interface Props {
    branchId: number;
    extraPrints: number;
    transaction: Transaction;
    manualQrisImageUrl?: string | null;
    onVoucherApplied: (newTransaction: Transaction) => void;
    onPaid: () => void;
    onExpired: () => void;
    onReopenGateway?: (url?: string) => void;
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

export default function PaymentStep({
    branchId,
    extraPrints,
    transaction,
    manualQrisImageUrl,
    onVoucherApplied,
    onPaid,
    onExpired,
    onReopenGateway,
}: Props) {
    const [paid, setPaid] = useState(false);
    const [simulating, setSimulating] = useState(false);
    const [confirming, setConfirming] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [voucherOpen, setVoucherOpen] = useState(false);
    const [voucherInput, setVoucherInput] = useState('');
    const [voucherLoading, setVoucherLoading] = useState(false);
    const [voucherError, setVoucherError] = useState<string | null>(null);
    const [appliedVoucher, setAppliedVoucher] = useState<AppliedVoucherInfo | null>(null);
    const [retrying, setRetrying] = useState(false);
    const [retryError, setRetryError] = useState<string | null>(null);
    const { display, seconds, expired } = useCountdown(transaction.expired_at);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const isManual = transaction.payment_provider === 'manual';
    const allowRetry = Boolean(onReopenGateway); // Duitku popup mode

    useEffect(() => {
        if (paid || expired || isManual) return;
        intervalRef.current = setInterval(async () => {
            try {
                const data = await api.get<{ paid: boolean }>(
                    `/booth/payment/${transaction.transaction_id}/status`,
                );
                if (data.paid) {
                    clearInterval(intervalRef.current!);
                    setPaid(true);
                    setTimeout(onPaid, 1800);
                }
            } catch {
                /* ignore */
            }
        }, 3000);
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
    }, [paid, expired, isManual, transaction.transaction_id, onPaid]);

    useEffect(() => {
        // Duitku popup mode keeps the user on this step so they can retry
        // until payment succeeds — don't auto-reset to landing on expiry.
        if (expired && !paid && !allowRetry) onExpired();
    }, [expired, paid, onExpired, allowRetry]);

    const handleRetryPayment = useCallback(async () => {
        if (retrying) return;
        setRetrying(true);
        setRetryError(null);
        try {
            const fresh = await api.post<Transaction>('/booth/session/reissue', {
                transaction_id: transaction.transaction_id,
                voucher_code: appliedVoucher?.code ?? null,
            });
            onVoucherApplied(fresh);
            if (onReopenGateway && fresh.payment_url) {
                onReopenGateway(fresh.payment_url);
            }
        } catch (e) {
            setRetryError(
                e instanceof Error
                    ? e.message
                    : 'Gagal memuat ulang pembayaran.',
            );
        } finally {
            setRetrying(false);
        }
    }, [
        appliedVoucher,
        onReopenGateway,
        onVoucherApplied,
        retrying,
        transaction.transaction_id,
    ]);

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

    const handleConfirmManual = async () => {
        setConfirming(true);
        try {
            await api.post(`/booth/payment/${transaction.transaction_id}/confirm-manual`, {});
            setPaid(true);
            setTimeout(onPaid, 1800);
        } catch {
            setConfirming(false);
        }
    };

    const handleApplyVoucher = async () => {
        const code = voucherInput.trim().toUpperCase();
        if (!code) {
            setVoucherError('Masukkan kode voucher.');
            return;
        }

        setVoucherLoading(true);
        setVoucherError(null);

        try {
            const validation = await api.post<{
                valid: boolean;
                message: string;
                discount_amount?: number;
                original_amount?: number;
            }>('/booth/voucher/validate', {
                code,
                branch_id: branchId,
                extra_prints: extraPrints,
            });

            if (!validation.valid) {
                setVoucherError(validation.message || 'Voucher tidak dapat digunakan.');
                return;
            }

            // Re-issue the transaction with the voucher applied (cancels current QR).
            const newTransaction = await api.post<Transaction>(
                '/booth/session/reissue',
                {
                    transaction_id: transaction.transaction_id,
                    voucher_code: code,
                },
            );

            setAppliedVoucher({
                code,
                discount_amount: validation.discount_amount ?? 0,
                original_amount: validation.original_amount ?? transaction.amount,
            });
            setVoucherOpen(false);
            setVoucherInput('');
            onVoucherApplied(newTransaction);
        } catch (e) {
            setVoucherError(
                e instanceof Error ? e.message : 'Gagal menerapkan voucher.',
            );
        } finally {
            setVoucherLoading(false);
        }
    };

    const handleRemoveVoucher = async () => {
        setVoucherLoading(true);
        try {
            const newTransaction = await api.post<Transaction>(
                '/booth/session/reissue',
                {
                    transaction_id: transaction.transaction_id,
                    voucher_code: null,
                },
            );
            setAppliedVoucher(null);
            setVoucherInput('');
            setVoucherError(null);
            onVoucherApplied(newTransaction);
        } catch (e) {
            setVoucherError(
                e instanceof Error ? e.message : 'Gagal menghapus voucher.',
            );
        } finally {
            setVoucherLoading(false);
        }
    };

    const isUrgent = seconds <= 60 && !expired;
    const timerColor = expired ? '#ef4444' : isUrgent ? '#f97316' : '#18181b';
    const hasQr = Boolean(transaction.qr_url);

    return (
        <div
            className="flex min-h-screen items-center justify-center px-6 py-10"
            style={{ background: '#FAFAF5' }}
        >
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
                        {isManual ? 'Scan QRIS Manual' : hasQr ? 'Scan QRIS' : 'Selesaikan Pembayaran'}
                    </h2>
                    <p className="mt-1 text-sm text-zinc-500">
                        {isManual
                            ? 'Scan kode QR berikut lalu kasir konfirmasi'
                            : hasQr
                              ? 'Bayar dengan e-wallet atau mobile banking'
                              : 'Lanjutkan pembayaran di halaman gateway'}
                    </p>
                </div>

                {/* QR Card */}
                <div
                    className="relative w-full overflow-hidden rounded-3xl bg-white"
                    style={{ boxShadow: '0 8px 40px rgba(232,201,0,0.18)' }}
                >
                    <div className="absolute top-0 left-0 h-10 w-10 rounded-br-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute top-0 right-0 h-10 w-10 rounded-bl-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute bottom-0 left-0 h-10 w-10 rounded-tr-2xl" style={{ background: YELLOW, opacity: 0.18 }} />
                    <div className="absolute right-0 bottom-0 h-10 w-10 rounded-tl-2xl" style={{ background: YELLOW, opacity: 0.18 }} />

                    <div className="flex flex-col items-center px-8 py-7">
                        <div className="relative flex min-h-64 w-full items-center justify-center rounded-2xl bg-white p-3">
                            {voucherLoading && (
                                <div className="absolute inset-0 z-20 flex flex-col items-center justify-center gap-2 rounded-2xl bg-white/95 backdrop-blur">
                                    <Loader2 className="h-8 w-8 animate-spin text-yellow-600" />
                                    <p className="text-sm font-bold text-zinc-700">Menerbitkan ulang QR...</p>
                                </div>
                            )}
                            {isManual ? (
                                manualQrisImageUrl ? (
                                    <img
                                        src={manualQrisImageUrl}
                                        alt="QRIS Manual"
                                        className="h-56 w-56 object-contain"
                                    />
                                ) : (
                                    <div className="flex flex-col items-center gap-2 text-center text-zinc-400">
                                        <p className="text-sm font-semibold">Gambar QRIS belum diupload.</p>
                                        <p className="text-xs">Hubungi admin untuk mengatur gambar QRIS.</p>
                                    </div>
                                )
                            ) : hasQr ? (
                                <img
                                    src={transaction.qr_url ?? ''}
                                    alt="QRIS"
                                    className="h-56 w-56 object-contain"
                                    onError={(e) => {
                                        (e.target as HTMLImageElement).style.display = 'none';
                                    }}
                                />
                            ) : onReopenGateway ? (
                                <button
                                    type="button"
                                    onClick={onReopenGateway}
                                    className="flex items-center gap-2 rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-bold tracking-wider text-white uppercase active:scale-95"
                                >
                                    <ExternalLink className="h-4 w-4" />
                                    Buka Halaman Pembayaran
                                </button>
                            ) : (
                                <a
                                    href={transaction.payment_url ?? '#'}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-bold tracking-wider text-white uppercase"
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

                            {!paid && expired && allowRetry && (
                                <div className="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-2xl bg-white/95 px-4 text-center">
                                    <AlertTriangle
                                        className="h-12 w-12 text-orange-500"
                                        strokeWidth={1.6}
                                    />
                                    <p className="text-sm font-bold text-zinc-900">
                                        Waktu pembayaran habis
                                    </p>
                                    <p className="text-[11px] text-zinc-500">
                                        Klik &quot;Coba Lagi&quot; untuk
                                        memperbarui kode bayar.
                                    </p>
                                </div>
                            )}
                        </div>

                        <p className="mt-3 text-xs text-zinc-400">
                            {isManual
                                ? 'Scan menggunakan aplikasi e-wallet atau mobile banking'
                                : hasQr
                                  ? 'Scan menggunakan aplikasi e-wallet'
                                  : 'Klik tombol untuk lanjutkan pembayaran'}
                        </p>
                    </div>
                </div>

                {/* Reopen Duitku popup — available while waiting */}
                {onReopenGateway && !paid && !expired && hasQr && (
                    <button
                        type="button"
                        onClick={() => onReopenGateway()}
                        className="flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-white/80 px-4 py-3 text-sm font-bold text-zinc-700 transition hover:border-zinc-400 hover:bg-white active:scale-[0.99]"
                    >
                        <ExternalLink className="h-4 w-4" />
                        Buka Halaman Pembayaran
                    </button>
                )}

                {/* Retry on expiry — Duitku popup mode */}
                {allowRetry && expired && !paid && (
                    <div className="w-full space-y-2">
                        <button
                            type="button"
                            onClick={handleRetryPayment}
                            disabled={retrying}
                            className="flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-bold tracking-wider text-white uppercase transition active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {retrying ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <ExternalLink className="h-4 w-4" />
                            )}
                            {retrying ? 'Memuat ulang…' : 'Coba Lagi Pembayaran'}
                        </button>
                        <button
                            type="button"
                            onClick={onExpired}
                            disabled={retrying}
                            className="flex w-full items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-white/80 px-4 py-2.5 text-xs font-semibold text-zinc-500 transition hover:bg-white active:scale-[0.99] disabled:opacity-50"
                        >
                            Batalkan Pembayaran
                        </button>
                        {retryError && (
                            <p className="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">
                                {retryError}
                            </p>
                        )}
                    </div>
                )}

                {/* Voucher action — below QR card */}
                {!paid && (
                    <div className="w-full">
                        {!appliedVoucher && !voucherOpen && (
                            <button
                                type="button"
                                onClick={() => setVoucherOpen(true)}
                                disabled={voucherLoading}
                                className="flex w-full items-center justify-center gap-2 rounded-2xl border border-dashed border-zinc-300 bg-white/70 px-4 py-3 text-sm font-bold text-zinc-700 transition hover:border-yellow-400 hover:bg-yellow-50/50 active:scale-[0.99] disabled:opacity-50"
                            >
                                <TicketPercent className="h-4 w-4 text-yellow-600" />
                                Punya kode voucher?
                            </button>
                        )}

                        {voucherOpen && !appliedVoucher && (
                            <div className="space-y-2 rounded-2xl border border-yellow-200 bg-yellow-50/60 p-3">
                                <div className="flex items-center justify-between">
                                    <p className="flex items-center gap-1.5 text-xs font-bold tracking-wider text-yellow-800 uppercase">
                                        <TicketPercent className="h-3.5 w-3.5" />
                                        Kode Voucher
                                    </p>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setVoucherOpen(false);
                                            setVoucherError(null);
                                            setVoucherInput('');
                                        }}
                                        className="text-[11px] font-semibold text-zinc-500 hover:text-zinc-700"
                                    >
                                        Tutup
                                    </button>
                                </div>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        value={voucherInput}
                                        onChange={(e) =>
                                            setVoucherInput(e.target.value.toUpperCase())
                                        }
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                handleApplyVoucher();
                                            }
                                        }}
                                        placeholder="contoh: PHILO-AB12CD"
                                        className="flex-1 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-bold tracking-wider text-zinc-800 outline-none transition focus:border-yellow-400"
                                        disabled={voucherLoading}
                                        maxLength={64}
                                    />
                                    <button
                                        type="button"
                                        onClick={handleApplyVoucher}
                                        disabled={voucherLoading || !voucherInput.trim()}
                                        className="flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold text-black transition active:scale-[0.97] disabled:cursor-not-allowed disabled:opacity-50"
                                        style={{ background: YELLOW }}
                                    >
                                        {voucherLoading ? (
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                        ) : (
                                            'Terapkan'
                                        )}
                                    </button>
                                </div>
                                {voucherError && (
                                    <p className="text-[12px] font-medium text-red-600">
                                        {voucherError}
                                    </p>
                                )}
                            </div>
                        )}

                        {appliedVoucher && (
                            <div className="flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <div className="min-w-0">
                                    <p className="flex items-center gap-1.5 text-xs font-bold text-emerald-700">
                                        <CheckCircle2 className="h-3.5 w-3.5" />
                                        {appliedVoucher.code}
                                    </p>
                                    <p className="mt-0.5 truncate text-[11px] text-emerald-700/80">
                                        Hemat {formatPrice(appliedVoucher.discount_amount)}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={handleRemoveVoucher}
                                    disabled={voucherLoading}
                                    className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-50"
                                    aria-label="Hapus voucher"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>
                        )}
                    </div>
                )}

                {/* Info strip */}
                <div className="grid w-full grid-cols-3 rounded-2xl bg-white/70 backdrop-blur-sm">
                    <div className="flex flex-col items-center gap-1 px-4 py-4">
                        <p className="text-[10px] font-semibold tracking-widest text-zinc-400 uppercase">
                            Total
                        </p>
                        {appliedVoucher && (
                            <p className="text-[10px] text-zinc-400 line-through">
                                {formatPrice(appliedVoucher.original_amount)}
                            </p>
                        )}
                        <p className="text-base font-black text-zinc-900">
                            {formatPrice(transaction.amount)}
                        </p>
                    </div>
                    <div className="flex flex-col items-center gap-1 border-x border-white/40 px-4 py-4">
                        <p className="text-[10px] font-semibold tracking-widest text-zinc-400 uppercase">
                            Berlaku
                        </p>
                        <p
                            className="flex items-center gap-1 text-lg font-black tabular-nums transition-colors duration-500"
                            style={{ color: timerColor }}
                        >
                            <Clock className="h-4 w-4" />
                            {display}
                        </p>
                    </div>
                    <div className="flex flex-col items-center gap-1 px-4 py-4">
                        <p className="text-[10px] font-semibold tracking-widest text-zinc-400 uppercase">
                            Status
                        </p>
                        <div className="flex items-center gap-1.5">
                            {paid ? (
                                <CheckCircle2 className="h-4 w-4 text-green-500" />
                            ) : (
                                <Loader2
                                    className={`h-4 w-4 ${isManual ? 'text-zinc-300' : 'animate-spin text-zinc-400'}`}
                                />
                            )}
                            <span
                                className={`text-sm font-bold ${paid ? 'text-green-500' : 'text-zinc-700'}`}
                            >
                                {paid ? 'Lunas' : isManual ? 'Manual' : 'Menunggu'}
                            </span>
                        </div>
                    </div>
                </div>

                <p className="text-[11px] tracking-widest text-zinc-400">
                    ID: {transaction.order_id}
                </p>

                {/* Manual QRIS confirm button */}
                {isManual && !paid && (
                    <button
                        onClick={() => setShowConfirmDialog(true)}
                        disabled={confirming || expired}
                        className="flex w-full items-center justify-center gap-3 rounded-2xl py-4 text-lg font-black text-black transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40"
                        style={{
                            background: YELLOW,
                            boxShadow: '0 6px 28px rgba(232,201,0,0.45)',
                        }}
                    >
                        {confirming ? (
                            <>
                                <Loader2 className="h-5 w-5 animate-spin" />
                                Memproses...
                            </>
                        ) : (
                            <>
                                <UserCheck className="h-6 w-6" strokeWidth={2.5} />
                                Konfirmasi Sudah Bayar
                            </>
                        )}
                    </button>
                )}

                {isManual && !paid && (
                    <p className="text-center text-xs text-zinc-400">
                        Tombol ini ditekan oleh kasir setelah memverifikasi bukti pembayaran pelanggan.
                    </p>
                )}

                {/* Confirmation dialog */}
                {showConfirmDialog && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center px-6">
                        <div
                            className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                            onClick={() => setShowConfirmDialog(false)}
                        />
                        <div className="relative w-full max-w-sm overflow-hidden rounded-3xl bg-white shadow-2xl">
                            <div className="flex items-start justify-between px-6 pt-6 pb-4">
                                <div
                                    className="flex h-12 w-12 items-center justify-center rounded-2xl"
                                    style={{ background: 'rgba(232,201,0,0.15)' }}
                                >
                                    <AlertTriangle
                                        className="h-6 w-6"
                                        style={{ color: YELLOW }}
                                        strokeWidth={2.5}
                                    />
                                </div>
                                <button
                                    onClick={() => setShowConfirmDialog(false)}
                                    className="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 transition hover:bg-zinc-200"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>

                            <div className="px-6 pb-2">
                                <h3 className="text-xl font-black text-zinc-900">
                                    Konfirmasi Pembayaran
                                </h3>
                                <p className="mt-2 text-sm leading-relaxed text-zinc-500">
                                    Pastikan pelanggan sudah melakukan pembayaran sebesar{' '}
                                    <span className="font-bold text-zinc-800">
                                        {formatPrice(transaction.amount)}
                                    </span>{' '}
                                    dan bukti transfer sudah diverifikasi.
                                </p>

                                <div className="mt-4 rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                                    <p className="text-[11px] font-semibold tracking-widest text-zinc-400 uppercase">
                                        Order ID
                                    </p>
                                    <p className="mt-0.5 font-mono text-sm font-bold text-zinc-800">
                                        {transaction.order_id}
                                    </p>
                                </div>
                            </div>

                            <div className="flex gap-2.5 p-6">
                                <button
                                    onClick={() => setShowConfirmDialog(false)}
                                    className="flex-1 rounded-2xl border border-zinc-200 bg-white py-3.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-50 active:scale-95"
                                >
                                    Batal
                                </button>
                                <button
                                    onClick={() => {
                                        setShowConfirmDialog(false);
                                        handleConfirmManual();
                                    }}
                                    className="flex flex-1 items-center justify-center gap-2 rounded-2xl py-3.5 text-sm font-black text-black transition active:scale-95"
                                    style={{
                                        background: YELLOW,
                                        boxShadow: '0 4px 18px rgba(232,201,0,0.4)',
                                    }}
                                >
                                    <UserCheck className="h-4 w-4" strokeWidth={2.5} />
                                    Ya, Sudah Bayar
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Trust badge */}
                <div className="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 backdrop-blur-sm">
                    <ShieldCheck className="h-4 w-4 text-green-500" />
                    <p className="text-xs text-zinc-500">Transaksi aman &amp; terenkripsi</p>
                </div>

                {/* Simulate button — visible only during local dev. Stripped
                    out of production bundles by Vite dead-code elimination. */}
                {import.meta.env.DEV && !paid && !isManual && (
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
