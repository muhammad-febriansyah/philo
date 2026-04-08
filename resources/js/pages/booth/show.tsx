import { useCallback, useState } from 'react';
import CaptureStep from '@/components/booth/capture-step';
import CompleteStep from '@/components/booth/complete-step';
import FrameStep from '@/components/booth/frame-step';
import LandingStep from '@/components/booth/landing-step';
import PackageStep from '@/components/booth/package-step';
import PaymentStep from '@/components/booth/payment-step';
import PreviewStep from '@/components/booth/preview-step';
import { api } from '@/lib/api';

// ─── Types ───────────────────────────────────────────────────────────────────

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
}

interface Template {
    id: number;
    name: string;
    thumbnail_path: string | null;
    frame_path: string | null;
    photo_slots: number;
    slot_positions: Array<{ x: number; y: number; width: number; height: number }> | null;
    print_size: string;
}

interface TransactionData {
    transaction_id: number;
    order_id: string;
    amount: number;
    qr_url: string;
    expired_at: string;
}

interface CapturedPhoto {
    id: number;
    url: string;
    order: number;
}

type Step = 'landing' | 'packages' | 'payment' | 'capturing' | 'frame' | 'preview' | 'complete';

// ─── Props ────────────────────────────────────────────────────────────────────

interface Props {
    branch: Branch;
    packages: Package[];
    templates: Template[];
    settings: {
        site_name: string | null;
        logo_path: string | null;
    };
}

// ─── Component ────────────────────────────────────────────────────────────────

export default function BoothShow({ branch, packages, templates, settings }: Props) {
    const [step, setStep] = useState<Step>('landing');
    const [loadingAction, setLoadingAction] = useState(false);

    // Session data
    const [selectedPackage, setSelectedPackage] = useState<Package | null>(null);
    const [transaction, setTransaction] = useState<TransactionData | null>(null);
    const [sessionId, setSessionId] = useState<number | null>(null);
    const [photoCount, setPhotoCount] = useState(0);
    const [capturedPhotos, setCapturedPhotos] = useState<CapturedPhoto[]>([]);
    const [selectedTemplate, setSelectedTemplate] = useState<Template | null>(null);

    const siteName = settings.site_name ?? 'Philo Photobooth';
    const logoUrl = settings.logo_path ? `/storage/${settings.logo_path}` : null;

    // ── Reset ────────────────────────────────────────────────────────────────

    const reset = useCallback(() => {
        setStep('landing');
        setLoadingAction(false);
        setSelectedPackage(null);
        setTransaction(null);
        setSessionId(null);
        setPhotoCount(0);
        setCapturedPhotos([]);
        setSelectedTemplate(null);
    }, []);

    // ── Handlers ─────────────────────────────────────────────────────────────

    const handlePackageSelect = useCallback(
        async (pkg: Package) => {
            setLoadingAction(true);
            setSelectedPackage(pkg);
            try {
                const data = await api.post<TransactionData>('/booth/session/start', {
                    branch_id: branch.id,
                    package_id: pkg.id,
                });
                setTransaction(data);
                setStep('payment');
            } catch {
                // stay on packages step
            } finally {
                setLoadingAction(false);
            }
        },
        [branch.id],
    );

    const handlePaymentPaid = useCallback(async () => {
        if (!transaction) return;
        try {
            const data = await api.post<{ session_id: number; photo_count: number }>('/booth/session/create', {
                transaction_id: transaction.transaction_id,
            });
            setSessionId(data.session_id);
            setPhotoCount(data.photo_count);
            setStep('capturing');
        } catch {
            reset();
        }
    }, [transaction, reset]);

    const handleCaptureComplete = useCallback((photos: CapturedPhoto[]) => {
        setCapturedPhotos(photos);
        setStep('frame');
    }, []);

    const handleFrameSelect = useCallback(
        async (template: Template) => {
            if (!sessionId) return;
            setLoadingAction(true);
            setSelectedTemplate(template);
            try {
                await api.post('/booth/session/template', {
                    session_id: sessionId,
                    template_id: template.id,
                });
                setStep('preview');
            } catch {
                setStep('preview');
            } finally {
                setLoadingAction(false);
            }
        },
        [sessionId],
    );

    const handleFrameSkip = useCallback(() => {
        setSelectedTemplate(null);
        setStep('preview');
    }, []);

    const handlePrint = useCallback(
        async (finalImage: string, email?: string) => {
            if (!sessionId) return;
            setLoadingAction(true);
            try {
                await api.post('/booth/session/complete', {
                    session_id: sessionId,
                    final_image_data: finalImage,
                    customer_email: email ?? null,
                });
                setStep('complete');
            } catch {
                setStep('complete');
            } finally {
                setLoadingAction(false);
            }
        },
        [sessionId],
    );

    // ── Render ────────────────────────────────────────────────────────────────

    return (
        <div className="fixed inset-0 overflow-hidden bg-black">
            {/* Ambient glow */}
            <div className="pointer-events-none absolute inset-0">
                <div className="absolute left-1/4 top-1/4 h-96 w-96 rounded-full blur-3xl" style={{ background: 'rgba(245,250,12,0.04)' }} />
                <div className="absolute bottom-1/4 right-1/4 h-96 w-96 rounded-full blur-3xl" style={{ background: 'rgba(245,250,12,0.03)' }} />
            </div>

            {/* Step container */}
            <div className="relative h-full w-full">
                {step === 'landing' && (
                    <LandingStep
                        branchName={branch.name}
                        siteName={siteName}
                        logoUrl={logoUrl}
                        onStart={() => setStep('packages')}
                    />
                )}

                {step === 'packages' && (
                    <PackageStep
                        packages={packages}
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

                {step === 'capturing' && sessionId && (
                    <CaptureStep
                        sessionId={sessionId}
                        photoCount={photoCount}
                        onComplete={handleCaptureComplete}
                    />
                )}

                {step === 'frame' && (
                    <FrameStep
                        templates={templates}
                        onSelect={handleFrameSelect}
                        onSkip={handleFrameSkip}
                        loading={loadingAction}
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

                {step === 'complete' && <CompleteStep onReset={reset} />}
            </div>

            {/* Step indicator */}
            {step !== 'landing' && step !== 'complete' && (
                <div className="absolute bottom-4 left-1/2 -translate-x-1/2">
                    <div className="flex gap-2 rounded-full bg-black/40 px-4 py-2 backdrop-blur-sm">
                        {(['packages', 'payment', 'capturing', 'frame', 'preview'] as const).map((s) => (
                            <div
                                key={s}
                                className={`h-1.5 rounded-full transition-all duration-300 ${
                                    s === step ? 'w-6' : 'w-1.5 bg-white/20'
                                }`}
                                style={s === step ? { background: '#F5FA0C' } : undefined}
                            />
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
