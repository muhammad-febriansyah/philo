import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import CaptureStep from '@/components/booth/capture-step';
import CompleteStep from '@/components/booth/complete-step';
import FrameStep from '@/components/booth/frame-step';
import LandingStep from '@/components/booth/landing-step';
import PaymentStep from '@/components/booth/payment-step';
import PhotoEditorStep from '@/components/booth/photo-editor-step';
import { api } from '@/lib/api';

interface Branch {
    id: number;
    name: string;
    code: string;
    photo: string | null;
}

interface Template {
    id: number;
    name: string;
    thumbnail_path: string | null;
    frame_path: string | null;
    photo_slots: number;
    slot_positions: Array<{
        x: number;
        y: number;
        width: number;
        height: number;
    }> | null;
    print_size: string;
}

export interface AppliedVoucher {
    id: number;
    code: string;
    name: string | null;
    type: 'percentage' | 'fixed';
    value: number;
    discount_amount: number;
    final_amount: number;
}

export interface TransactionData {
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

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

interface GatewayResumeState {
    transaction: TransactionData;
    extraPrints: number;
    branch_code: string;
    created_at: number;
}

type Step = 'landing' | 'payment' | 'frame' | 'capturing' | 'complete' | 'editing';

interface Props {
    branch: Branch;
    templates: Template[];
    galleryImages: string[];
    settings: {
        site_name: string | null;
        logo_path: string | null;
        booth_countdown_seconds: number;
        booth_idle_timeout_seconds: number;
        booth_base_price: number;
        booth_extra_print_price: number;
        booth_max_extra_prints: number;
        payment_provider: 'doku' | 'duitku' | 'manual';
        duitku_payment_method: string;
        manual_qris_image_url: string | null;
        print_enabled: boolean;
        print_auto_print: boolean;
        print_default_size: string;
    };
}

const GATEWAY_RESUME_KEY = 'booth_gateway_resume_v1';
const GATEWAY_RESUME_TTL_MS = 30 * 60 * 1000;

function useIdleReset(
    enabled: boolean,
    timeoutSeconds: number,
    onTimeout: () => void,
) {
    useEffect(() => {
        if (!enabled) {
            return;
        }

        let timeoutId: ReturnType<typeof setTimeout> | null = null;

        const schedule = () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            timeoutId = setTimeout(onTimeout, timeoutSeconds * 1000);
        };

        const handleActivity = () => schedule();

        schedule();

        const events: Array<keyof WindowEventMap> = [
            'pointerdown',
            'pointermove',
            'keydown',
            'touchstart',
        ];

        events.forEach((eventName) =>
            window.addEventListener(eventName, handleActivity, { passive: true }),
        );

        return () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            events.forEach((eventName) =>
                window.removeEventListener(eventName, handleActivity),
            );
        };
    }, [enabled, timeoutSeconds, onTimeout]);
}

