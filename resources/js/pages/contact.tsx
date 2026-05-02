import { Link, usePage } from '@inertiajs/react';
import type { ComponentType } from 'react';
import {
    ArrowRight,
    ArrowUpRight,
    CalendarCheck2,
    Clock3,
    Facebook,
    Globe2,
    Instagram,
    MapPin,
    MessageCircle,
    Music2,
    Send,
    ShieldCheck,
    Sparkles,
    Star,
    Zap,
} from 'lucide-react';
import HomeLayout from '@/layouts/home-layout';

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
    handle: string;
    href: string;
    icon: ComponentType<{ className?: string; style?: React.CSSProperties }>;
    gradient: string;
    accent: string;
    cta: string;
    tag: string;
};

function normalizeWhatsapp(number?: string | null) {
    return number ? number.replace(/[^0-9]/g, '') : '';
}

function formatWhatsappLabel(number: string) {
    if (!number) return '';
    return number.startsWith('0') ? number : `+${number}`;
}

function extractHandle(url: string): string {
    const cleaned = url.replace(/^https?:\/\//, '').replace(/\/$/, '');
    const lastSegment = cleaned.split('/').pop() || cleaned;
    const handle = lastSegment.replace(/^@/, '');
    return handle ? `@${handle}` : cleaned;
}

export default function ContactPage() {
    const { contact } = usePage<{ contact: ContactSettings }>().props;

    const whatsappNumber = normalizeWhatsapp(contact.whatsapp_number);
    const whatsappHref = whatsappNumber ? `https://wa.me/${whatsappNumber}` : null;

    const channels: ContactItem[] = [
        contact.instagram_url
            ? {
                  label: 'Instagram',
                  handle: extractHandle(contact.instagram_url),
                  href: contact.instagram_url,
                  icon: Instagram,
                  gradient: 'linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%)',
                  accent: '#dc2743',
                  cta: 'Lihat profil',
                  tag: 'Inspirasi visual',
              }
            : null,
        contact.facebook_url
            ? {
                  label: 'Facebook',
                  handle: extractHandle(contact.facebook_url),
                  href: contact.facebook_url,
                  icon: Facebook,
                  gradient: 'linear-gradient(135deg, #1877f2 0%, #0c63d4 100%)',
                  accent: '#1877f2',
                  cta: 'Kunjungi page',
                  tag: 'Komunitas',
              }
            : null,
        contact.x_url
            ? {
                  label: 'X / Twitter',
                  handle: extractHandle(contact.x_url),
                  href: contact.x_url,
                  icon: Globe2,
                  gradient: 'linear-gradient(135deg, #1f2937 0%, #000 100%)',
                  accent: '#0f0f0f',
                  cta: 'Ikuti updates',
                  tag: 'Update real-time',
              }
            : null,
        contact.tiktok_url
            ? {
                  label: 'TikTok',
                  handle: extractHandle(contact.tiktok_url),
                  href: contact.tiktok_url,
                  icon: Music2,
                  gradient: 'linear-gradient(135deg, #25f4ee 0%, #000 50%, #fe2c55 100%)',
                  accent: '#fe2c55',
                  cta: 'Tonton video',
                  tag: 'Video & reels',
              }
            : null,
    ].filter(Boolean) as ContactItem[];

    const reasons = [
        {
            icon: CalendarCheck2,
            title: 'Booking event',
            description: 'Kirim tanggal, kota, dan jenis acara — kami arahkan ke paket yang paling pas.',
        },
        {
            icon: Sparkles,
            title: 'Custom campaign',
            description: 'Aktivasi brand, template khusus, sampai pengalaman booth yang lebih personal.',
        },
        {
            icon: Clock3,
            title: 'Respon cepat',
            description: 'WhatsApp dijawab kurang dari 1 jam pada jam operasional.',
        },
        {
            icon: ShieldCheck,
            title: 'Koordinasi rapi',
            description: 'Satu titik komunikasi dari pertanyaan awal sampai hari acara.',
        },
    ];

    return (
        <HomeLayout title="Kontak Kami">
            {/* ── HERO ── */}
            <section className="relative overflow-hidden pt-28 pb-12 sm:pt-36 sm:pb-20">
                {/* Decorative blobs */}
                <div className="pointer-events-none absolute inset-0" aria-hidden>
                    <div
                        className="absolute -top-20 -left-20 size-[500px] rounded-full opacity-50 blur-[120px]"
                        style={{ background: 'radial-gradient(circle, rgba(232,201,0,0.25) 0%, transparent 70%)' }}
                    />
                    <div
                        className="absolute top-1/3 -right-20 size-96 rounded-full opacity-30 blur-[100px]"
                        style={{ background: 'radial-gradient(circle, rgba(232,201,0,0.18) 0%, transparent 70%)' }}
                    />
                </div>

                <div className="relative mx-auto max-w-6xl px-4 sm:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-5 flex items-center gap-1.5 text-xs text-zinc-500 sm:mb-7 sm:gap-2 sm:text-sm">
                        <Link href="/" className="transition hover:text-zinc-800">Beranda</Link>
                        <span className="text-zinc-300">›</span>
                        <span className="font-semibold" style={{ color: '#78680a' }}>Kontak Kami</span>
                    </nav>

                    <div className="grid gap-7 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-10">
                        {/* LEFT: Hero copy */}
                        <div>
                            <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <span
                                    className="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold tracking-[0.18em] uppercase sm:px-3 sm:py-1.5 sm:text-[11px] sm:tracking-[0.22em]"
                                    style={{ borderColor: 'rgba(232,201,0,0.4)', background: YELLOW_SOFT, color: '#78680a' }}
                                >
                                    <span className="size-1.5 rounded-full" style={{ background: YELLOW }} />
                                    Kontak Studio
                                </span>
                                <span className="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 px-2.5 py-1 text-[10px] font-semibold tracking-[0.16em] text-white/90 uppercase sm:px-3 sm:py-1.5 sm:text-[11px] sm:tracking-[0.18em]">
                                    <Zap className="size-3" style={{ color: YELLOW }} />
                                    Respon &lt; 1 jam
                                </span>
                            </div>

                            <h1
                                className="mt-4 text-[1.85rem] leading-[1.1] font-extrabold tracking-tight text-zinc-950 sm:mt-6 sm:text-5xl sm:leading-[1.05] lg:text-[3.4rem]"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Punya pertanyaan?
                                <br />
                                <span className="relative inline-block">
                                    <span
                                        className="absolute inset-x-0 bottom-1 h-2 -z-10 rounded-sm sm:h-3"
                                        style={{ background: YELLOW, opacity: 0.55 }}
                                    />
                                    <span className="relative">Kami siap bantu.</span>
                                </span>
                            </h1>

                            <p className="mt-3 max-w-xl text-sm leading-6 text-zinc-500 sm:mt-5 sm:text-[1.05rem] sm:leading-7">
                                {contact.site_description ||
                                    'Dari booking foto wisuda, pernikahan, sampai aktivasi brand — semua bisa disusun dari satu kanal komunikasi yang simpel.'}
                            </p>

                            {/* CTAs */}
                            <div className="mt-5 flex flex-wrap items-center gap-2 sm:mt-8 sm:gap-3">
                                {whatsappHref && (
                                    <a
                                        href={whatsappHref}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="group inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-[13px] font-bold text-zinc-950 transition-all hover:-translate-y-0.5 active:scale-95 sm:gap-2.5 sm:px-7 sm:py-3.5 sm:text-sm"
                                        style={{ background: YELLOW, boxShadow: '0 8px 28px rgba(232,201,0,0.45)' }}
                                    >
                                        <MessageCircle className="size-4" />
                                        Chat via WhatsApp
                                        <ArrowRight className="size-4 transition group-hover:translate-x-0.5" />
                                    </a>
                                )}
                                <a
                                    href="#channels"
                                    className="inline-flex items-center gap-1.5 rounded-full border border-zinc-300 bg-white px-4 py-2.5 text-[13px] font-semibold text-zinc-700 transition hover:border-zinc-800 hover:text-zinc-950 sm:gap-2 sm:px-6 sm:py-3.5 sm:text-sm"
                                >
                                    Lihat semua kanal
                                    <ArrowDownIcon />
                                </a>
                            </div>

                            {/* Trust strip */}
                            <div className="mt-5 flex flex-wrap items-center gap-x-4 gap-y-1.5 border-t border-zinc-100 pt-4 sm:mt-8 sm:gap-x-6 sm:gap-y-2 sm:pt-6">
                                {[
                                    { icon: Star, label: 'Rating 4.9+' },
                                    { icon: Send, label: 'Respon cepat' },
                                    { icon: ShieldCheck, label: 'Koordinasi 1 kanal' },
                                ].map(({ icon: Icon, label }) => (
                                    <span key={label} className="inline-flex items-center gap-1 text-[11px] font-semibold text-zinc-500 sm:gap-1.5 sm:text-xs">
                                        <Icon className="size-3 sm:size-3.5" style={{ color: YELLOW }} />
                                        {label}
                                    </span>
                                ))}
                            </div>
                        </div>

                        {/* RIGHT: WhatsApp dark card */}
                        <div
                            className="relative overflow-hidden rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-7 lg:p-8"
                            style={{ background: 'linear-gradient(145deg, #18181b 0%, #27272a 100%)' }}
                        >
                            <div
                                className="pointer-events-none absolute -top-12 -right-12 size-48 rounded-full blur-3xl"
                                style={{ background: 'rgba(232,201,0,0.18)' }}
                            />
                            <div className="relative">
                                <div className="flex items-center justify-between">
                                    <p className="text-[10px] font-bold tracking-[0.25em] text-white/40 uppercase sm:tracking-[0.3em]">Quick contact</p>
                                    <span
                                        className="flex size-8 items-center justify-center rounded-xl sm:size-9"
                                        style={{ background: YELLOW_MEDIUM }}
                                    >
                                        <MessageCircle className="size-4" style={{ color: YELLOW }} />
                                    </span>
                                </div>

                                <h3 className="mt-4 text-xl leading-snug font-extrabold sm:mt-5 sm:text-2xl">
                                    Mulai ngobrol di WhatsApp
                                </h3>
                                <p className="mt-2 text-[13px] leading-5 text-white/55 sm:text-sm sm:leading-6">
                                    Untuk booking, cek jadwal, atau pertanyaan urgent — paling cepat lewat WA.
                                </p>

                                <div
                                    className="mt-4 flex items-center justify-between rounded-xl border p-3 sm:mt-5 sm:rounded-2xl sm:p-4"
                                    style={{ borderColor: 'rgba(255,255,255,0.1)', background: 'rgba(255,255,255,0.04)' }}
                                >
                                    <div className="min-w-0">
                                        <p className="text-[10px] font-bold tracking-[0.22em] text-white/35 uppercase sm:tracking-[0.25em]">Nomor aktif</p>
                                        <p className="mt-0.5 truncate text-base font-bold sm:mt-1 sm:text-lg">
                                            {whatsappNumber ? formatWhatsappLabel(whatsappNumber) : 'Belum tersedia'}
                                        </p>
                                    </div>
                                    {whatsappNumber && (
                                        <span
                                            className="ml-2 shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-bold sm:px-2.5 sm:py-1"
                                            style={{ borderColor: 'rgba(232,201,0,0.3)', color: YELLOW }}
                                        >
                                            FAST
                                        </span>
                                    )}
                                </div>

                                {whatsappHref ? (
                                    <a
                                        href={whatsappHref}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white py-3 text-[13px] font-bold text-zinc-950 transition hover:bg-zinc-100 sm:mt-5 sm:rounded-2xl sm:py-3.5 sm:text-sm"
                                    >
                                        Mulai chat sekarang
                                        <ArrowUpRight className="size-4" />
                                    </a>
                                ) : (
                                    <p className="mt-4 text-[13px] text-white/40 sm:mt-5 sm:text-sm">
                                        Tambahkan nomor WhatsApp dari pengaturan umum.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── REASONS / VALUE PROPS ── */}
            <section className="px-4 sm:px-8">
                <div className="mx-auto max-w-6xl">
                    <div
                        className="relative overflow-hidden rounded-[1.5rem] px-5 py-6 sm:rounded-[2rem] sm:px-10 sm:py-9"
                        style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 100%)' }}
                    >
                        <div
                            className="absolute inset-x-0 top-0 h-[3px]"
                            style={{ background: `linear-gradient(90deg, transparent, ${YELLOW}, transparent)` }}
                        />
                        <p className="text-[10px] font-bold tracking-[0.25em] text-white/40 uppercase sm:tracking-[0.3em]">Kenapa hubungi kami</p>
                        <h2
                            className="mt-1.5 max-w-xl text-lg leading-snug font-extrabold text-white sm:mt-2 sm:text-3xl sm:leading-tight"
                            style={{ fontFamily: "'Poppins', sans-serif" }}
                        >
                            Komunikasi rapi, hasil event lebih tenang.
                        </h2>

                        <div className="mt-5 grid gap-4 sm:mt-8 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4">
                            {reasons.map(({ icon: Icon, title, description }) => (
                                <div key={title} className="flex gap-3 sm:flex-col sm:gap-3">
                                    <div
                                        className="flex size-10 shrink-0 items-center justify-center rounded-xl border sm:size-11"
                                        style={{ background: YELLOW_MEDIUM, borderColor: 'rgba(232,201,0,0.3)' }}
                                    >
                                        <Icon className="size-4 sm:size-5" style={{ color: YELLOW }} />
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-bold text-white sm:text-base">{title}</h3>
                                        <p className="mt-0.5 text-[13px] leading-5 text-white/55 sm:mt-1 sm:text-sm sm:leading-6">{description}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>

            {/* ── CHANNELS ── */}
            <section id="channels" className="px-4 py-10 sm:px-8 sm:py-20">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-6 flex flex-wrap items-end justify-between gap-3 sm:mb-9">
                        <div className="max-w-xl">
                            <span
                                className="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold tracking-[0.18em] uppercase sm:px-3 sm:tracking-[0.22em]"
                                style={{ borderColor: 'rgba(232,201,0,0.35)', background: YELLOW_SOFT, color: '#78680a' }}
                            >
                                <span className="size-1.5 rounded-full" style={{ background: YELLOW }} />
                                Kanal aktif
                            </span>
                            <h2
                                className="mt-2 text-2xl font-extrabold tracking-tight text-zinc-950 sm:mt-3 sm:text-4xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Pilih kanal yang paling nyaman
                            </h2>
                            <p className="mt-1.5 text-[13px] leading-5 text-zinc-500 sm:mt-2 sm:text-base sm:leading-6">
                                Klik kanal di bawah untuk langsung terhubung.
                            </p>
                        </div>
                        {channels.length > 0 && (
                            <div
                                className="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold text-zinc-900 sm:gap-2 sm:px-3 sm:py-1.5 sm:text-xs"
                                style={{ borderColor: 'rgba(232,201,0,0.4)', background: YELLOW_SOFT }}
                            >
                                <span className="relative flex size-2">
                                    <span
                                        className="absolute inline-flex size-full animate-ping rounded-full opacity-75"
                                        style={{ background: YELLOW }}
                                    />
                                    <span className="relative inline-flex size-2 rounded-full" style={{ background: YELLOW }} />
                                </span>
                                {channels.length} kanal aktif
                            </div>
                        )}
                    </div>

                    {channels.length > 0 ? (
                        <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                            {channels.map((item) => {
                                const Icon = item.icon;
                                return (
                                    <a
                                        key={item.label}
                                        href={item.href}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="group relative isolate flex aspect-square flex-col justify-between overflow-hidden rounded-2xl p-3.5 text-white transition-all duration-300 hover:-translate-y-1 hover:scale-[1.02] sm:aspect-[4/5] sm:rounded-[1.8rem] sm:p-6"
                                        style={{
                                            background: item.gradient,
                                            boxShadow: `0 10px 30px -10px ${item.accent}55`,
                                        }}
                                    >
                                        {/* Decorative orbs */}
                                        <div
                                            className="pointer-events-none absolute -top-12 -right-12 size-32 rounded-full opacity-50 blur-3xl transition-all duration-500 group-hover:scale-125 group-hover:opacity-70 sm:-top-16 sm:-right-16 sm:size-48"
                                            style={{ background: 'rgba(255,255,255,0.25)' }}
                                            aria-hidden
                                        />
                                        <div
                                            className="pointer-events-none absolute -bottom-16 -left-8 size-32 rounded-full opacity-30 blur-3xl sm:-bottom-20 sm:-left-10 sm:size-44"
                                            style={{ background: 'rgba(0,0,0,0.4)' }}
                                            aria-hidden
                                        />

                                        {/* Top: tag + arrow */}
                                        <div className="relative z-10 flex items-center justify-between">
                                            <span className="hidden rounded-full border border-white/30 bg-white/15 px-2 py-0.5 text-[9px] font-bold tracking-wider text-white/95 uppercase backdrop-blur-md sm:inline-flex sm:px-2.5 sm:py-1 sm:text-[10px] sm:tracking-widest">
                                                {item.tag}
                                            </span>
                                            <span className="ml-auto flex size-7 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-md transition group-hover:bg-white group-hover:text-zinc-950 sm:size-9">
                                                <ArrowUpRight className="size-3.5 sm:size-4" />
                                            </span>
                                        </div>

                                        {/* Big icon centered */}
                                        <div className="relative z-10 flex flex-1 items-center justify-center">
                                            <div className="relative">
                                                <span
                                                    className="absolute inset-0 -z-10 rounded-2xl blur-2xl sm:rounded-3xl"
                                                    style={{ background: 'rgba(255,255,255,0.4)' }}
                                                />
                                                <span className="flex size-12 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-md transition duration-300 group-hover:scale-110 group-hover:bg-white/25 sm:size-20 sm:rounded-3xl">
                                                    <Icon className="size-6 text-white sm:size-10" />
                                                </span>
                                            </div>
                                        </div>

                                        {/* Bottom: handle + cta */}
                                        <div className="relative z-10">
                                            <p className="text-[9px] font-bold tracking-[0.18em] text-white/70 uppercase sm:text-[10px] sm:tracking-[0.22em]">{item.label}</p>
                                            <p
                                                className="mt-0.5 truncate text-[13px] font-extrabold text-white sm:mt-1 sm:text-lg"
                                                style={{ fontFamily: "'Poppins', sans-serif" }}
                                                title={item.handle}
                                            >
                                                {item.handle}
                                            </p>
                                            <div className="mt-1.5 hidden items-center gap-1.5 text-xs font-bold text-white/85 transition group-hover:text-white sm:mt-3 sm:flex">
                                                {item.cta}
                                                <ArrowRight className="size-3.5 transition group-hover:translate-x-1" />
                                            </div>
                                        </div>
                                    </a>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 px-5 py-10 text-center text-sm text-zinc-400 sm:rounded-[1.6rem] sm:px-6 sm:py-12">
                            Belum ada kanal kontak. Tambahkan dari pengaturan umum.
                        </div>
                    )}
                </div>
            </section>

            {/* ── MAP ── */}
            <section className="px-4 pb-10 sm:px-8 sm:pb-20">
                <div className="mx-auto max-w-6xl">
                    <div className="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm sm:rounded-[2rem]">
                        <div className="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 px-4 py-3.5 sm:gap-3 sm:px-7 sm:py-5">
                            <div className="flex items-center gap-2.5 sm:gap-3">
                                <span
                                    className="flex size-9 items-center justify-center rounded-xl sm:size-10"
                                    style={{ background: YELLOW }}
                                >
                                    <MapPin className="size-4 text-zinc-950" />
                                </span>
                                <div>
                                    <p className="text-[10px] font-bold tracking-[0.18em] text-zinc-400 uppercase sm:tracking-[0.22em]">Lokasi & Navigasi</p>
                                    <h3
                                        className="text-sm font-extrabold text-zinc-950 sm:text-lg"
                                        style={{ fontFamily: "'Poppins', sans-serif" }}
                                    >
                                        Temukan titik kontak kami
                                    </h3>
                                </div>
                            </div>
                            <Link
                                href="/cabang"
                                className="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[11px] font-semibold text-zinc-600 transition hover:border-zinc-900 hover:text-zinc-900 sm:gap-1.5 sm:px-3.5 sm:py-1.5 sm:text-xs"
                            >
                                Lihat semua cabang
                                <ArrowUpRight className="size-3 sm:size-3.5" />
                            </Link>
                        </div>

                        {contact.google_maps_embed ? (
                            <div
                                className="aspect-[4/3] min-h-[240px] sm:aspect-[16/8] sm:min-h-[320px] [&_iframe]:size-full [&_iframe]:border-0"
                                dangerouslySetInnerHTML={{ __html: contact.google_maps_embed }}
                            />
                        ) : (
                            <div className="flex aspect-[4/3] min-h-[240px] items-center justify-center bg-zinc-50 px-5 text-center text-xs leading-5 text-zinc-400 sm:aspect-[16/8] sm:min-h-[320px] sm:px-6 sm:text-sm sm:leading-6">
                                Embed Google Maps belum tersedia. Tambahkan dari pengaturan umum.
                            </div>
                        )}
                    </div>
                </div>
            </section>

            {/* ── FINAL CTA ── */}
            <section className="px-4 pb-12 sm:px-8 sm:pb-20">
                <div className="mx-auto max-w-6xl">
                    <div
                        className="relative overflow-hidden rounded-2xl px-5 py-8 text-center sm:rounded-[2rem] sm:px-12 sm:py-12 md:py-16"
                        style={{ background: 'linear-gradient(135deg, #18181b 0%, #27272a 60%, #18181b 100%)' }}
                    >
                        <div
                            className="pointer-events-none absolute -top-24 left-1/2 size-72 -translate-x-1/2 rounded-full blur-3xl"
                            style={{ background: 'rgba(232,201,0,0.18)' }}
                        />
                        <div className="relative">
                            <span
                                className="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-black uppercase sm:px-3 sm:py-1 sm:text-[11px]"
                                style={{ background: YELLOW }}
                            >
                                Siap mulai?
                            </span>
                            <h2
                                className="mt-3 text-xl font-extrabold leading-tight text-white sm:mt-4 sm:text-4xl md:text-5xl"
                                style={{ fontFamily: "'Poppins', sans-serif" }}
                            >
                                Konsultasi gratis,
                                <br className="sm:hidden" /> tanpa syarat.
                            </h2>
                            <p className="mx-auto mt-2 max-w-xl text-[13px] leading-5 text-white/60 sm:mt-3 sm:text-base sm:leading-6">
                                Ceritakan jenis acara, tanggal, dan ekspektasinya — kami bantu susun yang paling cocok.
                            </p>
                            {whatsappHref ? (
                                <a
                                    href={whatsappHref}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group mt-5 inline-flex items-center gap-2 rounded-full px-5 py-3 text-[13px] font-extrabold text-black transition hover:-translate-y-0.5 hover:brightness-105 active:scale-95 sm:mt-7 sm:px-7 sm:py-4 sm:text-base"
                                    style={{ background: YELLOW, boxShadow: '0 12px 36px rgba(232,201,0,0.45)' }}
                                >
                                    <MessageCircle className="size-4 sm:size-5" />
                                    Chat WhatsApp Sekarang
                                    <span className="transition group-hover:translate-x-1">→</span>
                                </a>
                            ) : (
                                <a
                                    href="#channels"
                                    className="mt-5 inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-3 text-[13px] font-bold text-white transition hover:bg-white/10 sm:mt-7 sm:px-7 sm:py-4 sm:text-base"
                                >
                                    Lihat semua kanal
                                    <ArrowUpRight className="size-4 sm:size-5" />
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </section>
        </HomeLayout>
    );
}

const ArrowDownIcon = () => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} className="size-4">
        <path d="M12 5v14M6 13l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);
