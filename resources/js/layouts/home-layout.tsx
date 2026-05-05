import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import HomeFooter from '@/components/home-footer';
import {
    MobileNav,
    MobileNavHeader,
    MobileNavMenu,
    MobileNavToggle,
    NavBody,
    NavItems,
    Navbar,
    NavbarButton,
} from '@/components/ui/resizable-navbar';
import { dashboard, login } from '@/routes';

const YELLOW = '#E8C900';

/*
 * Navbar items — rekomendasi untuk photobooth multi-cabang:
 *
 * - Fitur     : highlight fitur utama sistem
 * - Cara Kerja: edukasi alur kerja untuk calon customer / operator baru
 * - Harga     : paket & pricing (anchor #pricing atau halaman /harga)
 * - Cabang    : daftar & peta lokasi booth (anchor #cabang atau /cabang)
 * - Kontak    : WhatsApp / email support
 */
const NAV_ITEMS = [
    { name: 'Home', link: '/' },
    { name: 'Profil', link: '/profil' },
    // { name: 'Harga', link: '/harga' },
    { name: 'Cabang', link: '/cabang' },
    { name: 'Kontak Kami', link: '/kontak' },
];

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
        <line
            x1="14.83"
            y1="14.83"
            x2="19.07"
            y2="19.07"
            strokeLinecap="round"
        />
        <line x1="2" y1="12" x2="8" y2="12" strokeLinecap="round" />
        <line x1="16" y1="12" x2="22" y2="12" strokeLinecap="round" />
        <line x1="4.93" y1="19.07" x2="9.17" y2="14.83" strokeLinecap="round" />
        <line x1="14.83" y1="9.17" x2="19.07" y2="4.93" strokeLinecap="round" />
    </svg>
);

interface HomeLayoutProps {
    children: React.ReactNode;
    title?: string;
}

