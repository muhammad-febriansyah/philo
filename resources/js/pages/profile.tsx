import { Link, usePage } from '@inertiajs/react';
import PageHeader from '@/components/page-header';
import HomeLayout from '@/layouts/home-layout';
import { dashboard, login } from '@/routes';

const YELLOW = '#E8C900';

type AboutSettings = {
    about_title?: string | null;
    about_tagline?: string | null;
    about_description?: string | null;
    about_vision?: string | null;
    about_mission?: string | null;
    about_founded_year?: string | null;
    about_total_sessions?: string | null;
    about_total_branches?: string | null;
    about_total_clients?: string | null;
    about_hero_image_path?: string | null;
};

const FEATURE_ITEMS = [
    {
        icon: (
            <svg
                className="size-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={1.8}
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M4 5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V5zM4 15a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4zm10 0a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1v-4z"
                />
            </svg>
        ),
        title: 'Template Fleksibel',
        description:
            'Pilihan frame yang mudah disesuaikan untuk wedding, brand event, wisuda, sampai private party.',
    },
    {
        icon: (
            <svg
                className="size-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={1.8}
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1zm12 0h2a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1zM5 20h2a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1z"
                />
            </svg>
        ),
        title: 'Pembayaran QRIS',
        description:
            'QRIS membuat alur transaksi singkat dan nyaman tanpa antre panjang di booth.',
    },
    {
        icon: (
            <svg
                className="size-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={1.8}
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M17.657 16.657 13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"
                />
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"
                />
            </svg>
        ),
        title: 'Booth Multi Cabang',
        description:
            'Pengelolaan lebih rapi untuk beberapa lokasi dengan pengalaman yang tetap konsisten.',
    },
    {
        icon: (
            <svg
                className="size-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                strokeWidth={1.8}
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"
                />
            </svg>
        ),
        title: 'Hasil Digital Instan',
        description:
            'Setelah sesi selesai, tamu bisa langsung mendapatkan file digital dengan alur yang simpel.',
    },
];

const HOW_STEPS = [
    {
        number: '01',
        title: 'Pilih Paket',
        description:
            'Pengunjung memilih jumlah foto dan format cetak yang paling cocok untuk momen mereka.',
    },
    {
        number: '02',
        title: 'Bayar Praktis',
        description:
            'Pembayaran dilakukan cepat lewat QRIS, sehingga alur tetap ringkas dan modern.',
    },
    {
        number: '03',
        title: 'Ambil Foto',
        description:
            'Kamera, filter, dan frame membantu sesi terasa seru tanpa membuat pengguna bingung.',
    },
    {
        number: '04',
        title: 'Cetak & Unduh',
        description:
            'Hasil foto siap dicetak dan versi digital bisa dibawa pulang dalam hitungan saat.',
    },
];

