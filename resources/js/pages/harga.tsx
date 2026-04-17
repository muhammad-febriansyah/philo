import { usePage } from '@inertiajs/react';
import PageHeader from '@/components/page-header';
import HomeLayout from '@/layouts/home-layout';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';

const YELLOW = '#E8C900';
const YELLOW_DIM = 'rgba(232,201,0,0.12)';

type Package = {
    id: number;
    name: string;
    description: string | null;
    photo_count: number;
    print_size: string;
    price: number;
};

type Faq = {
    id: number;
    question: string;
    answer: string;
};

const CheckIcon = () => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke={YELLOW}
        strokeWidth={2.5}
        className="size-4 shrink-0"
    >
        <path d="M5 13l4 4L19 7" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const ArrowRight = () => (
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth={2.5}
        className="size-4"
    >
        <path
            d="M5 12h14M12 5l7 7-7 7"
            strokeLinecap="round"
            strokeLinejoin="round"
        />
    </svg>
);

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID').format(price);
}

function getPerks(pkg: Package): string[] {
    const perks: string[] = [
        `${pkg.photo_count} foto cetak`,
        `Ukuran ${pkg.print_size}`,
        'Pembayaran QRIS',
        'Cetak instan di tempat',
        'Template pilihan',
    ];
    return perks;
}

