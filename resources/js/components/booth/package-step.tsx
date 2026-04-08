import { ArrowLeft, Camera, Printer, Star } from 'lucide-react';
import { useState } from 'react';

interface Package {
    id: number;
    name: string;
    description: string | null;
    photo_count: number;
    print_size: string;
    price: number;
}

interface Props {
    packages: Package[];
    onSelect: (pkg: Package) => void;
    onBack: () => void;
    loading: boolean;
}

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}

function PhotoPreview({ count, isSelected }: { count: number; isSelected: boolean }) {
    const dim = isSelected ? 'rgba(245,250,12,0.15)' : 'rgba(255,255,255,0.07)';

    if (count === 2) {
        return (
            <div className="flex gap-2 px-2">
                {[0, 1].map((i) => (
                    <div
                        key={i}
                        className="flex-1 rounded-xl"
                        style={{
                            aspectRatio: '2/3',
                            background: dim,
                            border: `1px solid ${isSelected ? 'rgba(245,250,12,0.2)' : 'rgba(255,255,255,0.06)'}`,
                        }}
                    />
                ))}
            </div>
        );
    }

    if (count === 4) {
        return (
            <div className="grid grid-cols-2 gap-2 px-2">
                {[0, 1, 2, 3].map((i) => (
                    <div
                        key={i}
                        className="rounded-xl"
                        style={{
                            aspectRatio: '1',
                            background: dim,
                            border: `1px solid ${isSelected ? 'rgba(245,250,12,0.2)' : 'rgba(255,255,255,0.06)'}`,
                        }}
                    />
                ))}
            </div>
        );
    }

    return (
        <div className="grid grid-cols-3 gap-2 px-2">
            {[0, 1, 2, 3, 4, 5].map((i) => (
                <div
                    key={i}
                    className="rounded-lg"
                    style={{
                        aspectRatio: '1',
                        background: dim,
                        border: `1px solid ${isSelected ? 'rgba(245,250,12,0.2)' : 'rgba(255,255,255,0.06)'}`,
                    }}
                />
            ))}
        </div>
    );

}

export default function PackageStep({ packages, onSelect, onBack, loading }: Props) {
    const [selected, setSelected] = useState<Package | null>(null);

    return (
        <div className="flex h-full flex-col px-10 py-8">
            {/* Header */}
            <div className="mb-8 flex items-center gap-4">
                <button
                    onClick={onBack}
                    className="flex h-11 w-11 items-center justify-center rounded-full border border-white/10 text-white transition hover:bg-white/10 active:scale-95"
                >
                    <ArrowLeft className="h-5 w-5" />
                </button>
                <div>
                    <h2 className="text-3xl font-bold text-white">Pilih Paket</h2>
                    <p className="text-zinc-500">Pilih paket foto yang sesuai</p>
                </div>
            </div>

            {/* Package grid */}
            <div className="grid flex-1 grid-cols-3 gap-5 overflow-auto pt-4">
                {packages.map((pkg, i) => {
                    const isSelected = selected?.id === pkg.id;
                    const isPopular = i === 1;

                    return (
                        <button
                            key={pkg.id}
                            onClick={() => setSelected(pkg)}
                            className="relative flex flex-col rounded-3xl border-2 p-6 text-left transition-all duration-200 active:scale-95"
                            style={
                                isSelected
                                    ? { borderColor: '#F5FA0C', background: 'rgba(245,250,12,0.06)' }
                                    : { borderColor: 'rgba(255,255,255,0.08)', background: 'rgba(255,255,255,0.03)' }
                            }
                        >
                            {isPopular && (
                                <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                                    <span className="flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold text-black" style={{ background: '#F5FA0C' }}>
                                        <Star className="h-3 w-3 fill-black" /> Popular
                                    </span>
                                </div>
                            )}

                            {/* Top: icon + name + description */}
                            <div className="flex items-start gap-3 mb-5">
                                <div
                                    className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                                    style={isSelected ? { background: '#F5FA0C' } : { background: 'rgba(255,255,255,0.08)' }}
                                >
                                    <Camera className={`h-6 w-6 ${isSelected ? 'text-black' : 'text-zinc-400'}`} />
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-white leading-tight">{pkg.name}</h3>
                                    {pkg.description && <p className="mt-0.5 text-xs text-zinc-500 leading-snug">{pkg.description}</p>}
                                </div>
                            </div>

                            {/* Photo preview */}
                            <div className="flex-1 flex items-center justify-center py-2">
                                <div className="w-full">
                                    <PhotoPreview count={pkg.photo_count} isSelected={isSelected} />
                                </div>
                            </div>

                            {/* Footer */}
                            <div className="mt-5 space-y-1.5 border-t pt-4" style={{ borderColor: isSelected ? 'rgba(245,250,12,0.15)' : 'rgba(255,255,255,0.06)' }}>
                                <div className="flex items-center gap-2 text-sm text-zinc-400">
                                    <Camera className="h-3.5 w-3.5" style={{ color: '#F5FA0C' }} />
                                    <span>{pkg.photo_count} foto</span>
                                </div>
                                <div className="flex items-center gap-2 text-sm text-zinc-400">
                                    <Printer className="h-3.5 w-3.5 text-white/40" />
                                    <span>Cetak {pkg.print_size}</span>
                                </div>
                                <div className="pt-2 text-2xl font-bold text-white">{formatPrice(pkg.price)}</div>
                            </div>

                            {isSelected && (
                                <div className="absolute right-4 top-4 flex h-7 w-7 items-center justify-center rounded-full" style={{ background: '#F5FA0C' }}>
                                    <span className="text-sm font-bold text-black">✓</span>
                                </div>
                            )}
                        </button>
                    );
                })}
            </div>

            {/* Continue */}
            <div className="mt-6">
                <button
                    onClick={() => selected && onSelect(selected)}
                    disabled={!selected || loading}
                    className="w-full rounded-2xl py-5 text-xl font-bold text-black shadow-lg transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-30 active:scale-99"
                    style={{ background: '#F5FA0C', boxShadow: selected ? '0 0 30px rgba(245,250,12,0.25)' : undefined }}
                >
                    {loading ? 'Memproses...' : 'Lanjut ke Pembayaran →'}
                </button>
            </div>
        </div>
    );
}