export default function ProfilePage() {
    const { auth, about } = usePage<{
        auth: { user: unknown };
        about: AboutSettings;
    }>().props;

    const ctaHref = auth.user ? dashboard().url : login().url;
    const ctaLabel = auth.user ? 'Buka Dashboard' : 'Mulai Sekarang';
    const heroImage = about.about_hero_image_path
        ? `/storage/${about.about_hero_image_path}`
        : null;
    const foundedYear = about.about_founded_year ?? '2020';
    const stats = [
        { label: 'Sesi Foto', value: about.about_total_sessions ?? '500+' },
        { label: 'Cabang Aktif', value: about.about_total_branches ?? '3+' },
        { label: 'Client & Event', value: about.about_total_clients ?? '120+' },
    ];

    return (
        <HomeLayout title="Profil">
            <PageHeader
                title="Profil"
                description="Kenali lebih dekat siapa kami, visi misi, dan cara kami bekerja untuk setiap momen spesialmu."
                breadcrumbs={[
                    { label: 'Beranda', href: '/' },
                    { label: 'Profil' },
                ]}
            />

            {/* ── HERO ── */}
            <section className="relative overflow-hidden px-4 py-16 sm:px-6 lg:py-24">
                {/* Decorative radial glow */}
                <div
                    className="pointer-events-none absolute top-0 left-1/2 h-[500px] w-[800px] -translate-x-1/2 -translate-y-1/2 rounded-full blur-3xl"
                    style={{ backgroundColor: 'rgba(232,201,0,0.07)' }}
                />

                <div className="relative mx-auto flex max-w-3xl flex-col items-center gap-8 text-center">
                    {/* Badge */}
                    <div className="inline-flex items-center gap-2 rounded-full border border-yellow-200 bg-yellow-50 px-4 py-1.5">
                        <span
                            className="size-1.5 rounded-full"
                            style={{ backgroundColor: YELLOW }}
                        />
                        <span className="text-[11px] font-bold tracking-widest text-yellow-700 uppercase">
                            Sejak {foundedYear}
                        </span>
                    </div>

                    {/* Headline */}
                    <h1 className="text-4xl leading-[1.1] font-extrabold tracking-tight text-zinc-900 sm:text-5xl lg:text-6xl">
                        {about.about_title || 'Tentang Kami'}
                    </h1>

                    {/* Tagline */}
                    <p className="max-w-xl text-base leading-relaxed text-zinc-500 sm:text-lg">
                        {about.about_tagline ||
                            'Kami merancang pengalaman photobooth yang modern, mudah dipakai, dan tetap terasa personal untuk setiap acara.'}
                    </p>

                    {/* Buttons */}
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href={ctaHref}
                            className="inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-bold text-zinc-950 shadow-sm transition hover:brightness-105 active:scale-95"
                            style={{ backgroundColor: YELLOW }}
                        >
                            {ctaLabel}
                            <svg
                                className="size-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                strokeWidth={2.5}
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                />
                            </svg>
                        </Link>
                        <a
                            href="#cara-kerja"
                            className="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-7 py-3 text-sm font-semibold text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50"
                        >
                            Cara Kerja
                        </a>
                    </div>

                    {/* Divider */}
                    <div className="w-full border-t border-zinc-100" />

                    {/* Stats row */}
                    <div className="flex w-full items-center justify-center divide-x divide-zinc-100">
                        {stats.map((item) => (
                            <div
                                key={item.label}
                                className="flex flex-1 flex-col items-center gap-1 px-4"
                            >
                                <span className="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
                                    {item.value}
                                </span>
                                <span className="text-[10px] font-semibold tracking-widest text-zinc-400 uppercase">
                                    {item.label}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Showcase image */}
                {heroImage && (
                    <div className="relative mx-auto mt-16 max-w-5xl px-4 sm:px-6">
                        {/* Glow behind image */}
                        <div
                            className="pointer-events-none absolute inset-x-0 -bottom-10 h-40 blur-3xl"
                            style={{ backgroundColor: 'rgba(232,201,0,0.12)' }}
                        />
                        <div className="relative overflow-hidden rounded-3xl shadow-2xl ring-1 ring-black/8">
                            <img
                                src={heroImage}
                                alt={about.about_title || 'Philo Photobooth'}
                                className="aspect-[16/7] w-full object-cover object-center"
                            />
                            {/* Bottom fade overlay with stat chips */}
                            <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent" />
                            <div className="absolute right-6 bottom-6 left-6 flex flex-wrap items-end justify-between gap-3">
                                <div className="flex flex-col gap-1">
                                    <p className="text-xs font-semibold tracking-widest text-white/60 uppercase">
                                        {about.about_title ||
                                            'Philo Photobooth'}
                                    </p>
                                    <p className="text-lg font-bold text-white sm:text-xl">
                                        {about.about_tagline ||
                                            'Abadikan momen, cetak kenangan.'}
                                    </p>
                                </div>
                                <div
                                    className="rounded-full px-5 py-2 text-xs font-bold text-zinc-950 shadow-lg"
                                    style={{ backgroundColor: YELLOW }}
                                >
                                    Sejak {foundedYear}
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </section>

            {/* ── TENTANG / VISI / MISI ── */}
            <section className="bg-zinc-50/60 px-4 py-16 sm:px-6 sm:py-20">
                <div className="mx-auto max-w-6xl">
                    <div className="mb-12 text-center">
                        <p className="mb-2 text-xs font-bold tracking-widest text-zinc-400 uppercase">
                            Mengenal Lebih Dekat
                        </p>
                        <h2 className="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
                            Cerita & Komitmen Kami
                        </h2>
                    </div>

                    <div className="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        {/* Deskripsi */}
                        <div className="flex flex-col gap-4 rounded-2xl bg-white p-7 shadow-md lg:col-span-2">
                            <div className="flex items-center gap-3">
                                <div
                                    className="flex size-9 items-center justify-center rounded-xl"
                                    style={{
                                        backgroundColor: 'rgba(232,201,0,0.15)',
                                    }}
                                >
                                    <svg
                                        className="size-4 text-zinc-700"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        strokeWidth={1.8}
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                        />
                                    </svg>
                                </div>
                                <h3 className="font-bold text-zinc-900">
                                    Filosofi Kami
                                </h3>
                            </div>
                            <div
                                className="prose prose-sm max-w-none prose-zinc"
                                dangerouslySetInnerHTML={{
                                    __html:
                                        about.about_description ||
                                        '<p>Philo Photobooth hadir untuk membantu setiap acara terasa lebih hidup lewat pengalaman foto yang modern, praktis, dan tetap punya sentuhan personal. Kami percaya bahwa momen terbaik tidak harus rumit untuk diabadikan. Karena itu, kami merancang alur yang ringkas, visual yang rapi, dan hasil yang langsung siap dibawa pulang.</p>',
                                }}
                            />
                        </div>

                        {/* Visi */}
                        <div className="relative flex flex-col justify-center gap-3 overflow-hidden rounded-2xl bg-zinc-900 p-7 shadow-sm">
                            <div
                                className="pointer-events-none absolute -top-10 -right-10 size-40 rounded-full blur-3xl"
                                style={{
                                    backgroundColor: 'rgba(232,201,0,0.2)',
                                }}
                            />
                            <p className="relative text-xs font-bold tracking-widest text-zinc-500 uppercase">
                                Visi
                            </p>
                            <div
                                className="prose prose-sm max-w-none font-medium prose-invert"
                                dangerouslySetInnerHTML={{
                                    __html:
                                        about.about_vision ||
                                        '<p>Menjadi pilihan photobooth modern yang dipercaya untuk menghadirkan kenangan visual yang kuat di berbagai kota.</p>',
                                }}
                            />
                        </div>

                        {/* Misi */}
                        <div className="rounded-2xl bg-white p-7 shadow-md lg:col-span-3">
                            <div className="mb-6 flex items-center gap-3">
                                <div
                                    className="flex size-9 items-center justify-center rounded-xl"
                                    style={{
                                        backgroundColor: 'rgba(232,201,0,0.15)',
                                    }}
                                >
                                    <svg
                                        className="size-4 text-zinc-700"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        strokeWidth={1.8}
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 0 0 1.946-.806 3.42 3.42 0 0 1 4.438 0 3.42 3.42 0 0 0 1.946.806 3.42 3.42 0 0 1 3.138 3.138 3.42 3.42 0 0 0 .806 1.946 3.42 3.42 0 0 1 0 4.438 3.42 3.42 0 0 0-.806 1.946 3.42 3.42 0 0 1-3.138 3.138 3.42 3.42 0 0 0-1.946.806 3.42 3.42 0 0 1-4.438 0 3.42 3.42 0 0 0-1.946-.806 3.42 3.42 0 0 1-3.138-3.138 3.42 3.42 0 0 0-.806-1.946 3.42 3.42 0 0 1 0-4.438 3.42 3.42 0 0 0 .806-1.946 3.42 3.42 0 0 1 3.138-3.138z"
                                        />
                                    </svg>
                                </div>
                                <h3 className="font-bold text-zinc-900">
                                    Misi
                                </h3>
                            </div>
                            <div
                                className="prose prose-sm max-w-none prose-zinc"
                                dangerouslySetInnerHTML={{
                                    __html:
                                        about.about_mission ??
                                        '<ul><li>Membuat pengalaman photobooth yang mudah digunakan siapa saja.</li><li>Menyediakan desain frame yang terasa segar dan relevan untuk berbagai acara.</li><li>Menjaga alur dari pembayaran sampai hasil akhir tetap cepat dan rapi.</li></ul>',
                                }}
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* ── FITUR UNGGULAN ── */}
            <section
                id="fitur-unggulan"
                className="px-4 py-16 sm:px-6 sm:py-20"
            >
                <div className="mx-auto max-w-6xl">
                    <div className="mb-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="mb-2 text-xs font-bold tracking-widest text-zinc-400 uppercase">
                                Standar Kualitas
                            </p>
                            <h2 className="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
                                Dibuat untuk operasional rapi
                            </h2>
                        </div>
                        <p className="max-w-sm text-sm leading-relaxed text-zinc-500 sm:text-right">
                            Fokus kami bukan hanya tampilan, tapi juga alur
                            booth yang ringkas dan nyaman dipakai.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {FEATURE_ITEMS.map((item, index) => (
                            <div
                                key={item.title}
                                className="group flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div
                                    className="flex size-10 items-center justify-center rounded-xl bg-zinc-50 text-zinc-400 transition-colors duration-300 group-hover:text-zinc-900"
                                    style={{
                                        backgroundColor: 'rgba(232,201,0,0)',
                                    }}
                                    onMouseEnter={(e) => {
                                        (
                                            e.currentTarget as HTMLElement
                                        ).style.backgroundColor =
                                            'rgba(232,201,0,0.12)';
                                    }}
                                    onMouseLeave={(e) => {
                                        (
                                            e.currentTarget as HTMLElement
                                        ).style.backgroundColor =
                                            'rgba(232,201,0,0)';
                                    }}
                                >
                                    {item.icon}
                                </div>
                                <div>
                                    <div className="mb-1 text-xs font-bold text-zinc-300">
                                        0{index + 1}
                                    </div>
                                    <h3 className="mb-2 font-bold text-zinc-900">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-zinc-500">
                                        {item.description}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* ── CARA KERJA ── */}
            <section id="cara-kerja" className="px-4 pb-20 sm:px-6 sm:pb-24">
                <div className="mx-auto max-w-6xl">
                    <div className="overflow-hidden rounded-3xl bg-zinc-900">
                        {/* Header */}
                        <div className="border-b border-zinc-800 px-7 py-10 sm:px-10">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p
                                        className="mb-2 text-xs font-bold tracking-widest uppercase"
                                        style={{ color: YELLOW }}
                                    >
                                        Cara Kerja
                                    </p>
                                    <h2 className="text-2xl font-extrabold text-white sm:text-3xl">
                                        Ringkas untuk tamu, nyaman untuk
                                        operator
                                    </h2>
                                </div>
                                <p className="max-w-xs text-sm leading-relaxed text-zinc-400 sm:text-right">
                                    Alur yang mudah dipahami bahkan oleh
                                    pengguna pertama kali.
                                </p>
                            </div>
                        </div>

                        {/* Steps */}
                        <div className="grid grid-cols-1 gap-px bg-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                            {HOW_STEPS.map((step) => (
                                <div
                                    key={step.number}
                                    className="flex flex-col gap-4 bg-zinc-900 p-7 sm:p-8"
                                >
                                    <span
                                        className="text-3xl font-extrabold"
                                        style={{ color: YELLOW }}
                                    >
                                        {step.number}
                                    </span>
                                    <div>
                                        <h3 className="mb-2 font-bold text-white">
                                            {step.title}
                                        </h3>
                                        <p className="text-sm leading-relaxed text-zinc-400">
                                            {step.description}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </section>
        </HomeLayout>
    );
}
