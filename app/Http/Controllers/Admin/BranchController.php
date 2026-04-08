<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('branches.index');
    }

    public function data(): JsonResponse
    {
        $query = Branch::query()->select(['id', 'name', 'code', 'phone', 'address', 'photo', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no', fn (Branch $branch) => '')
            ->addColumn('photo', function (Branch $branch) {
                if ($branch->photo) {
                    return '<img src="'.Storage::url($branch->photo).'" class="rounded" style="width:40px;height:40px;object-fit:cover;" alt="">';
                }

                return '<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="mdi mdi-store text-white fs-5"></i></div>';
            })
            ->addColumn('status', function (Branch $branch) {
                return $branch->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('actions', function (Branch $branch) {
                return '<a href="'.route('branches.edit', $branch).'" class="btn btn-sm btn-info waves-effect me-1">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-name="'.e($branch->name).'"
                        data-url="'.route('branches.destroy', $branch).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['photo', 'status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('branches', 'public');
        }

        Branch::create($data);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($branch->photo) {
                Storage::disk('public')->delete($branch->photo);
            }
            $data['photo'] = $request->file('photo')->store('branches', 'public');
        } elseif ($request->input('remove_photo') === '1' && $branch->photo) {
            Storage::disk('public')->delete($branch->photo);
            $data['photo'] = null;
        } else {
            unset($data['photo']);
        }

        $branch->update($data);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch): JsonResponse
    {
        if ($branch->photo) {
            Storage::disk('public')->delete($branch->photo);
        }

        $branch->delete();

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }
}
