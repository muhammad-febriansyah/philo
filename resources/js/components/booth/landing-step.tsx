import { Camera } from 'lucide-react';

interface Props {
    branchName: string;
    siteName: string;
    logoUrl: string | null;
    onStart: () => void;
}

export default function LandingStep({ branchName, siteName, logoUrl, onStart }: Props) {
    return (
        <div className="flex h-full flex-col items-center justify-center gap-8 px-8 text-center">
            {/* Rings */}
            <div className="relative flex items-center justify-center">
                <div
                    className="absolute h-72 w-72 animate-ping rounded-full"
                    style={{ background: 'rgba(245,250,12,0.06)', animationDuration: '3s' }}
                />
                <div
                    className="absolute h-52 w-52 animate-ping rounded-full"
                    style={{ background: 'rgba(245,250,12,0.10)', animationDuration: '2s', animationDelay: '0.4s' }}
                />

                <div
                    className="relative flex h-36 w-36 items-center justify-center rounded-full shadow-2xl"
                    style={{ background: '#F5FA0C', boxShadow: '0 0 60px rgba(245,250,12,0.35)' }}
                >
                    {logoUrl ? (
                        <img src={logoUrl} alt={siteName} className="h-20 w-20 rounded-full object-cover" />
                    ) : (
                        <Camera className="h-16 w-16 text-black" strokeWidth={1.5} />
                    )}
                </div>
            </div>

            {/* Text */}
            <div className="space-y-3">
                <h1 className="text-5xl font-bold tracking-tight text-white">{siteName}</h1>
                <p className="text-xl font-semibold" style={{ color: '#F5FA0C' }}>{branchName}</p>
                <p className="text-zinc-500">Abadikan momen terbaik Anda</p>
            </div>

            {/* CTA */}
            <button
                onClick={onStart}
                className="group relative mt-4 overflow-hidden rounded-full px-16 py-5 text-xl font-bold text-black shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95"
                style={{ background: '#F5FA0C', boxShadow: '0 0 40px rgba(245,250,12,0.30)' }}
            >
                Mulai Foto
            </button>

            <p className="text-sm text-zinc-700">Sentuh tombol untuk memulai</p>
        </div>
    );
}