export default function HomeLayout({
    children,
    title = 'Philo Photobooth',
}: HomeLayoutProps) {
    const { auth, settings } = usePage<{
        auth: { user: unknown };
        settings: Record<string, string>;
    }>().props;
    const [mobileOpen, setMobileOpen] = useState(false);
    const currentUrl = usePage().url;

    const ctaHref = auth.user ? dashboard().url : login().url;
    const ctaLabel = auth.user ? 'Buka Dashboard' : 'Masuk Sekarang';

    const siteName = settings?.site_name ?? 'philo';
    const logoUrl = settings?.logo_path
        ? `/storage/${settings.logo_path}`
        : null;

    const siteDescription =
        settings?.site_description ??
        `${siteName} – Photobooth terbaik untuk momen tak terlupakan.`;
    const baseUrl =
        typeof window !== 'undefined'
            ? `${window.location.protocol}//${window.location.host}`
            : '';
    const ogImage = settings?.logo_path
        ? `${baseUrl}/storage/${settings.logo_path}`
        : `${baseUrl}/philo.png`;
    const currentFullUrl =
        typeof window !== 'undefined' ? window.location.href : '';

    // Always render a favicon link — uploaded from DB if present, otherwise
    // fall back to the bundled /philo.png. Using DB data ensures changes in
    // admin settings reflect immediately on the public site.
    const faviconPath = settings?.favicon_path ?? '';
    const faviconExt = (faviconPath.split('.').pop() ?? 'png').toLowerCase();
    const faviconMime: Record<string, string> = {
        ico: 'image/x-icon',
        png: 'image/png',
        svg: 'image/svg+xml',
        jpg: 'image/jpeg',
        jpeg: 'image/jpeg',
        webp: 'image/webp',
    };
    const faviconType = faviconMime[faviconExt] ?? 'image/png';
    const faviconHash = faviconPath
        ? faviconPath.split('').reduce((h, c) => ((h << 5) - h + c.charCodeAt(0)) | 0, 0)
        : 0;
    const faviconUrl = faviconPath
        ? `/storage/${faviconPath}?v=${Math.abs(faviconHash).toString(36)}`
        : `/philo.png?v=default`;

    return (
        <>
            <Head title={`${title} – ${siteName}`}>
                <link rel="icon" type={faviconType} href={faviconUrl} />
                <link rel="shortcut icon" type={faviconType} href={faviconUrl} />
                <link rel="apple-touch-icon" href={faviconUrl} />

                {/* SEO */}
                <meta name="description" content={siteDescription} />

                {/* Open Graph */}
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content={siteName} />
                <meta property="og:title" content={`${title} – ${siteName}`} />
                <meta property="og:description" content={siteDescription} />
                <meta property="og:image" content={ogImage} />
                <meta property="og:url" content={currentFullUrl} />

                {/* Twitter Card */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={`${title} – ${siteName}`} />
                <meta name="twitter:description" content={siteDescription} />
                <meta name="twitter:image" content={ogImage} />

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
                style={{
                    fontFamily: "'Poppins', sans-serif",
                    background: '#FAFAF5',
                }}
                className="home-layout-root min-h-screen text-zinc-900"
            >
                {/* Noise texture */}
                <div
                    className="pointer-events-none fixed inset-0 z-0 opacity-[0.04]"
                    style={{
                        backgroundImage: `url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E")`,
                    }}
                />

                {/* ── NAVBAR ── */}
                <Navbar>
                    <NavBody>
                        <a
                            href="/"
                            className="relative z-20 flex items-center gap-2.5 px-2 py-1"
                        >
                            {logoUrl ? (
                                <img
                                    src={logoUrl}
                                    alt={siteName}
                                    className="h-12 w-40 object-cover"
                                />
                            ) : (
                                <>
                                    <div
                                        className="flex size-8 shrink-0 items-center justify-center rounded-xl"
                                        style={{ background: YELLOW }}
                                    >
                                        <ApertureIcon size={16} color="black" />
                                    </div>
                                    <span
                                        className="text-lg leading-none text-zinc-900"
                                        style={{
                                            fontFamily: "'Poppins', sans-serif",
                                            fontWeight: 800,
                                        }}
                                    >
                                        {siteName}
                                    </span>
                                </>
                            )}
                        </a>

                        <NavItems items={NAV_ITEMS} activeLink={currentUrl} />

                        <div className="relative z-20 flex items-center gap-3">
                            <NavbarButton
                                as="a"
                                href={ctaHref}
                                variant="primary"
                            >
                                {ctaLabel}
                            </NavbarButton>
                        </div>
                    </NavBody>

                    {/* Mobile */}
                    <MobileNav>
                        <MobileNavHeader>
                            <a href="/" className="flex items-center gap-2.5">
                                {logoUrl ? (
                                    <img
                                        src={logoUrl}
                                        alt={siteName}
                                        className="h-10 w-32 object-cover"
                                    />
                                ) : (
                                    <>
                                        <div
                                            className="flex size-8 items-center justify-center rounded-xl"
                                            style={{ background: YELLOW }}
                                        >
                                            <ApertureIcon
                                                size={16}
                                                color="black"
                                            />
                                        </div>
                                        <span
                                            className="text-lg leading-none text-zinc-900"
                                            style={{
                                                fontFamily:
                                                    "'Poppins', sans-serif",
                                                fontWeight: 800,
                                            }}
                                        >
                                            {siteName}
                                        </span>
                                    </>
                                )}
                            </a>
                            <MobileNavToggle
                                isOpen={mobileOpen}
                                onClick={() => setMobileOpen(!mobileOpen)}
                            />
                        </MobileNavHeader>

                        <MobileNavMenu
                            isOpen={mobileOpen}
                            onClose={() => setMobileOpen(false)}
                        >
                            {NAV_ITEMS.map((item) => {
                                const isActive =
                                    item.link === '/'
                                        ? currentUrl === '/'
                                        : currentUrl.startsWith(item.link);
                                return (
                                    <a
                                        key={item.name}
                                        href={item.link}
                                        onClick={() => setMobileOpen(false)}
                                        className="flex items-center gap-2 py-2 text-sm font-medium transition-colors"
                                        style={{
                                            color: isActive
                                                ? '#b89e00'
                                                : '#3f3f46',
                                        }}
                                    >
                                        {isActive && (
                                            <span
                                                className="inline-block h-1.5 w-1.5 rounded-full"
                                                style={{
                                                    background: '#E8C900',
                                                }}
                                            />
                                        )}
                                        {item.name}
                                    </a>
                                );
                            })}
                            <NavbarButton
                                as="a"
                                href={ctaHref}
                                variant="primary"
                                className="mt-2 w-full text-center"
                            >
                                {ctaLabel}
                            </NavbarButton>
                        </MobileNavMenu>
                    </MobileNav>
                </Navbar>

                {/* ── PAGE CONTENT ── */}
                <main>{children}</main>

                {/* ── FOOTER ── */}
                <HomeFooter />
            </div>
        </>
    );
}
