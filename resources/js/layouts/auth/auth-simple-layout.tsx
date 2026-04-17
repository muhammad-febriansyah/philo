import { usePage } from '@inertiajs/react';
import type { AuthLayoutProps } from '@/types';

const YELLOW = '#E8C900';

const ApertureIcon = ({
    size = 20,
    color = 'black',
}: {
    size?: number;
    color?: string;
}) => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke={color}
        strokeWidth={2}
        width={size}
        height={size}
    >
        <circle cx="12" cy="12" r="4" />
        <line x1="12" y1="2" x2="12" y2="8" strokeLinecap="round" />
        <line x1="12" y1="16" x2="12" y2="22" strokeLinecap="round" />
        <line x1="4.93" y1="4.93" x2="9.17" y2="9.17" strokeLinecap="round" />
        <line x1="14.83" y1="14.83" x2="19.07" y2="19.07" strokeLinecap="round" />
        <line x1="2" y1="12" x2="8" y2="12" strokeLinecap="round" />
        <line x1="16" y1="12" x2="22" y2="12" strokeLinecap="round" />
        <line x1="4.93" y1="19.07" x2="9.17" y2="14.83" strokeLinecap="round" />
        <line x1="14.83" y1="9.17" x2="19.07" y2="4.93" strokeLinecap="round" />
    </svg>
);

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { settings } = usePage<{
        settings: Record<string, string>;
    }>().props;

    const siteName = settings?.site_name ?? 'Philo';
    const logoUrl = settings?.logo_path ? `/storage/${settings.logo_path}` : null;

    return (
        <div
            className="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
            style={{
                fontFamily: "'Poppins', sans-serif",
                background: '#FAFAF5',
            }}
        >
            {/* Noise texture */}
            <div
                className="pointer-events-none fixed inset-0 z-0 opacity-[0.04]"
                style={{
                    backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                }}
            />

            <div className="relative z-10 w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    {/* Logo & site name */}
                    <div className="flex flex-col items-center gap-4">
                        <div className="flex flex-col items-center gap-2">
                            {logoUrl ? (
                                <img
                                    src={logoUrl}
                                    alt={siteName}
                                    className="h-12 w-auto object-contain"
                                />
                            ) : (
                                <>
                                    <div
                                        className="flex size-12 items-center justify-center rounded-2xl shadow-md"
                                        style={{ background: YELLOW }}
                                    >
                                        <ApertureIcon size={22} color="black" />
                                    </div>
                                    <span
                                        className="text-2xl leading-none text-zinc-900"
                                        style={{
                                            fontFamily: "'Poppins', sans-serif",
                                            fontWeight: 800,
                                        }}
                                    >
                                        {siteName}
                                    </span>
                                </>
                            )}
                        </div>

                        <div className="space-y-1 text-center">
                            <h1 className="text-xl font-semibold text-zinc-900">
                                {title}
                            </h1>
                            <p className="text-sm text-zinc-500">{description}</p>
                        </div>
                    </div>

                    {/* Card */}
                    <div
                        className="rounded-2xl p-6 shadow-sm"
                        style={{
                            background: 'white',
                            border: '1px solid rgba(0,0,0,0.07)',
                        }}
                    >
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