export default function BoothShow({
    branch,
    templates,
    galleryImages,
    settings,
}: Props) {
    const [step, setStep] = useState<Step>('landing');
    const [loadingAction, setLoadingAction] = useState(false);
    const [extraPrints, setExtraPrints] = useState(0);
    const [transaction, setTransaction] = useState<TransactionData | null>(null);
    const [selectedTemplate, setSelectedTemplate] = useState<Template | null>(null);
    const [sessionId, setSessionId] = useState<number | null>(null);
    const [capturedPhotos, setCapturedPhotos] = useState<CapturedPhoto[]>([]);
    const [editedImage, setEditedImage] = useState<string | null>(null);
    const [duitkuPaymentMethod] = useState(settings.duitku_payment_method || 'GQ');

    const siteName = settings.site_name ?? 'Philo Photobooth';
    const logoUrl = settings.logo_path ? `/storage/${settings.logo_path}` : null;
    const countdownSeconds = settings.booth_countdown_seconds || 5;
    const idleTimeoutSeconds = settings.booth_idle_timeout_seconds || 60;

    const reset = useCallback(() => {
        sessionStorage.removeItem(GATEWAY_RESUME_KEY);
        setStep('landing');
        setLoadingAction(false);
        setExtraPrints(0);
        setTransaction(null);
        setSelectedTemplate(null);
        setSessionId(null);
        setCapturedPhotos([]);
        setEditedImage(null);
    }, []);

    // Resume after Duitku redirect
    useEffect(() => {
        const raw = sessionStorage.getItem(GATEWAY_RESUME_KEY);
        if (!raw) return;

        try {
            const parsed = JSON.parse(raw) as GatewayResumeState;

            if (
                !parsed?.transaction ||
                !parsed?.branch_code ||
                !parsed?.created_at
            ) {
                sessionStorage.removeItem(GATEWAY_RESUME_KEY);
                return;
            }

            const isExpired = Date.now() - parsed.created_at > GATEWAY_RESUME_TTL_MS;
            if (isExpired || parsed.branch_code !== branch.code) {
                sessionStorage.removeItem(GATEWAY_RESUME_KEY);
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const txFromUrl = params.get('tx');
            if (
                txFromUrl &&
                Number(txFromUrl) !== parsed.transaction.transaction_id
            ) {
                return;
            }

            setExtraPrints(parsed.extraPrints || 0);
            setTransaction(parsed.transaction);
            setStep('payment');

            if (params.get('gateway_return') === 'duitku') {
                params.delete('gateway_return');
                params.delete('tx');
                const search = params.toString();
                const cleanedUrl = `${window.location.pathname}${search ? `?${search}` : ''}`;
                window.history.replaceState({}, '', cleanedUrl);
            }
        } catch {
            sessionStorage.removeItem(GATEWAY_RESUME_KEY);
        }
    }, [branch.code]);

    useIdleReset(step === 'landing', idleTimeoutSeconds, reset);

    const handleStart = useCallback(
        async (extras: number) => {
            setLoadingAction(true);
            setExtraPrints(extras);

            try {
                const data = await api.post<TransactionData>(
                    '/booth/session/start',
                    {
                        branch_id: branch.id,
                        extra_prints: extras,
                        payment_method_code:
                            settings.payment_provider === 'duitku'
                                ? duitkuPaymentMethod
                                : undefined,
                    },
                );

                if (data.payment_provider === 'duitku' && data.payment_url) {
                    const resumeState: GatewayResumeState = {
                        transaction: data,
                        extraPrints: extras,
                        branch_code: branch.code,
                        created_at: Date.now(),
                    };
                    sessionStorage.setItem(
                        GATEWAY_RESUME_KEY,
                        JSON.stringify(resumeState),
                    );
                    window.location.href = data.payment_url;
                    return;
                }

                setTransaction(data);
                setStep('payment');
            } catch {
                // stay on landing on error
            } finally {
                setLoadingAction(false);
            }
        },
        [branch.code, branch.id, duitkuPaymentMethod, settings.payment_provider],
    );

    const handleVoucherApplied = useCallback((newTransaction: TransactionData) => {
        setTransaction(newTransaction);
    }, []);

    const handlePaymentPaid = useCallback(async () => {
        if (!transaction) return;

        try {
            const data = await api.post<{ session_id: number; photo_count: number }>(
                '/booth/session/create',
                {
                    transaction_id: transaction.transaction_id,
                },
            );
            sessionStorage.removeItem(GATEWAY_RESUME_KEY);
            setSessionId(data.session_id);
            setStep('frame');
        } catch {
            reset();
        }
    }, [reset, transaction]);

    const handleFrameSelect = useCallback(
        async (template: Template) => {
            if (!sessionId) {
                return;
            }
            setLoadingAction(true);
            setSelectedTemplate(template);
            try {
                await api.post('/booth/session/template', {
                    session_id: sessionId,
                    template_id: template.id,
                });
            } catch {
                // continue even if persistence fails — local state still has it
            }
            setLoadingAction(false);
            setStep('capturing');
        },
        [sessionId],
    );

    const handleCaptureComplete = useCallback((photos: CapturedPhoto[]) => {
        setCapturedPhotos(photos);
        setEditedImage(null);
        setStep('complete');
    }, []);

    const handleEdit = useCallback((image: string) => {
        setEditedImage(image);
        setStep('editing');
    }, []);

    const handleEditDone = useCallback((edited: string) => {
        setEditedImage(edited);
        setStep('complete');
    }, []);

    const totalPrintCopies = 1 + extraPrints;

    return (
        <>
            <Head title={`${branch.name} | ${siteName}`}>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link
                    rel="preconnect"
                    href="https://fonts.gstatic.com"
                    crossOrigin="anonymous"
                />
                <link
                    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
                    rel="stylesheet"
                />
            </Head>

            <div
                className={`fixed inset-0 overflow-x-hidden ${step === 'landing' ? 'overflow-hidden' : 'overflow-y-auto'}`}
                style={{
                    background:
                        'radial-gradient(circle at top left, rgba(232,201,0,0.18), transparent 32%), radial-gradient(circle at 85% 16%, rgba(15,23,42,0.12), transparent 24%), linear-gradient(180deg, #fafaf5 0%, #f7f3e8 100%)',
                    fontFamily: "'Poppins', sans-serif",
                }}
            >
                <div
                    className="pointer-events-none absolute inset-0 z-0 opacity-[0.04]"
                    style={{
                        backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                    }}
                />
                <div
                    className="pointer-events-none absolute top-[-10rem] right-[-6rem] z-0 h-80 w-80 rounded-full blur-3xl"
                    style={{ background: 'rgba(232,201,0,0.20)' }}
                />
                <div
                    className="pointer-events-none absolute bottom-[-8rem] left-[-5rem] z-0 h-72 w-72 rounded-full blur-3xl"
                    style={{ background: 'rgba(24,24,27,0.08)' }}
                />

                <div className="relative z-10 min-h-full w-full">
                    {step === 'landing' && (
                        <LandingStep
                            branchName={branch.name}
                            siteName={siteName}
                            logoUrl={logoUrl}
                            templates={templates}
                            galleryImages={galleryImages}
                            basePrice={settings.booth_base_price}
                            extraPrintPrice={settings.booth_extra_print_price}
                            maxExtraPrints={settings.booth_max_extra_prints}
                            starting={loadingAction}
                            onStart={handleStart}
                        />
                    )}

                    {step === 'payment' && transaction && (
                        <PaymentStep
                            branchId={branch.id}
                            extraPrints={extraPrints}
                            transaction={transaction}
                            manualQrisImageUrl={
                                transaction.manual_qris_image_url ??
                                settings.manual_qris_image_url ??
                                null
                            }
                            onVoucherApplied={handleVoucherApplied}
                            onPaid={handlePaymentPaid}
                            onExpired={reset}
                        />
                    )}

                    {step === 'frame' && (
                        <FrameStep
                            templates={templates}
                            extraPrints={extraPrints}
                            onSelect={handleFrameSelect}
                            loading={loadingAction}
                        />
                    )}

                    {step === 'capturing' && sessionId && selectedTemplate && (
                        <CaptureStep
                            sessionId={sessionId}
                            photoCount={selectedTemplate.photo_slots}
                            countdownSeconds={countdownSeconds}
                            template={selectedTemplate}
                            onComplete={handleCaptureComplete}
                        />
                    )}

                    {step === 'complete' && sessionId && (
                        <CompleteStep
                            sessionId={sessionId}
                            photos={capturedPhotos}
                            template={selectedTemplate}
                            initialImage={editedImage}
                            printEnabled={settings.print_enabled}
                            printAutoPrint={settings.print_auto_print}
                            printCopies={totalPrintCopies}
                            printPaperSize={
                                selectedTemplate?.print_size ??
                                settings.print_default_size
                            }
                            onEdit={handleEdit}
                            onReset={reset}
                        />
                    )}

                    {step === 'editing' && editedImage && (
                        <PhotoEditorStep
                            image={editedImage}
                            onBack={() => setStep('complete')}
                            onDone={handleEditDone}
                            loading={loadingAction}
                        />
                    )}
                </div>
            </div>
        </>
    );
}
