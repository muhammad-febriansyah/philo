<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkVoucherRequest;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Models\Branch;
use App\Models\Package;
use App\Models\Voucher;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total' => Voucher::count(),
            'active' => Voucher::active()->count(),
            'redeemed' => (int) Voucher::sum('uses_count'),
            'auto_next_visit' => Voucher::where('source', Voucher::SOURCE_AUTO_NEXT_VISIT)->count(),
        ];

        return view('vouchers.index', [
            'stats' => $stats,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'type' => $request->string('type')->toString(),
                'source' => $request->string('source')->toString(),
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = Voucher::query()
            ->select(['id', 'code', 'name', 'type', 'value', 'max_uses', 'uses_count', 'min_purchase', 'valid_from', 'valid_until', 'is_active', 'source', 'created_at'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')->toString()).'%';
                $q->where(function ($w) use ($term) {
                    $w->where('code', 'like', $term)->orWhere('name', 'like', $term);
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->string('source')->toString()))
            ->when($request->string('status')->toString() === 'active', fn ($q) => $q->active())
            ->when($request->string('status')->toString() === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->string('status')->toString() === 'expired', fn ($q) => $q->whereNotNull('valid_until')->where('valid_until', '<', now()))
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('select', function (Voucher $v) {
                return '<input type="checkbox" class="form-check-input voucher-select" value="'.$v->id.'" aria-label="Pilih voucher '.e($v->code).'">';
            })
            ->addColumn('value_formatted', function (Voucher $v) {
                return $v->type === Voucher::TYPE_PERCENTAGE
                    ? rtrim(rtrim(number_format((float) $v->value, 2, '.', ''), '0'), '.').'%'
                    : 'Rp '.number_format((float) $v->value, 0, ',', '.');
            })
            ->addColumn('uses_text', function (Voucher $v) {
                return $v->max_uses
                    ? $v->uses_count.' / '.$v->max_uses
                    : $v->uses_count.' / ∞';
            })
            ->addColumn('validity', function (Voucher $v) {
                $from = $v->valid_from ? $v->valid_from->format('d M Y') : '—';
                $until = $v->valid_until ? $v->valid_until->format('d M Y') : '∞';

                return $from.' → '.$until;
            })
            ->addColumn('status_badge', function (Voucher $v) {
                $label = $v->statusLabel();
                $color = match ($label) {
                    'Aktif' => 'success',
                    'Nonaktif' => 'secondary',
                    'Kedaluwarsa' => 'danger',
                    'Belum Aktif' => 'info',
                    'Habis Terpakai' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-'.$color.'">'.$label.'</span>';
            })
            ->addColumn('source_badge', function (Voucher $v) {
                $map = [
                    Voucher::SOURCE_MANUAL => ['label' => 'Manual', 'color' => 'light text-dark'],
                    Voucher::SOURCE_BULK => ['label' => 'Bulk', 'color' => 'info'],
                    Voucher::SOURCE_AUTO_NEXT_VISIT => ['label' => 'Auto Next-Visit', 'color' => 'primary'],
                ];
                $cfg = $map[$v->source] ?? $map[Voucher::SOURCE_MANUAL];

                return '<span class="badge bg-'.$cfg['color'].'">'.$cfg['label'].'</span>';
            })
            ->addColumn('actions', function (Voucher $v) {
                $copyBtn = '<button type="button" class="btn btn-sm btn-light btn-copy me-1" data-code="'.e($v->code).'" title="Salin kode">
                    <i class="fas fa-copy"></i>
                </button>';
                $printBtn = '<a href="'.route('vouchers.print', $v).'" target="_blank" rel="noopener" class="btn btn-sm btn-info me-1" title="Print thermal">
                    <i class="fas fa-print"></i>
                </a>';
                $editBtn = '<a href="'.route('vouchers.edit', $v).'" class="btn btn-sm btn-warning me-1" title="Edit">
                    <i class="fas fa-pen"></i>
                </a>';
                $delBtn = '<button type="button" class="btn btn-sm btn-danger btn-delete" data-name="'.e($v->code).'" data-url="'.route('vouchers.destroy', $v).'" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>';

                return $copyBtn.$printBtn.$editBtn.$delBtn;
            })
            ->rawColumns(['select', 'status_badge', 'source_badge', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('vouchers.create', $this->formData() + [
            'voucher' => null,
            'suggestedCode' => Voucher::generateUniqueCode('', 8),
        ]);
    }

    public function store(StoreVoucherRequest $request): JsonResponse|RedirectResponse
    {
        Voucher::create([
            ...$request->validated(),
            'source' => Voucher::SOURCE_MANUAL,
            'created_by' => $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Voucher berhasil dibuat.',
                'redirect' => route('vouchers.index'),
            ]);
        }

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('vouchers.edit', $this->formData() + [
            'voucher' => $voucher,
        ]);
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher): JsonResponse|RedirectResponse
    {
        $voucher->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Voucher berhasil diperbarui.',
                'redirect' => route('vouchers.index'),
            ]);
        }

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): JsonResponse
    {
        $voucher->delete();

        return response()->json(['message' => 'Voucher berhasil dihapus.']);
    }

    public function print(Voucher $voucher): View
    {
        return view('vouchers.print', [
            'vouchers' => collect([$this->decoratePrintableVoucher($voucher)]),
            'printTitle' => 'Print Voucher Thermal',
        ]);
    }

    public function bulkPrint(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'voucher_ids' => ['required', 'array', 'min:1'],
            'voucher_ids.*' => ['integer', 'exists:vouchers,id'],
        ], [
            'voucher_ids.required' => 'Pilih minimal satu voucher untuk dicetak.',
        ]);

        $vouchers = Voucher::query()
            ->whereIn('id', $validated['voucher_ids'])
            ->orderBy('id')
            ->get()
            ->map(fn (Voucher $voucher) => $this->decoratePrintableVoucher($voucher));

        if ($vouchers->isEmpty()) {
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Voucher yang dipilih tidak ditemukan.');
        }

        return view('vouchers.print', [
            'vouchers' => $vouchers,
            'printTitle' => 'Bulk Print Voucher Thermal',
        ]);
    }

    public function bulkCreate(): View
    {
        return view('vouchers.bulk', $this->formData() + [
            'suggestedPrefix' => 'PROMO-',
        ]);
    }

    public function bulkStore(BulkVoucherRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $created = 0;

        DB::transaction(function () use ($data, $request, &$created) {
            for ($i = 0; $i < $data['quantity']; $i++) {
                Voucher::create([
                    'code' => Voucher::generateUniqueCode($data['prefix'] ?? '', 6),
                    'name' => $data['name'] ?? null,
                    'type' => $data['type'],
                    'value' => $data['value'],
                    'max_uses' => $data['max_uses'] ?? 1,
                    'min_purchase' => $data['min_purchase'] ?? null,
                    'valid_from' => $data['valid_from'] ?? null,
                    'valid_until' => $data['valid_until'] ?? null,
                    'applicable_packages' => $data['applicable_packages'] ?? null,
                    'applicable_branches' => $data['applicable_branches'] ?? null,
                    'is_active' => true,
                    'source' => Voucher::SOURCE_BULK,
                    'created_by' => $request->user()->id,
                ]);
                $created++;
            }
        });

        return redirect()
            ->route('vouchers.index')
            ->with('success', $created.' voucher berhasil di-generate.');
    }

    /**
     * @return array{packages: Collection, branches: Collection}
     */
    private function formData(): array
    {
        return [
            'packages' => Package::orderBy('name')->get(['id', 'name', 'price']),
            'branches' => Branch::orderBy('name')->get(['id', 'name', 'code']),
        ];
    }

    private function decoratePrintableVoucher(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name ?: 'Voucher Diskon',
            'type' => $voucher->type,
            'value_formatted' => $voucher->type === Voucher::TYPE_PERCENTAGE
                ? rtrim(rtrim(number_format((float) $voucher->value, 2, '.', ''), '0'), '.').'%'
                : 'Rp '.number_format((float) $voucher->value, 0, ',', '.'),
            'min_purchase_formatted' => $voucher->min_purchase !== null
                ? 'Rp '.number_format((float) $voucher->min_purchase, 0, ',', '.')
                : 'Tanpa minimum',
            'validity' => trim(($voucher->valid_from ? $voucher->valid_from->format('d/m/Y') : 'Kapan saja').' - '.($voucher->valid_until ? $voucher->valid_until->format('d/m/Y') : 'Tanpa batas')),
            'status_label' => $voucher->statusLabel(),
            'qr_svg' => $this->generateVoucherQrSvgDataUri($voucher),
        ];
    }

    private function generateVoucherQrSvgDataUri(Voucher $voucher): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(220, 0, null, null, Fill::uniformColor(
                    new Rgb(255, 255, 255),
                    new Rgb(12, 12, 12),
                )),
                new SvgImageBackEnd
            )
        ))->writeString($voucher->code);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
