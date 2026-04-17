import { Camera } from 'lucide-react';
import { useEffect, useState } from 'react';

interface TemplatePreview {
    id: number;
    name: string;
    thumbnail_path: string | null;
}

interface Props {
    branchName: string;
    siteName: string;
    logoUrl: string | null;
    templates: TemplatePreview[];
    onStart: () => void;
}

export default function LandingStep({
    branchName,
    siteName,
    logoUrl,
    templates,
    onStart,
}: Props) {
    const visibleTemplates = templates.filter(
        (template) => template.thumbnail_path,
    );
    const [activeIndex, setActiveIndex] = useState(0);

    useEffect(() => {
        if (visibleTemplates.length <= 1) {
            return;
        }

        const id = setInterval(() => {
            setActiveIndex(
                (current) => (current + 1) % visibleTemplates.length,
            );
        }, 2500);

        return () => clearInterval(id);
    }, [visibleTemplates.length]);

    const activeTemplate = visibleTemplates[activeIndex] ?? null;

    return (
        <div className="flex min-h-screen items-center justify-center px-10 py-12">
            <div className="grid w-full max-w-7xl gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <div className="flex flex-col justify-center gap-8">
                    <div className="relative flex items-center justify-start">
                        <div
                            className="absolute h-72 w-72 animate-ping rounded-full"
                            style={{
                                background: 'rgba(232,201,0,0.10)',
                                animationDuration: '3s',
                            }}
                        />
                        <div
                            className="absolute h-52 w-52 animate-ping rounded-full"
                            style={{
                                background: 'rgba(232,201,0,0.16)',
                                animationDuration: '2s',
                                animationDelay: '0.4s',
                            }}
                        />

                        <div
                            className="relative flex h-36 w-36 items-center justify-center rounded-full"
                            style={{
                                background: '#E8C900',
                                boxShadow: '0 0 60px rgba(232,201,0,0.30)',
                            }}
                        >
                            {logoUrl ? (
                                <img
                                    src={logoUrl}
                                    alt={siteName}
                                    className="h-20 w-20 rounded-full object-cover"
                                />
                            ) : (
                                <Camera
                                    className="h-16 w-16 text-black"
                                    strokeWidth={1.5}
                                />
                            )}
                        </div>
                    </div>

                    <div className="space-y-4">
                        <div className="inline-flex items-center rounded-full bg-white/60 px-4 py-2 text-sm font-medium text-zinc-500 backdrop-blur-sm">
                            Booth siap digunakan
                        </div>
                        <h1 className="max-w-2xl text-5xl font-bold tracking-tight text-zinc-900 lg:text-6xl">
                            {siteName}
                        </h1>
                        <p className="text-xl font-semibold text-zinc-600">
                            {branchName}
                        </p>
                        <p className="max-w-xl text-lg leading-relaxed text-zinc-500">
                            Pilih paket, bayar QRIS, tentukan frame favorit,
                            lalu ambil foto dengan preview frame langsung di
                            kamera.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-4">
                        <button
                            onClick={onStart}
                            className="group relative overflow-hidden rounded-full px-16 py-5 text-xl font-bold text-black shadow-lg transition-all duration-300 hover:scale-105 active:scale-95"
                            style={{
                                background: '#E8C900',
                                boxShadow: '0 8px 30px rgba(232,201,0,0.35)',
                            }}
                        >
                            Mulai Foto
                        </button>
                        <p className="text-sm text-zinc-400">
                            Sentuh layar untuk memulai sesi baru
                        </p>
                    </div>
                </div>

                <div className="relative flex min-h-[520px] items-center justify-center">
                    <div className="absolute inset-0 rounded-[2.5rem] bg-[radial-gradient(circle_at_top,_rgba(232,201,0,0.24),_transparent_58%)]" />
                    <div className="relative w-full max-w-md rounded-[2rem] border border-white/70 bg-white/80 p-5 backdrop-blur-md">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold tracking-[0.25em] text-zinc-400 uppercase">
                                    Attract Mode
                                </p>
                                <p className="mt-1 text-lg font-bold text-zinc-900">
                                    Preview Frame
                                </p>
                            </div>
                            <div className="rounded-full bg-zinc-900 px-3 py-1 text-xs font-semibold text-white">
                                {visibleTemplates.length > 0
                                    ? `${activeIndex + 1}/${visibleTemplates.length}`
                                    : 'Ready'}
                            </div>
                        </div>

                        <div className="relative aspect-[3/4] overflow-hidden rounded-[1.5rem] bg-zinc-100">
                            {activeTemplate?.thumbnail_path ? (
                                <img
                                    key={activeTemplate.id}
                                    src={activeTemplate.thumbnail_path}
                                    alt={activeTemplate.name}
                                    className="h-full w-full object-cover transition duration-700"
                                />
                            ) : (
                                <div className="flex h-full flex-col items-center justify-center gap-4 bg-[linear-gradient(180deg,_#f7f4d1,_#f4f4f0)] text-center">
                                    <div className="flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-md">
                                        <Camera className="h-10 w-10 text-zinc-900" />
                                    </div>
                                    <div>
                                        <p className="text-lg font-bold text-zinc-900">
                                            Siap untuk mulai
                                        </p>
                                        <p className="text-sm text-zinc-500">
                                            Frame pilihan akan tampil di sini
                                        </p>
                                    </div>
                                </div>
                            )}
                        </div>

                        <div className="mt-4 grid grid-cols-3 gap-3">
                            <div className="rounded-2xl bg-white/60 px-3 py-3 text-center">
                                <p className="text-xs text-zinc-400">Flow</p>
                                <p className="mt-1 text-sm font-semibold text-zinc-900">
                                    Frame dulu
                                </p>
                            </div>
                            <div className="rounded-2xl bg-white/60 px-3 py-3 text-center">
                                <p className="text-xs text-zinc-400">Capture</p>
                                <p className="mt-1 text-sm font-semibold text-zinc-900">
                                    Preview per foto
                                </p>
                            </div>
                            <div className="rounded-2xl bg-white/60 px-3 py-3 text-center">
                                <p className="text-xs text-zinc-400">Digital</p>
                                <p className="mt-1 text-sm font-semibold text-zinc-900">
                                    QR download
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
