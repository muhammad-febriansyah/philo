import { Head } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import CaptureStep from '@/components/booth/capture-step';
import CompleteStep from '@/components/booth/complete-step';
import FrameStep from '@/components/booth/frame-step';
import LandingStep from '@/components/booth/landing-step';
import PackageStep from '@/components/booth/package-step';
import PaymentStep from '@/components/booth/payment-step';
import PreviewStep from '@/components/booth/preview-step';
import { api } from '@/lib/api';

interface Branch {
    id: number;
    name: string;
    code: string;
    photo: string | null;
}

interface Package {
    id: number;
    name: string;
    description: string | null;
    photo_count: number;
    print_size: string;
    price: number;
    template_ids: number[];
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

interface TransactionData {
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

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

interface CompleteResponse {
    success: boolean;
    final_image_url: string | null;
    download_qr_svg: string | null;
}

interface GatewayResumeState {
    transaction: TransactionData;
    selectedPackage: Package;
    branch_code: string;
    created_at: number;
}

type Step =
    | 'landing'
    | 'packages'
    | 'payment'
    | 'frame'
    | 'capturing'
    | 'preview'
    | 'complete';

interface Props {
    branch: Branch;
    packages: Package[];
    templates: Template[];
    settings: {
        site_name: string | null;
        logo_path: string | null;
        booth_countdown_seconds: number;
        booth_idle_timeout_seconds: number;
        payment_provider: 'doku' | 'duitku';
        duitku_payment_method: string;
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
            window.addEventListener(eventName, handleActivity, {
                passive: true,
            }),
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
    packages,
    templates,
    settings,
}: Props) {
    const [step, setStep] = useState<Step>('landing');
    const [loadingAction, setLoadingAction] = useState(false);
    const [selectedPackage, setSelectedPackage] = useState<Package | null>(
        null,
    );
    const [transaction, setTransaction] = useState<TransactionData | null>(
        null,
    );
    const [sessionId, setSessionId] = useState<number | null>(null);
    const [photoCount, setPhotoCount] = useState(0);
    const [capturedPhotos, setCapturedPhotos] = useState<CapturedPhoto[]>([]);
    const [selectedTemplate, setSelectedTemplate] = useState<Template | null>(
        null,
    );
    const [finalImage, setFinalImage] = useState<string | null>(null);
    const [downloadUrl, setDownloadUrl] = useState<string | null>(null);
    const [downloadQrSvg, setDownloadQrSvg] = useState<string | null>(null);
    const [duitkuPaymentMethod, setDuitkuPaymentMethod] = useState(
        settings.duitku_payment_method || 'GQ',
    );

    const siteName = settings.site_name ?? 'Philo Photobooth';
    const logoUrl = settings.logo_path
        ? `/storage/${settings.logo_path}`
        : null;
    const countdownSeconds = settings.booth_countdown_seconds || 5;
    const idleTimeoutSeconds = settings.booth_idle_timeout_seconds || 60;

    const matchingTemplates = useMemo(() => {
        if (!selectedPackage) {
            return [];
        }

        return templates.filter((template) => {
            const assignedByAdmin = selectedPackage.template_ids.includes(
                template.id,
            );
            const isCompatibleWithPackage =
                template.print_size.toLowerCase() ===
                    selectedPackage.print_size.toLowerCase() &&
                template.photo_slots === selectedPackage.photo_count;

            if (selectedPackage.template_ids.length > 0) {
                return assignedByAdmin && isCompatibleWithPackage;
            }

            return isCompatibleWithPackage;
        });
    }, [selectedPackage, templates]);

    const reset = useCallback(() => {
        sessionStorage.removeItem(GATEWAY_RESUME_KEY);
        setStep('landing');
        setLoadingAction(false);
        setSelectedPackage(null);
        setTransaction(null);
        setSessionId(null);
        setPhotoCount(0);
        setFinalImage(null);
        setDownloadUrl(null);
        setDownloadQrSvg(null);
        setCapturedPhotos([]);
        setSelectedTemplate(null);
    }, []);

    useEffect(() => {
        const raw = sessionStorage.getItem(GATEWAY_RESUME_KEY);

        if (!raw) {
            return;
        }

        try {
            const parsed = JSON.parse(raw) as GatewayResumeState;

            if (!parsed?.transaction || !parsed?.selectedPackage || !parsed?.branch_code || !parsed?.created_at) {
                sessionStorage.removeItem(GATEWAY_RESUME_KEY);
                return;
            }

            const isExpired = Date.now() - parsed.created_at > GATEWAY_RESUME_TTL_MS;

            if (isExpired) {
                sessionStorage.removeItem(GATEWAY_RESUME_KEY);
                return;
            }

            if (parsed.branch_code !== branch.code) {
                sessionStorage.removeItem(GATEWAY_RESUME_KEY);
                return;
            }

            const params = new URLSearchParams(window.location.search);
            const txFromUrl = params.get('tx');

            if (txFromUrl && Number(txFromUrl) !== parsed.transaction.transaction_id) {
                return;
            }

            setSelectedPackage(parsed.selectedPackage);
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

    useIdleReset(
        ['landing', 'packages', 'frame'].includes(step),
        idleTimeoutSeconds,
        reset,
    );

    const handlePackageSelect = useCallback(
        async (pkg: Package) => {
            setLoadingAction(true);
            setSelectedPackage(pkg);

            try {
                const data = await api.post<TransactionData>(
                    '/booth/session/start',
                    {
                        branch_id: branch.id,
                        package_id: pkg.id,
                        payment_method_code:
                            settings.payment_provider === 'duitku'
                                ? duitkuPaymentMethod
                                : undefined,
                    },
                );

                if (data.payment_provider === 'duitku' && data.payment_url) {
                    const resumeState: GatewayResumeState = {
                        transaction: data,
                        selectedPackage: pkg,
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
                setSelectedPackage(null);
            } finally {
                setLoadingAction(false);
            }
        },
        [
            branch.code,
            branch.id,
            duitkuPaymentMethod,
            settings.payment_provider,
        ],
    );

    const handlePaymentPaid = useCallback(async () => {
        if (!transaction) {
            return;
        }

        try {
            const data = await api.post<{
                session_id: number;
                photo_count: number;
            }>('/booth/session/create', {
                transaction_id: transaction.transaction_id,
            });

            sessionStorage.removeItem(GATEWAY_RESUME_KEY);

            setSessionId(data.session_id);
            setPhotoCount(data.photo_count);

            if (matchingTemplates.length === 1) {
                const template = matchingTemplates[0];
                setSelectedTemplate(template);

                try {
                    await api.post('/booth/session/template', {
                        session_id: data.session_id,
                        template_id: template.id,
                    });
                } catch {
                    // Keep local selection even if persisting template fails.
                }

                setStep('capturing');

                return;
            }

            setStep('frame');
        } catch {
            reset();
        }
    }, [matchingTemplates, reset, transaction]);

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
                // Continue to capture even if save template fails.
            } finally {
                setLoadingAction(false);
            }

            setStep('capturing');
        },
        [sessionId],
    );

    const handleFrameSkip = useCallback(() => {
        setSelectedTemplate(null);
        setStep('capturing');
    }, []);

    const handleCaptureComplete = useCallback((photos: CapturedPhoto[]) => {
        setCapturedPhotos(photos);
        setStep('preview');
    }, []);

    const handlePrint = useCallback(
        async (image: string, email?: string) => {
            if (!sessionId) {
                return;
            }

            setLoadingAction(true);
            setFinalImage(image);

            try {
                const response = await api.post<CompleteResponse>(
                    '/booth/session/complete',
                    {
                        session_id: sessionId,
                        final_image_data: image,
                        customer_email: email ?? null,
                    },
                );

                setDownloadUrl(response.final_image_url);
                setDownloadQrSvg(response.download_qr_svg);
            } catch {
                setDownloadUrl(null);
                setDownloadQrSvg(null);
            } finally {
                setLoadingAction(false);
                setStep('complete');
            }
        },
        [sessionId],
    );

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
                className="fixed inset-0 overflow-x-hidden overflow-y-auto"
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
                            onStart={() => setStep('packages')}
                        />
                    )}

                    {step === 'packages' && (
                        <PackageStep
                            packages={packages}
                            templates={templates}
                            paymentProvider={settings.payment_provider}
                            duitkuPaymentMethod={duitkuPaymentMethod}
                            onDuitkuPaymentMethodChange={
                                setDuitkuPaymentMethod
                            }
                            onSelect={handlePackageSelect}
                            onBack={() => setStep('landing')}
                            loading={loadingAction}
                        />
                    )}

                    {step === 'payment' && transaction && (
                        <PaymentStep
                            transaction={transaction}
                            onPaid={handlePaymentPaid}
                            onExpired={reset}
                        />
                    )}

                    {step === 'frame' && (
                        <FrameStep
                            templates={matchingTemplates}
                            selectedPackage={selectedPackage}
                            onSelect={handleFrameSelect}
                            onSkip={handleFrameSkip}
                            onBack={() => setStep('packages')}
                            loading={loadingAction}
                        />
                    )}

                    {step === 'capturing' && sessionId && (
                        <CaptureStep
                            sessionId={sessionId}
                            photoCount={photoCount}
                            countdownSeconds={countdownSeconds}
                            template={selectedTemplate}
                            onComplete={handleCaptureComplete}
                        />
                    )}

                    {step === 'preview' && (
                        <PreviewStep
                            photos={capturedPhotos}
                            template={selectedTemplate}
                            onPrint={handlePrint}
                            loading={loadingAction}
                        />
                    )}

                    {step === 'complete' && (
                        <CompleteStep
                            onReset={reset}
                            finalImage={finalImage}
                            downloadUrl={downloadUrl}
                            downloadQrSvg={downloadQrSvg}
                        />
                    )}
                </div>
            </div>
        </>
    );
}
