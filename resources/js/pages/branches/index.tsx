import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/components/page-header';
import HomeLayout from '@/layouts/home-layout';

const YELLOW = '#E8C900';

type Branch = {
    id: number;
    name: string;
    code: string;
    address: string | null;
    phone: string | null;
    photo: string | null;
    photo_sessions_count: number;
    transactions_count: number;
};

function BranchCard({ branch }: { branch: Branch }) {
    return (
        <Link
            href={`/cabang/${branch.code}`}
            className="group flex flex-col overflow-hidden rounded-2xl bg-white transition-all duration-300 hover:-translate-y-0.5 sm:flex-row"
        >
            {/* Thumbnail */}
            <div className="relative h-48 shrink-0 overflow-hidden sm:h-auto sm:w-52">
                {branch.photo ? (
                    <img
                        src={branch.photo}
                        alt={branch.name}
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                ) : (
                    <div
                        className="flex size-full items-center justify-center"
                        style={{ backgroundColor: 'rgba(232,201,0,0.1)' }}
                    >
                        <span
                            className="select-none text-8xl font-extrabold leading-none sm:text-7xl"
                            style={{ color: YELLOW, opacity: 0.5 }}
                        >
                            {branch.name.slice(0, 1)}
                        </span>
                    </div>
                )}

                {/* Code chip */}
                <div className="absolute bottom-3 left-3">
                    <span className="rounded-lg bg-zinc-900/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-white backdrop-blur-sm">
                        {branch.code}
                    </span>
                </div>
            </div>

            {/* Content */}
            <div className="flex flex-1 flex-col justify-between gap-4 p-6">
                <div className="flex flex-col gap-3">
                    {/* Name + status */}
                    <div className="flex items-start justify-between gap-3">
                        <h3 className="text-lg font-bold text-zinc-900 leading-snug">
                            {branch.name}
                        </h3>
                        <span
                            className="mt-0.5 flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-zinc-950"
                            style={{ backgroundColor: YELLOW }}
                        >
                            <span className="relative flex size-1.5">
                                <span className="absolute inline-flex size-full animate-ping rounded-full bg-zinc-900 opacity-40" />
                                <span className="relative inline-flex size-1.5 rounded-full bg-zinc-900" />
                            </span>
                            Aktif
                        </span>
                    </div>

                    {/* Address */}
                    {branch.address && (
                        <p className="flex items-start gap-2 text-sm leading-relaxed text-zinc-500">
                            <svg className="mt-0.5 size-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6z" />
                                <circle cx="12" cy="8" r="2" />
                            </svg>
                            {branch.address}
                        </p>
                    )}
                </div>

                {/* Bottom row: stats + CTA */}
                <div className="flex items-center justify-between gap-4 pt-4">
                    <div className="flex flex-wrap items-center gap-4 text-xs text-zinc-400">
                        <span className="flex items-center gap-1.5">
                            <svg className="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" />
                                <circle cx="12" cy="13" r="4" />
                            </svg>
                            {branch.photo_sessions_count} sesi selesai
                        </span>
                        {branch.phone && (
                            <span className="flex items-center gap-1.5">
                                <svg className="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.01 21 3 13.99 3 5a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.25 1.01l-2.2 2.2z" />
                                </svg>
                                {branch.phone}
                            </span>
                        )}
                    </div>

                    <div
                        className="flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-xs font-bold text-zinc-950 transition-all duration-200 group-hover:gap-2.5"
                        style={{ backgroundColor: 'rgba(232,201,0,0.12)' }}
                    >
                        Lihat Cabang
                        <svg className="size-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </div>
                </div>
            </div>
        </Link>
    );
}

export default function BranchesIndex() {
    const { branches } = usePage<{ branches: Branch[] }>().props;
    const [search, setSearch] = useState('');

    const filtered = branches.filter(
        (b) =>
            b.name.toLowerCase().includes(search.toLowerCase()) ||
            (b.address ?? '').toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <HomeLayout title="Cabang — Philo Photobooth">
            <PageHeader
                title="Cabang Kami"
                description="Temukan booth terdekat di kotamu. Setiap cabang dirancang untuk memberi pengalaman photobooth yang cepat, rapi, dan nyaman."
                breadcrumbs={[
                    { label: 'Beranda', href: '/' },
                    { label: 'Cabang' },
                ]}
            />

            <section className="px-4 pt-10 pb-28 sm:px-6">
                <div className="mx-auto max-w-4xl space-y-6">

                    {/* Search */}
                    <div className="flex items-center gap-3">
                        <div className="relative flex-1">
                            <div className="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                                <svg className="size-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <circle cx="11" cy="11" r="8" />
                                    <path strokeLinecap="round" d="M21 21l-4.35-4.35" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari nama atau kota cabang..."
                                className="w-full rounded-2xl bg-white py-3.5 pr-5 pl-11 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2"
                                style={{ '--tw-ring-color': YELLOW } as React.CSSProperties}
                            />
                        </div>
                        <div className="hidden shrink-0 rounded-2xl bg-white px-5 py-3.5 text-sm font-semibold text-zinc-500 sm:block">
                            <span className="font-extrabold text-zinc-900">{filtered.length}</span> cabang
                        </div>
                    </div>

                    {/* List */}
                    {filtered.length === 0 ? (
                        <div className="flex flex-col items-center justify-center rounded-2xl bg-white py-24 text-center">
                            <div className="mb-4 text-4xl">📍</div>
                            <p className="font-bold text-zinc-700">
                                {search ? 'Cabang tidak ditemukan' : 'Belum ada cabang aktif'}
                            </p>
                            <p className="mt-1 text-sm text-zinc-400">
                                {search ? 'Coba kata kunci lain.' : 'Segera hadir di kota kamu.'}
                            </p>
                        </div>
                    ) : (
                        <div className="flex flex-col gap-4">
                            {filtered.map((branch) => (
                                <BranchCard key={branch.id} branch={branch} />
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </HomeLayout>
    );
}
