<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $packages = Package::query()
            ->with(['templates:id,name,print_size,photo_slots'])
            ->withCount(['templates', 'transactions as paid_transactions_count' => function ($q) {
                $q->where('status', 'paid');
            }])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->orderByDesc('is_active')
            ->orderBy('price')
            ->get();

        return view('packages.index', [
            'packages' => $packages,
            'filters' => [
                'q' => $request->string('q'),
                'status' => $request->string('status'),
                'size' => $request->string('size'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('packages.create', [
            'templates' => $this->activeTemplates(),
        ]);
    }

    /**
     * Get data for DataTables.
     */
    public function data(): JsonResponse
    {
        $query = Package::query()->select(['id', 'name', 'photo_count', 'print_size', 'print_copies', 'price', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no', fn (Package $package) => '')
            ->addColumn('price_formatted', function (Package $package) {
                return 'Rp '.number_format($package->price, 0, ',', '.');
            })
            ->addColumn('status', function (Package $package) {
                return $package->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('actions', function (Package $package) {
                return '<a href="'.route('packages.show', $package).'" class="btn btn-sm btn-info waves-effect me-1">
                            <i class="mdi mdi-eye me-1"></i> Detail
                        </a>
                        <a href="'.route('packages.edit', $package).'" class="btn btn-sm btn-warning waves-effect me-1">
                            <i class="mdi mdi-pencil me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete" 
                            data-name="'.e($package->name).'" 
                            data-url="'.route('packages.destroy', $package).'">
                            <i class="mdi mdi-trash-can me-1"></i> Hapus
                        </button>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackageRequest $request): JsonResponse|RedirectResponse
    {
        $templateIds = $request->input('template_ids', []);
        $derived = $this->deriveSpecsFromTemplates($templateIds);

        $package = Package::create([
            ...$request->safe()->except('template_ids'),
            'print_size' => $derived['print_size'],
            'photo_count' => $derived['photo_count'],
        ]);
        $package->templates()->sync($templateIds);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('packages.index')
                ->with('success', 'Paket foto berhasil ditambahkan.');
        }

        return response()->json([
            'message' => 'Paket foto berhasil ditambahkan.',
            'data' => $package,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Package $package): JsonResponse|View
    {
        $package->load(['templates:id,name,print_size,photo_slots,thumbnail_path,frame_path']);

        if (! $request->expectsJson()) {
            $stats = [
                'paid_count' => $package->transactions()->where('status', 'paid')->count(),
                'total_count' => $package->transactions()->count(),
                'revenue' => (int) $package->transactions()->where('status', 'paid')->sum('amount'),
                'pending_count' => $package->transactions()->where('status', 'pending')->count(),
                'last_sold_at' => optional($package->transactions()->where('status', 'paid')->latest('paid_at')->value('paid_at'))->toDateTimeString(),
            ];

            $recentTransactions = $package->transactions()
                ->with(['branch:id,name'])
                ->latest()
                ->limit(8)
                ->get(['id', 'order_id', 'branch_id', 'package_id', 'amount', 'status', 'paid_at', 'created_at']);

            return view('packages.show', compact('package', 'stats', 'recentTransactions'));
        }

        return response()->json($package);
    }

    public function edit(Package $package): View
    {
        $package->load('templates:id');

        return view('packages.edit', [
            'package' => $package,
            'templates' => $this->activeTemplates(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePackageRequest $request, Package $package): JsonResponse|RedirectResponse
    {
        $templateIds = $request->input('template_ids', []);
        $derived = $this->deriveSpecsFromTemplates($templateIds);

        $package->update([
            ...$request->safe()->except('template_ids'),
            'print_size' => $derived['print_size'],
            'photo_count' => $derived['photo_count'],
        ]);
        $package->templates()->sync($templateIds);

        if (! $request->expectsJson()) {
            return redirect()
                ->route('packages.index')
                ->with('success', 'Paket foto berhasil diperbarui.');
        }

        return response()->json([
            'message' => 'Paket foto berhasil diperbarui.',
            'data' => $package,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package): JsonResponse
    {
        $package->delete();

        return response()->json(['message' => 'Paket foto berhasil dihapus.']);
    }

    private function activeTemplates()
    {
        return Template::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'print_size', 'photo_slots', 'thumbnail_path', 'frame_path']);
    }

    /**
     * Derive print_size & photo_count from the first selected template.
     * All templates are validated to share the same size & slots upstream.
     *
     * @param  array<int, int|string>  $templateIds
     * @return array{print_size: string, photo_count: int}
     */
    private function deriveSpecsFromTemplates(array $templateIds): array
    {
        if ($templateIds === []) {
            return ['print_size' => '', 'photo_count' => 0];
        }

        $template = Template::query()
            ->whereIn('id', $templateIds)
            ->first(['print_size', 'photo_slots']);

        return [
            'print_size' => (string) ($template->print_size ?? ''),
            'photo_count' => (int) ($template->photo_slots ?? 0),
        ];
    }
}
