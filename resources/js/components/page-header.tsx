import { Link } from '@inertiajs/react';

const YELLOW = '#E8C900';

type BreadcrumbItem = {
    label: string;
    href?: string;
};

type PageHeaderProps = {
    title: string;
    description?: string;
    breadcrumbs?: BreadcrumbItem[];
};

export default function PageHeader({
    title,
    description,
    breadcrumbs = [],
}: PageHeaderProps) {
    return (
        <section className="relative overflow-hidden bg-white px-8 pt-44 pb-20">
            {/* Dot grid */}
            <div
                className="pointer-events-none absolute inset-0"
                style={{
                    backgroundImage:
                        'radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px)',
                    backgroundSize: '28px 28px',
                }}
            />
            {/* Yellow blur — top right */}
            <div
                className="pointer-events-none absolute top-0 right-0 size-[400px] rounded-full blur-3xl"
                style={{
                    background:
                        'radial-gradient(circle, rgba(232,201,0,0.18) 0%, transparent 70%)',
                }}
            />
            {/* Yellow blur — bottom left */}
            <div
                className="pointer-events-none absolute bottom-0 left-0 size-72 rounded-full blur-3xl"
                style={{
                    background:
                        'radial-gradient(circle, rgba(232,201,0,0.10) 0%, transparent 70%)',
                }}
            />
            {/* Horizontal yellow line */}
            <div
                className="pointer-events-none absolute inset-x-0 top-1/2 h-px"
                style={{
                    background:
                        'linear-gradient(to right, transparent, rgba(232,201,0,0.3), transparent)',
                }}
            />

            <div className="relative mx-auto max-w-6xl">
                {/* Breadcrumb */}
                {breadcrumbs.length > 0 && (
                    <nav className="mb-4 flex items-center gap-2 text-sm text-zinc-500">
                        {breadcrumbs.map((crumb, i) => (
                            <span key={i} className="flex items-center gap-2">
                                {i > 0 && (
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth={2}
                                        className="size-3.5 shrink-0 text-zinc-300"
                                    >
                                        <path
                                            d="M9 18l6-6-6-6"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </svg>
                                )}
                                {crumb.href ? (
                                    <Link
                                        href={crumb.href}
                                        className="transition-colors hover:text-zinc-800"
                                    >
                                        {crumb.label}
                                    </Link>
                                ) : (
                                    <span
                                        className="font-semibold"
                                        style={{ color: YELLOW }}
                                    >
                                        {crumb.label}
                                    </span>
                                )}
                            </span>
                        ))}
                    </nav>
                )}

                <h1 className="text-4xl font-extrabold tracking-tight text-zinc-900 md:text-5xl">
                    {title}
                </h1>
                {description && (
                    <p className="mt-3 max-w-xl text-zinc-500">{description}</p>
                )}
            </div>
        </section>
    );
}
