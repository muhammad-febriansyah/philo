import { usePage } from '@inertiajs/react';
import { useState } from 'react';

const YELLOW = '#E8C900';

const NAV_LINKS = [
    { label: 'Profil', href: '/profil' },
    { label: 'Harga', href: '/harga' },
    { label: 'Cabang', href: '/cabang' },
    { label: 'Kontak', href: '/kontak' },
];

const LAYANAN_LINKS = [
    { label: 'Photo Strip', href: '/harga' },
    { label: 'Photo 4R / A4', href: '/harga' },
    { label: 'Photo A3 Grand', href: '/harga' },
    { label: 'Event Photobooth', href: '/cabang' },
    { label: 'Brand Activation', href: '/#contact' },
];

const INFORMASI_LINKS = [
    { label: 'FAQ', href: '#' },
    { label: 'Kebijakan Privasi', href: '#' },
    { label: 'Syarat & Ketentuan', href: '#' },
    { label: 'Hubungi Kami', href: '/kontak' },
];

function FooterLinkList({
    links,
}: {
    links: { label: string; href: string }[];
}) {
    const [hovered, setHovered] = useState<string | null>(null);
    return (
        <ul className="space-y-3">
            {links.map((item) => (
                <li key={item.label}>
                    <a
                        href={item.href}
                        onMouseEnter={() => setHovered(item.label)}
                        onMouseLeave={() => setHovered(null)}
                        className="group flex items-center gap-2 text-sm text-zinc-400 transition-colors duration-200"
                        style={{
                            color: hovered === item.label ? YELLOW : undefined,
                        }}
                    >
                        <span
                            className="inline-block h-px w-3 transition-all duration-200"
                            style={{
                                background:
                                    hovered === item.label ? YELLOW : '#52525b',
                                width: hovered === item.label ? '18px' : '10px',
                            }}
                        />
                        {item.label}
                    </a>
                </li>
            ))}
        </ul>
    );
}

const SOCIALS = [
    {
        label: 'Instagram',
        key: 'instagram_url',
        hoverBg: '#E1306C',
        icon: (
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth={1.75}
                className="size-4"
            >
                <rect x="2" y="2" width="20" height="20" rx="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" />
            </svg>
        ),
    },
    {
        label: 'Facebook',
        key: 'facebook_url',
        hoverBg: '#1877F2',
        icon: (
            <svg viewBox="0 0 24 24" fill="currentColor" className="size-4">
                <path d="M13.5 22v-8h2.7l.5-3h-3.2V9.1c0-.9.3-1.6 1.7-1.6H17V4.8c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.4V11H7.5v3h2.8v8h3.2z" />
            </svg>
        ),
    },
    {
        label: 'X',
        key: 'x_url',
        hoverBg: '#14171A',
        icon: (
            <svg viewBox="0 0 24 24" fill="currentColor" className="size-4">
                <path d="M18.9 2H22l-6.77 7.74L23 22h-6.1l-4.77-6.78L6.2 22H3.1l7.25-8.29L1 2h6.25l4.3 6.13L18.9 2zm-1.07 18h1.69L6.33 3.9H4.52L17.83 20z" />
            </svg>
        ),
    },
    {
        label: 'TikTok',
        key: 'tiktok_url',
        hoverBg: '#EE1D52',
        icon: (
            <svg viewBox="0 0 24 24" fill="currentColor" className="size-4">
                <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.88a8.27 8.27 0 004.84 1.55V7a4.85 4.85 0 01-1.07-.31z" />
            </svg>
        ),
    },
    {
        label: 'WhatsApp',
        key: 'whatsapp_number',
        hoverBg: '#25D366',
        icon: (
            <svg viewBox="0 0 24 24" fill="currentColor" className="size-4">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.117.551 4.099 1.514 5.817L.057 23.997l6.305-1.651A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.892a9.877 9.877 0 01-5.031-1.378l-.361-.214-3.741.979 1.004-3.638-.235-.374A9.861 9.861 0 012.108 12C2.108 6.536 6.536 2.108 12 2.108S21.892 6.536 21.892 12 17.464 21.892 12 21.892z" />
            </svg>
        ),
    },
];

