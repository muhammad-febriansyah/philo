import { usePage } from '@inertiajs/react';
import type { ComponentType } from 'react';
import {
    ArrowRight,
    ArrowUpRight,
    CalendarCheck2,
    CheckCircle2,
    Clock3,
    Facebook,
    Globe2,
    Instagram,
    MapPin,
    MessageCircle,
    Music2,
    PhoneCall,
    Send,
    ShieldCheck,
    Sparkles,
    Star,
    Zap,
} from 'lucide-react';
import HomeLayout from '@/layouts/home-layout';
import PageHeader from '@/components/page-header';

const YELLOW = '#E8C900';
const YELLOW_SOFT = 'rgba(232,201,0,0.13)';
const YELLOW_MEDIUM = 'rgba(232,201,0,0.25)';

type ContactSettings = {
    site_description?: string | null;
    google_maps_embed?: string | null;
    instagram_url?: string | null;
    facebook_url?: string | null;
    x_url?: string | null;
    tiktok_url?: string | null;
    whatsapp_number?: string | null;
};

type ContactItem = {
    label: string;
    value: string;
    href: string;
    icon: ComponentType<{ className?: string; style?: React.CSSProperties }>;
    gradient: string;
    iconBg: string;
    iconColor: string;
    note: string;
    badge: string;
};

function normalizeWhatsapp(number?: string | null) {
    return number ? number.replace(/[^0-9]/g, '') : '';
}

function formatWhatsappLabel(number: string) {
    if (!number) return '';
    return number.startsWith('0') ? number : `+${number}`;
}