export default function Harga() {
    const { packages, faqs, whatsapp_number } = usePage<{
        packages: Package[];
        faqs: Faq[];
        whatsapp_number: string | null;
    }>().props;

    const waNumber = (whatsapp_number ?? '').replace(/\D/g, '');
    const ctaHref = waNumber ? `https://wa.me/${waNumber}` : 'https://wa.me/';

    const midIndex = Math.floor(packages.length / 2);
    const faqItems =
        faqs.length > 0
            ? faqs
            : [
                  {
                      id: 1,
                      question: 'Apakah harga sudah termasuk cetak foto?',
                      answer: 'Ya, semua paket sudah termasuk cetak foto langsung di tempat sesuai jumlah yang tertera.',
                  },
                  {
                      id: 2,
                      question: 'Metode pembayaran apa yang diterima?',
                      answer: 'Kami menerima pembayaran via QRIS yang mencakup semua dompet digital dan m-banking.',
                  },
                  {
                      id: 3,
                      question: 'Apakah bisa memilih template sendiri?',
                      answer: 'Ya, setiap paket memungkinkan kamu memilih template frame sesuai paket yang dipilih.',
                  },
                  {
                      id: 4,
                      question: 'Berapa lama proses satu sesi foto?',
                      answer: 'Rata-rata satu sesi berlangsung 10–15 menit termasuk proses cetak.',
                  },
              ];

    return (
        <HomeLayout title="Paket Harga — Philo Photobooth">
            {/* ── PAGE HEADER ── */}
            <PageHeader
                title="Paket Harga"
                description="Harga transparan tanpa biaya tersembunyi. Semua paket sudah termasuk cetak foto langsung di tempat."
                breadcrumbs={[
                    { label: 'Beranda', href: '/' },
                    { label: 'Harga' },
                ]}
            />

            {/* ── PACKAGES ── */}
            <section className="px-6 pt-16 pb-28">
                <div className="mx-auto max-w-6xl">
                    {packages.length === 0 ? (
                        <div className="py-24 text-center text-zinc-400">
                            <div className="mb-3 text-5xl">📷</div>
                            <p className="text-lg font-medium">
                                Paket belum tersedia
                            </p>
                            <p className="mt-1 text-sm">
                                Hubungi kami untuk info lebih lanjut.
                            </p>
                        </div>
                    ) : (
                        <div
                            className={`grid gap-6 ${
                                packages.length === 1
                                    ? 'mx-auto max-w-sm'
                                    : packages.length === 2
                                      ? 'mx-auto max-w-2xl md:grid-cols-2'
                                      : packages.length === 3
                                        ? 'md:grid-cols-3'
                                        : 'md:grid-cols-2 lg:grid-cols-4'
                            }`}
                        >
                            {packages.map((pkg, i) => {
                                const isPopular = i === midIndex;
                                return (
                                    <div
                                        key={pkg.id}
                                        className={`relative flex flex-col rounded-2xl border transition-all duration-300 hover:-translate-y-1 ${
                                            isPopular
                                                ? 'border-yellow-300 bg-zinc-900'
                                                : 'border-zinc-200 bg-white hover:border-zinc-300'
                                        }`}
                                    >
                                        {isPopular && (
                                            <div className="absolute -top-3.5 left-1/2 -translate-x-1/2">
                                                <span
                                                    className="rounded-full px-4 py-1 text-xs font-bold text-black"
                                                    style={{
                                                        background: YELLOW,
                                                    }}
                                                >
                                                    ✦ Terpopuler
                                                </span>
                                            </div>
                                        )}

                                        <div className="p-7">
                                            {/* Icon */}
                                            <div
                                                className="mb-5 inline-flex size-12 items-center justify-center rounded-xl"
                                                style={{
                                                    background: isPopular
                                                        ? 'rgba(232,201,0,0.15)'
                                                        : YELLOW_DIM,
                                                }}
                                            >
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke={YELLOW}
                                                    strokeWidth={1.8}
                                                    className="size-6"
                                                >
                                                    <path
                                                        d="M3 9a2 2 0 0 1 2-2h1.5l1-2h5l1 2H19a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="13"
                                                        r="3"
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                    />
                                                </svg>
                                            </div>

                                            {/* Name */}
                                            <h3
                                                className={`mb-1 text-lg font-bold ${isPopular ? 'text-white' : 'text-zinc-900'}`}
                                            >
                                                {pkg.name}
                                            </h3>

                                            {/* Description */}
                                            {pkg.description && (
                                                <p
                                                    className={`mb-5 text-sm leading-relaxed ${isPopular ? 'text-zinc-400' : 'text-zinc-500'}`}
                                                >
                                                    {pkg.description}
                                                </p>
                                            )}

                                            {/* Price */}
                                            <div className="mb-6">
                                                <span
                                                    className={`text-sm font-medium ${isPopular ? 'text-zinc-400' : 'text-zinc-400'}`}
                                                >
                                                    Rp
                                                </span>
                                                <span
                                                    className={`ml-1 text-4xl font-extrabold tracking-tight ${isPopular ? 'text-white' : 'text-zinc-900'}`}
                                                >
                                                    {formatPrice(pkg.price)}
                                                </span>
                                                <span
                                                    className={`ml-1 text-sm ${isPopular ? 'text-zinc-500' : 'text-zinc-400'}`}
                                                >
                                                    / sesi
                                                </span>
                                            </div>

                                            {/* Divider */}
                                            <div
                                                className={`mb-6 h-px ${isPopular ? 'bg-white/10' : 'bg-zinc-100'}`}
                                            />

                                            {/* Perks */}
                                            <ul className="space-y-3">
                                                {getPerks(pkg).map((perk) => (
                                                    <li
                                                        key={perk}
                                                        className="flex items-center gap-3"
                                                    >
                                                        <CheckIcon />
                                                        <span
                                                            className={`text-sm ${isPopular ? 'text-zinc-300' : 'text-zinc-600'}`}
                                                        >
                                                            {perk}
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>

                                        {/* CTA */}
                                        <div className="mt-auto p-7 pt-0">
                                            <a
                                                href={ctaHref}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className={`flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition-all duration-200 hover:-translate-y-0.5 active:scale-95 ${
                                                    isPopular
                                                        ? 'text-black hover:brightness-110'
                                                        : 'border border-zinc-200 bg-white text-zinc-800 hover:border-zinc-300 hover:bg-zinc-50'
                                                }`}
                                                style={
                                                    isPopular
                                                        ? {
                                                              background:
                                                                  YELLOW,
                                                          }
                                                        : {}
                                                }
                                            >
                                                Hubungi via WhatsApp
                                                <ArrowRight />
                                            </a>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {/* Note */}
                    <p className="mt-10 text-center text-sm text-zinc-400">
                        Harga belum termasuk pajak. Butuh paket khusus?{' '}
                        <a
                            href="#contact"
                            className="font-medium underline underline-offset-2 hover:text-zinc-600"
                        >
                            Hubungi kami
                        </a>
                        .
                    </p>
                </div>
            </section>

            {/* ── FAQ ── */}
            <section className="px-6 py-24">
                <div className="mx-auto max-w-6xl">
                    <div className="grid gap-16 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">

                        {/* Left — sticky label */}
                        <div className="lg:sticky lg:top-32">
                            <span
                                className="mb-4 inline-block rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5 text-sm font-semibold"
                                style={{ color: YELLOW }}
                            >
                                FAQ
                            </span>
                            <h2 className="text-3xl font-extrabold text-zinc-900 md:text-4xl">
                                Pertanyaan<br />yang Sering<br />Ditanyakan
                            </h2>
                            <p className="mt-4 text-sm leading-relaxed text-zinc-500">
                                Tidak menemukan jawaban yang kamu cari? Hubungi kami langsung via WhatsApp.
                            </p>
                            <div
                                className="mt-6 h-1 w-12 rounded-full"
                                style={{ background: YELLOW }}
                            />
                        </div>

                        {/* Right — accordion */}
                        <div>
                            <Accordion type="single" collapsible className="space-y-2">
                                {faqItems.map((faq, i) => (
                                    <AccordionItem
                                        key={faq.id}
                                        value={`faq-${faq.id}`}
                                        className="group overflow-hidden rounded-2xl border-0 bg-white transition-all duration-200 data-[state=open]:ring-2"
                                        style={{ ['--tw-ring-color' as string]: 'rgba(232,201,0,0.5)' }}
                                    >
                                        <AccordionTrigger className="px-5 py-4 hover:no-underline hover:bg-zinc-50 [&>svg]:hidden">
                                            <span className="flex w-full items-center justify-between gap-4">
                                                <span className="flex items-center gap-3 text-left">
                                                    <span
                                                        className="flex size-7 shrink-0 items-center justify-center rounded-lg text-[10px] font-black text-black transition-all group-data-[state=open]:scale-110"
                                                        style={{ background: YELLOW }}
                                                    >
                                                        {String(i + 1).padStart(2, '0')}
                                                    </span>
                                                    <span className="text-sm font-semibold text-zinc-900">
                                                        {faq.question}
                                                    </span>
                                                </span>
                                                <span
                                                    className="flex size-6 shrink-0 items-center justify-center rounded-full transition-all group-data-[state=open]:rotate-45"
                                                    style={{ background: 'rgba(232,201,0,0.15)' }}
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" stroke={YELLOW} strokeWidth={2.5} className="size-3.5">
                                                        <path d="M12 5v14M5 12h14" strokeLinecap="round" />
                                                    </svg>
                                                </span>
                                            </span>
                                        </AccordionTrigger>
                                        <AccordionContent className="px-5 pb-5 text-sm leading-relaxed text-zinc-500">
                                            <div className="pl-10 border-l-2 ml-3.5" style={{ borderColor: YELLOW }}>
                                                <p className="pl-3">{faq.answer}</p>
                                            </div>
                                        </AccordionContent>
                                    </AccordionItem>
                                ))}
                            </Accordion>
                        </div>
                    </div>
                </div>
            </section>

            {/* ── CTA ── */}
            <section className="relative overflow-hidden px-6 py-20">
                <div className="pointer-events-none absolute inset-0">
                    <div
                        className="absolute inset-x-0 top-0 h-px"
                        style={{
                            background:
                                'linear-gradient(to right, transparent, rgba(232,201,0,0.5), transparent)',
                        }}
                    />
                    <div
                        className="absolute inset-x-0 bottom-0 h-px"
                        style={{
                            background:
                                'linear-gradient(to right, transparent, rgba(232,201,0,0.5), transparent)',
                        }}
                    />
                    <div
                        className="absolute top-1/2 left-1/2 size-[400px] -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl"
                        style={{
                            background:
                                'radial-gradient(circle, rgba(232,201,0,0.08) 0%, transparent 70%)',
                        }}
                    />
                </div>
                <div className="relative mx-auto max-w-xl text-center">
                    <h2 className="mb-3 text-3xl font-extrabold text-zinc-900">
                        Siap Pesan Sekarang?
                    </h2>
                    <p className="mb-8 text-zinc-500">
                        Kunjungi booth terdekat atau hubungi kami untuk
                        reservasi.
                    </p>
                    <a
                        href={ctaHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 rounded-full px-10 py-4 text-base font-bold text-black transition hover:-translate-y-0.5 hover:brightness-110 active:scale-95"
                        style={{
                            background: YELLOW,
                            boxShadow: `0 4px 32px rgba(232,201,0,0.35)`,
                        }}
                    >
                        Hubungi via WhatsApp
                        <ArrowRight />
                    </a>
                </div>
            </section>
        </HomeLayout>
    );
}