export default function HomeFooter() {
    const { settings } = usePage<{ settings: Record<string, string> }>().props;

    const [hoveredSocial, setHoveredSocial] = useState<string | null>(null);

    const siteName = settings?.site_name ?? 'philo';
    const logoUrl = settings?.logo_path
        ? `/storage/${settings.logo_path}`
        : null;
    const socialLinks = SOCIALS.map((item) => {
        if (item.key === 'whatsapp_number') {
            const number = settings?.whatsapp_number?.replace(/[^0-9]/g, '');
            return number ? { ...item, href: `https://wa.me/${number}` } : null;
        }

        const href = settings?.[item.key];
        return href ? { ...item, href } : null;
    }).filter(Boolean) as Array<(typeof SOCIALS)[number] & { href: string }>;

    return (
        <footer id="contact" style={{ background: '#111111' }}>
            {/* Yellow top accent bar */}
            <div className="h-1 w-full" style={{ background: YELLOW }} />

            <div className="mx-auto max-w-6xl px-8 pt-14 pb-0">
                {/* CTA strip */}
                <div
                    className="mb-14 flex flex-col items-start justify-between gap-6 rounded-2xl px-8 py-7 sm:flex-row sm:items-center"
                    style={{ background: '#1c1c1c' }}
                >
                    <div>
                        <p className="text-base font-bold text-white">
                            Siap abadikan momen spesialmu?
                        </p>
                        <p className="mt-0.5 text-sm text-zinc-400">
                            Hubungi kami sekarang dan temukan paket yang sesuai.
                        </p>
                    </div>
                    <a
                        href="/kontak"
                        className="shrink-0 rounded-full px-6 py-2.5 text-sm font-semibold text-black transition-opacity duration-200 hover:opacity-80"
                        style={{ background: YELLOW }}
                    >
                        Hubungi Kami →
                    </a>
                </div>

                {/* Main grid */}
                <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr]">
                    {/* Col 1 — Brand */}
                    <div>
                        <a
                            href="/"
                            className="inline-flex items-center gap-2.5"
                        >
                            {logoUrl ? (
                                <img
                                    src={logoUrl}
                                    alt={siteName}
                                    className="h-12 w-40 object-cover brightness-0 invert"
                                />
                            ) : (
                                <span
                                    className="text-2xl leading-none text-white"
                                    style={{
                                        fontFamily: "'Poppins', sans-serif",
                                        fontWeight: 800,
                                    }}
                                >
                                    {siteName}
                                </span>
                            )}
                        </a>

                        <p className="mt-4 max-w-xs text-sm leading-relaxed text-zinc-400">
                            Photobooth modern untuk acara, brand activation, dan
                            kenangan yang ingin terasa spesial. Cepat, rapi, dan
                            siap dipakai di banyak cabang.
                        </p>

                        <p className="mt-3 text-xs font-medium text-zinc-500">
                            Dikelola oleh{' '}
                            <span className="font-semibold text-zinc-300">
                                {siteName} Indonesia
                            </span>
                        </p>

                        {/* Social icons */}
                        <div className="mt-6 flex items-center gap-2">
                            {socialLinks.map((s) => (
                                <a
                                    key={s.label}
                                    href={s.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label={s.label}
                                    onMouseEnter={() =>
                                        setHoveredSocial(s.label)
                                    }
                                    onMouseLeave={() => setHoveredSocial(null)}
                                    className="flex size-9 items-center justify-center rounded-full text-white transition-all duration-200"
                                    style={{
                                        background:
                                            hoveredSocial === s.label
                                                ? s.hoverBg
                                                : '#2a2a2a',
                                        transform:
                                            hoveredSocial === s.label
                                                ? 'scale(1.1)'
                                                : 'scale(1)',
                                        border: '1px solid #2f2f2f',
                                    }}
                                >
                                    {s.icon}
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Col 2 — Halaman */}
                    <div>
                        <p
                            className="mb-5 text-xs font-semibold tracking-widest uppercase"
                            style={{ color: YELLOW }}
                        >
                            Halaman
                        </p>
                        <FooterLinkList links={NAV_LINKS} />
                    </div>

                    {/* Col 3 — Layanan */}
                    <div>
                        <p
                            className="mb-5 text-xs font-semibold tracking-widest uppercase"
                            style={{ color: YELLOW }}
                        >
                            Layanan
                        </p>
                        <FooterLinkList links={LAYANAN_LINKS} />
                    </div>

                    {/* Col 4 — Informasi */}
                    <div>
                        <p
                            className="mb-5 text-xs font-semibold tracking-widest uppercase"
                            style={{ color: YELLOW }}
                        >
                            Informasi
                        </p>
                        <FooterLinkList links={INFORMASI_LINKS} />
                    </div>
                </div>

                {/* Bottom bar */}
                <div
                    className="mt-14 flex flex-col items-center justify-between gap-3 border-t py-6 sm:flex-row"
                    style={{ borderColor: '#2a2a2a' }}
                >
                    <p className="text-xs text-zinc-500">
                        Copyright © {new Date().getFullYear()}{' '}
                        <span className="text-zinc-400">{siteName}</span>. All
                        rights reserved.
                    </p>
                    <p className="text-xs text-zinc-600">
                        Tersedia di{' '}
                        <span style={{ color: YELLOW }}>seluruh Indonesia</span>
                    </p>
                </div>
            </div>
        </footer>
    );
}