export default function ContactPage() {
    const { contact } = usePage<{
        contact: ContactSettings;
    }>().props;

    const whatsappNumber = normalizeWhatsapp(contact.whatsapp_number);
    const whatsappHref = whatsappNumber ? `https://wa.me/${whatsappNumber}` : null;

    const contactItems: ContactItem[] = [
        contact.whatsapp_number
            ? {
                  label: 'WhatsApp',
                  value: formatWhatsappLabel(whatsappNumber),
                  href: `https://wa.me/${whatsappNumber}`,
                  icon: MessageCircle,
                  gradient: 'from-emerald-50 to-teal-50',
                  iconBg: '#dcfce7',
                  iconColor: '#16a34a',
                  note: 'Paling cocok untuk cek jadwal, booking, dan kebutuhan cepat.',
                  badge: 'Paling cepat',
              }
            : null,
        contact.instagram_url
            ? {
                  label: 'Instagram',
                  value: contact.instagram_url.replace(/^https?:\/\//, ''),
                  href: contact.instagram_url,
                  icon: Instagram,
                  gradient: 'from-pink-50 to-orange-50',
                  iconBg: '#fce7f3',
                  iconColor: '#db2777',
                  note: 'Lihat update terbaru, style booth, dan referensi event.',
                  badge: 'Inspirasi visual',
              }
            : null,
        contact.facebook_url
            ? {
                  label: 'Facebook',
                  value: contact.facebook_url.replace(/^https?:\/\//, ''),
                  href: contact.facebook_url,
                  icon: Facebook,
                  gradient: 'from-blue-50 to-sky-50',
                  iconBg: '#dbeafe',
                  iconColor: '#2563eb',
                  note: 'Terhubung untuk informasi layanan dan aktivitas brand.',
                  badge: 'Komunitas',
              }
            : null,
        contact.x_url
            ? {
                  label: 'X / Twitter',
                  value: contact.x_url.replace(/^https?:\/\//, ''),
                  href: contact.x_url,
                  icon: Globe2,
                  gradient: 'from-zinc-50 to-slate-50',
                  iconBg: '#f4f4f5',
                  iconColor: '#18181b',
                  note: 'Cocok untuk update singkat dan komunikasi publik.',
                  badge: 'Update real-time',
              }
            : null,
        contact.tiktok_url
            ? {
                  label: 'TikTok',
                  value: contact.tiktok_url.replace(/^https?:\/\//, ''),
                  href: contact.tiktok_url,
                  icon: Music2,
                  gradient: 'from-fuchsia-50 to-cyan-50',
                  iconBg: '#fdf4ff',
                  iconColor: '#a21caf',
                  note: 'Intip mood event, video booth, dan momen behind the scenes.',
                  badge: 'Video & reels',
              }
            : null,
    ].filter(Boolean) as ContactItem[];

    const steps = [
        { num: '01', text: 'Ceritakan jenis acara, tanggal, dan lokasi.' },
        { num: '02', text: 'Kami bantu arahkan paket atau opsi custom yang relevan.' },
        { num: '03', text: 'Lanjutkan koordinasi teknis sampai hari event.' },
    ];

    const helperPoints = [
        {
            icon: CalendarCheck2,
            title: 'Booking event lebih rapi',
            description: 'Kirim tanggal, kota, dan jenis acara. Tim kami bantu arahkan paket yang paling pas.',
        },
        {
            icon: Sparkles,
            title: 'Kebutuhan custom campaign',
            description: 'Kami bisa diskusikan aktivasi brand, template, sampai pengalaman booth yang lebih personal.',
        },
        {
            icon: ShieldCheck,
            title: 'Koordinasi lebih tenang',
            description: 'Mulai dari pertanyaan awal sampai hari acara, semua bisa disusun lewat satu titik komunikasi.',
        },
    ];

    const serviceHighlights = [
        {
            icon: Clock3,
            title: 'Respon cepat',
            stat: '< 1 jam',
            description: 'WhatsApp jadi jalur utama untuk pertanyaan urgent dan pengecekan ketersediaan jadwal.',
        },
        {
            icon: PhoneCall,
            title: 'Brief lebih jelas',
            stat: '3 langkah',
            description: 'Bagikan rundown acara, ekspektasi tamu, dan kebutuhan cetak agar rekomendasinya lebih akurat.',
        },
        {
            icon: MapPin,
            title: 'Koordinasi lokasi',
            stat: 'Multi-kota',
            description: 'Peta di bawah memudahkan tamu, partner, atau tim event menemukan titik yang relevan.',
        },
    ];

    return (
        <HomeLayout title="Kontak Kami">
            <PageHeader
                title="Kontak Kami"
                description="Hubungi kami untuk reservasi, pertanyaan layanan, atau kebutuhan event photobooth yang lebih personal."
                breadcrumbs={[
                    { label: 'Beranda', href: '/' },
                    { label: 'Kontak Kami' },
                ]}
            />

            {/* ── HERO SECTION ── */}
            <section className="relative overflow-hidden">
                {/* Decorative blobs */}
                <div className="pointer-events-none absolute inset-0" aria-hidden>
                    <div
                        className="absolute -top-20 -left-20 h-[500px] w-[500px] rounded-full opacity-40 blur-[120px]"
                        style={{ background: 'radial-gradient(circle, rgba(232,201,0,0.3) 0%, transparent 70%)' }}
                    />
                    <div
                        className="absolute top-1/3 -right-20 h-[400px] w-[400px] rounded-full opacity-30 blur-[100px]"
                        style={{ background: 'radial-gradient(circle, rgba(232,201,0,0.2) 0%, transparent 70%)' }}
                    />
                </div>

                <div className="relative mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-16">
                    <div className="grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:gap-10">

                        {/* ── LEFT: Hero Copy ── */}
                        <div className="flex flex-col justify-center">
                            {/* Badge row */}
                            <div className="flex flex-wrap items-center gap-3">
                                <span
                                    className="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-semibold tracking-[0.22em] uppercase"
                                    style={{ borderColor: 'rgba(232,201,0,0.4)', background: YELLOW_SOFT, color: '#78680a' }}
                                >
                                    <span className="h-1.5 w-1.5 rounded-full" style={{ background: YELLOW }} />
                                    Kontak Studio
                                </span>
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 px-4 py-1.5 text-[11px] font-semibold tracking-[0.18em] text-white/90 uppercase">
                                    <Zap className="h-3 w-3" style={{ color: YELLOW }} />
                                    Respon Cepat
                                </span>
                            </div>

                            {/* Heading */}
                            <h1 className="mt-7 text-4xl leading-[1.07] font-black text-zinc-950 sm:text-5xl lg:text-[3.5rem] lg:leading-[1.05]">
                                Satu titik kontak{' '}
                                <span
                                    className="relative inline-block"
                                    style={{ color: '#78680a' }}
                                >
                                    <span
                                        className="absolute inset-x-0 bottom-1 h-[6px] rounded-full"
                                        style={{ background: YELLOW, opacity: 0.7, zIndex: -1, transform: 'scaleX(1.04)' }}
                                    />
                                    untuk semua
                                </span>{' '}
                                kebutuhanmu.
                            </h1>

                            <p className="mt-6 max-w-xl text-base leading-8 text-zinc-500 sm:text-[1.05rem]">
                                {contact.site_description ||
                                    'Dari booking foto wisuda, pernikahan, sampai aktivasi brand — kami siap bantu semua persiapan dari satu kanal komunikasi yang simpel.'}
                            </p>

                            {/* CTA Buttons */}
                            <div className="mt-9 flex flex-wrap items-center gap-3">
                                {whatsappHref && (
                                    <a
                                        href={whatsappHref}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="group inline-flex items-center gap-2.5 rounded-full px-7 py-3.5 text-sm font-bold text-zinc-950 transition-all hover:-translate-y-0.5"
                                        style={{ background: YELLOW }}
                                    >
                                        <MessageCircle className="h-4 w-4" />
                                        Chat via WhatsApp
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                                    </a>
                                )}
                                <a
                                    href="#contact-channels"
                                    className="inline-flex items-center gap-2 rounded-full border border-zinc-300 bg-white px-6 py-3.5 text-sm font-semibold text-zinc-700 transition hover:border-zinc-800 hover:text-zinc-950"
                                >
                                    Lihat semua kanal
                                    <ArrowDownIcon />
                                </a>
                            </div>

                            {/* Trust indicators */}
                            <div className="mt-10 flex flex-wrap items-center gap-5 border-t border-zinc-100 pt-7">
                                {[
                                    { icon: Star, label: 'Rating 4.9+' },
                                    { icon: CheckCircle2, label: 'QRIS tersedia' },
                                    { icon: Send, label: 'Respon < 1 jam' },
                                ].map(({ icon: Icon, label }) => (
                                    <span key={label} className="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500">
                                        <Icon className="h-3.5 w-3.5" style={{ color: YELLOW }} />
                                        {label}
                                    </span>
                                ))}
                            </div>
                        </div>

                        {/* ── RIGHT: Quick Contact + Steps ── */}
                        <div className="flex flex-col gap-5">
                            {/* Dark WhatsApp card */}
                            <div
                                className="relative overflow-hidden rounded-[2rem] p-7 text-white"
                                style={{ background: 'linear-gradient(145deg, #18181b 0%, #27272a 100%)' }}
                            >
                                {/* Glow accent */}
                                <div
                                    className="pointer-events-none absolute -top-8 -right-8 h-40 w-40 rounded-full blur-3xl"
                                    style={{ background: 'rgba(232,201,0,0.15)' }}
                                    aria-hidden
                                />

                                <div className="relative">
                                    <div className="flex items-center justify-between gap-3">
                                        <p className="text-[10px] font-bold tracking-[0.3em] text-white/40 uppercase">Quick Contact</p>
                                        <span
                                            className="flex h-9 w-9 items-center justify-center rounded-xl"
                                            style={{ background: YELLOW_MEDIUM }}
                                        >
                                            <MessageCircle className="h-4 w-4" style={{ color: YELLOW }} />
                                        </span>
                                    </div>

                                    <h3 className="mt-5 text-2xl font-extrabold leading-snug sm:text-[1.8rem]">
                                        Kanal tercepat untuk mulai ngobrol
                                    </h3>
                                    <p className="mt-3 text-sm leading-7 text-white/55">
                                        Untuk booking, cek jadwal, atau pertanyaan mendadak — WhatsApp adalah jalur terbaik agar responnya instan.
                                    </p>

                                    {/* Number box */}
                                    <div
                                        className="mt-6 flex items-center justify-between rounded-2xl border p-4"
                                        style={{ borderColor: 'rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.05)' }}
                                    >
                                        <div>
                                            <p className="text-[10px] font-bold tracking-[0.25em] text-white/35 uppercase">Nomor Aktif</p>
                                            <p className="mt-1.5 text-lg font-bold text-white">
                                                {whatsappNumber ? formatWhatsappLabel(whatsappNumber) : 'Belum tersedia'}
                                            </p>
                                        </div>
                                        <span
                                            className="rounded-full border px-3 py-1 text-[11px] font-semibold"
                                            style={{ borderColor: 'rgba(232,201,0,0.3)', color: YELLOW }}
                                        >
                                            Fast lane
                                        </span>
                                    </div>

                                    {whatsappHref ? (
                                        <a
                                            href={whatsappHref}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white py-3.5 text-sm font-bold text-zinc-950 transition hover:bg-zinc-100"
                                        >
                                            Mulai chat sekarang
                                            <ArrowUpRight className="h-4 w-4" />
                                        </a>
                                    ) : (
                                        <p className="mt-5 text-sm leading-6 text-white/40">
                                            Tambahkan nomor WhatsApp dari pengaturan umum untuk mengaktifkan tombol ini.
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Steps card */}
                            <div className="rounded-[2rem] bg-white p-6 sm:p-7">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <p className="text-[10px] font-bold tracking-[0.3em] text-zinc-400 uppercase">Alur Booking</p>
                                        <h3 className="mt-2 text-xl font-extrabold text-zinc-950">3 langkah simpel</h3>
                                    </div>
                                    <span
                                        className="flex h-9 w-9 items-center justify-center rounded-xl text-sm font-black text-zinc-950"
                                        style={{ background: YELLOW }}
                                    >
                                        3
                                    </span>
                                </div>

                                <div className="mt-5 space-y-3">
                                    {steps.map(({ num, text }) => (
                                        <div key={num} className="flex items-start gap-4 rounded-2xl bg-zinc-50/60 p-4">
                                            <span
                                                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black text-zinc-950"
                                                style={{ background: YELLOW_SOFT }}
                                            >
                                                {num}
                                            </span>
                                            <p className="pt-0.5 text-sm leading-6 text-zinc-600">{text}</p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── FEATURE POINTS STRIP ── */}
            <section className="relative overflow-hidden px-4 sm:px-6">
                <div className="mx-auto max-w-6xl">
                    <div
                        className="relative overflow-hidden rounded-[2rem] px-6 py-8 sm:px-10 sm:py-10"
                        style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 100%)' }}
                    >
                        {/* Yellow decorative line */}
                        <div
                            className="absolute inset-x-0 top-0 h-[3px]"
                            style={{ background: `linear-gradient(90deg, transparent, ${YELLOW}, transparent)` }}
                        />

                        <div className="grid gap-6 sm:grid-cols-3">
                            {helperPoints.map(({ icon: Icon, title, description }) => (
                                <div key={title} className="group flex flex-col gap-4">
                                    <div
                                        className="flex h-12 w-12 items-center justify-center rounded-2xl border transition-all group-hover:scale-105"
                                        style={{ background: YELLOW_MEDIUM, borderColor: 'rgba(232,201,0,0.3)' }}
                                    >
                                        <Icon className="h-5 w-5" style={{ color: YELLOW }} />
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-white">{title}</h3>
                                        <p className="mt-1.5 text-sm leading-6 text-white/50">{description}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── CONTACT CHANNELS ── */}
            <section id="contact-channels" className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
                <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div className="max-w-xl">
                        <span
                            className="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-[11px] font-bold tracking-[0.22em] uppercase"
                            style={{ borderColor: 'rgba(232,201,0,0.35)', background: YELLOW_SOFT, color: '#78680a' }}
                        >
                            <span className="h-1.5 w-1.5 rounded-full" style={{ background: YELLOW }} />
                            Kanal Aktif
                        </span>
                        <h2 className="mt-4 text-3xl font-extrabold text-zinc-950 sm:text-4xl">
                            Pilih kanal yang paling{' '}
                            <span style={{ color: '#78680a' }}>nyaman</span> buat kamu
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-zinc-500 sm:text-base">
                            Semua tombol di bawah langsung mengarah ke kanal terkait — bisa langsung mulai percakapan tanpa ribet.
                        </p>
                    </div>
                    <div
                        className="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-bold text-zinc-900"
                        style={{ borderColor: 'rgba(232,201,0,0.4)', background: YELLOW_SOFT }}
                    >
                        <span className="relative flex h-2 w-2">
                            <span
                                className="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                style={{ background: YELLOW }}
                            />
                            <span className="relative inline-flex h-2 w-2 rounded-full" style={{ background: YELLOW }} />
                        </span>
                        {contactItems.length} kanal aktif
                    </div>
                </div>

                {contactItems.length > 0 ? (
                    <div className="mt-8 space-y-3">
                        {contactItems.map((item, i) => {
                            const Icon = item.icon;
                            const isFeatured = i === 0;

                            return isFeatured ? (
                                /* Featured card — dark, full width */
                                <a
                                    key={item.label}
                                    href={item.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group relative flex items-center justify-between gap-6 overflow-hidden rounded-[1.8rem] p-6 transition-all hover:-translate-y-0.5 sm:p-8"
                                    style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 100%)' }}
                                >
                                    {/* Glow */}
                                    <div
                                        className="pointer-events-none absolute -top-10 -left-10 h-40 w-40 rounded-full blur-3xl"
                                        style={{ background: `${item.iconColor}22` }}
                                    />
                                    <div className="relative flex items-center gap-5">
                                        <div
                                            className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                                            style={{ background: `${item.iconColor}22` }}
                                        >
                                            <Icon className="h-6 w-6" style={{ color: item.iconColor }} />
                                        </div>
                                        <div>
                                            <div className="flex items-center gap-2.5">
                                                <p className="text-[10px] font-bold tracking-[0.25em] text-white/40 uppercase">{item.label}</p>
                                                <span
                                                    className="rounded-full px-2.5 py-0.5 text-[10px] font-bold text-black"
                                                    style={{ background: YELLOW }}
                                                >
                                                    {item.badge}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-lg font-extrabold text-white">{item.value}</p>
                                            <p className="mt-1 text-sm text-white/50">{item.note}</p>
                                        </div>
                                    </div>
                                    <span
                                        className="relative shrink-0 flex h-11 w-11 items-center justify-center rounded-2xl text-black transition group-hover:brightness-110"
                                        style={{ background: YELLOW }}
                                    >
                                        <ArrowUpRight className="h-5 w-5" />
                                    </span>
                                </a>
                            ) : (
                                /* Regular row */
                                <a
                                    key={item.label}
                                    href={item.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group relative flex items-center justify-between gap-4 overflow-hidden rounded-[1.6rem] bg-white px-6 py-5 transition-all hover:-translate-y-0.5"
                                >
                                    {/* Left color stripe */}
                                    <div
                                        className="absolute inset-y-0 left-0 w-1 rounded-l-[1.6rem] opacity-60"
                                        style={{ background: item.iconColor }}
                                    />
                                    <div className="flex items-center gap-4 pl-3">
                                        <div
                                            className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl transition-transform group-hover:scale-105"
                                            style={{ background: item.iconBg }}
                                        >
                                            <Icon className="h-5 w-5" style={{ color: item.iconColor }} />
                                        </div>
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <p className="text-[10px] font-bold tracking-[0.2em] text-zinc-400 uppercase">{item.label}</p>
                                                <span className="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-semibold text-zinc-500">
                                                    {item.badge}
                                                </span>
                                            </div>
                                            <p className="mt-0.5 truncate text-sm font-bold text-zinc-950">{item.value}</p>
                                        </div>
                                    </div>
                                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 transition group-hover:bg-zinc-950 group-hover:text-white">
                                        <ArrowUpRight className="h-3.5 w-3.5" />
                                    </span>
                                </a>
                            );
                        })}
                    </div>
                ) : (
                    <div className="mt-8 rounded-[1.6rem] bg-zinc-50 px-6 py-10 text-center text-sm leading-7 text-zinc-400">
                        Kanal kontak belum diisi. Tambahkan WhatsApp atau sosial media dari pengaturan umum.
                    </div>
                )}
            </section>

            {/* ── SERVICE HIGHLIGHTS + MAP ── */}
            <section className="px-4 pb-16 sm:px-6 sm:pb-24">
                <div className="mx-auto max-w-6xl">
                    <div className="grid gap-6 xl:grid-cols-[1fr_0.85fr]">

                        {/* Map */}
                        <div className="order-2 xl:order-1 rounded-[2rem] bg-white p-4 sm:p-5">
                            <div className="flex flex-col gap-3 px-2 pt-2 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <span
                                        className="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-bold tracking-[0.22em] uppercase"
                                        style={{ borderColor: 'rgba(232,201,0,0.35)', background: YELLOW_SOFT, color: '#78680a' }}
                                    >
                                        <MapPin className="h-3 w-3" />
                                        Lokasi & Navigasi
                                    </span>
                                    <h2 className="mt-3 text-2xl font-extrabold text-zinc-950 sm:text-3xl">
                                        Temukan titik kontak kami
                                    </h2>
                                    <p className="mt-2 text-sm leading-6 text-zinc-500">
                                        Peta ini membantu tamu, partner, dan tim event menemukan lokasi dengan lebih cepat.
                                    </p>
                                </div>
                                <span
                                    className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                                    style={{ background: YELLOW }}
                                >
                                    <Globe2 className="h-4 w-4 text-zinc-950" />
                                </span>
                            </div>

                            <div className="mt-5 overflow-hidden rounded-[1.5rem] bg-zinc-100">
                                {contact.google_maps_embed ? (
                                    <div
                                        className="aspect-[16/10] min-h-[320px] [&_iframe]:h-full [&_iframe]:w-full [&_iframe]:border-0"
                                        dangerouslySetInnerHTML={{ __html: contact.google_maps_embed }}
                                    />
                                ) : (
                                    <div className="flex aspect-[16/10] min-h-[320px] items-center justify-center px-6 text-center text-sm leading-7 text-zinc-400">
                                        Embed Google Maps belum tersedia. Tambahkan dari pengaturan umum.
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Service highlights */}
                        <div className="order-1 xl:order-2 grid gap-4 self-start">
                            {serviceHighlights.map(({ icon: Icon, title, stat, description }) => (
                                <div
                                    key={title}
                                    className="group relative overflow-hidden rounded-[1.8rem] bg-white p-6 transition hover:-translate-y-0.5"
                                >
                                    {/* Yellow glow line on left */}
                                    <div
                                        className="absolute inset-y-4 left-0 w-[3px] rounded-r-full opacity-0 transition-opacity group-hover:opacity-100"
                                        style={{ background: YELLOW }}
                                        aria-hidden
                                    />

                                    <div className="flex items-start gap-5">
                                        <div
                                            className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition-transform group-hover:scale-105"
                                            style={{ background: YELLOW_SOFT }}
                                        >
                                            <Icon className="h-5 w-5 text-zinc-900" />
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex items-center justify-between gap-2">
                                                <h3 className="text-lg font-extrabold text-zinc-950">{title}</h3>
                                                <span
                                                    className="rounded-full border px-2.5 py-1 text-[11px] font-bold text-zinc-900"
                                                    style={{ borderColor: 'rgba(232,201,0,0.4)', background: YELLOW_SOFT }}
                                                >
                                                    {stat}
                                                </span>
                                            </div>
                                            <p className="mt-2 text-sm leading-6 text-zinc-500">{description}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}

                            {/* CTA card */}
                            <div
                                className="relative overflow-hidden rounded-[1.8rem] p-6 text-white"
                                style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 100%)' }}
                            >
                                <div
                                    className="pointer-events-none absolute -bottom-6 -right-6 h-32 w-32 rounded-full blur-3xl"
                                    style={{ background: 'rgba(232,201,0,0.18)' }}
                                    aria-hidden
                                />
                                <div className="relative">
                                    <p className="text-[10px] font-bold tracking-[0.3em] text-white/40 uppercase">Siap mulai?</p>
                                    <h3 className="mt-3 text-xl font-extrabold">Langsung hubungi kami sekarang</h3>
                                    <p className="mt-2 text-sm leading-6 text-white/50">
                                        Konsultasi gratis, tanpa syarat. Ceritakan event kamu dan kami siap bantu.
                                    </p>
                                    {whatsappHref ? (
                                        <a
                                            href={whatsappHref}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-5 inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold text-zinc-950 transition hover:brightness-105"
                                            style={{ background: YELLOW }}
                                        >
                                            Chat via WhatsApp
                                            <ArrowUpRight className="h-4 w-4" />
                                        </a>
                                    ) : (
                                        <a
                                            href="#contact-channels"
                                            className="mt-5 inline-flex items-center gap-2 rounded-2xl border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10"
                                        >
                                            Lihat semua kanal
                                            <ArrowUpRight className="h-4 w-4" />
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </HomeLayout>
    );
}

const ArrowDownIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="h-4 w-4">
        <path d="M12 5v14M6 13l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);
