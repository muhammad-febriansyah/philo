<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('packages.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data(): JsonResponse
    {
        $query = Package::query()->select(['id', 'name', 'photo_count', 'print_size', 'price', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no', fn (Package $package) => '')
            ->addColumn('price_formatted', function (Package $package) {
                return 'Rp ' . number_format($package->price, 0, ',', '.');
            })
            ->addColumn('status', function (Package $package) {
                return $package->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('actions', function (Package $package) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect me-1 btn-detail" data-id="'.$package->id.'">
                            <i class="mdi mdi-eye me-1"></i> Detail
                        </button>
                        <button type="button" class="btn btn-sm btn-warning waves-effect me-1 btn-edit" data-id="'.$package->id.'">
                            <i class="mdi mdi-pencil me-1"></i> Edit
                        </button>
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
    public function store(StorePackageRequest $request): JsonResponse
    {
        $package = Package::create($request->validated());

        return response()->json([
            'message' => 'Paket foto berhasil ditambahkan.',
            'data' => $package
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package): JsonResponse
    {
        return response()->json($package);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePackageRequest $request, Package $package): JsonResponse
    {
        $package->update($request->validated());

        return response()->json([
            'message' => 'Paket foto berhasil diperbarui.',
            'data' => $package
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
}
