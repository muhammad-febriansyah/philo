<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FeatureController extends Controller
{
    public function index(): View
    {
        return view('features.index');
    }

    public function data(): JsonResponse
    {
        $query = Feature::query()->select(['id', 'icon', 'title', 'description', 'sort_order', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no', fn (Feature $feature) => '')
            ->addColumn('icon_preview', fn (Feature $feature) => '<i class="'.e($feature->icon).' fs-4"></i>')
            ->addColumn('status', fn (Feature $feature) => $feature->is_active
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Nonaktif</span>')
            ->addColumn('actions', function (Feature $feature) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect me-1 btn-edit"
                        data-id="'.$feature->id.'"
                        data-icon="'.e($feature->icon).'"
                        data-title="'.e($feature->title).'"
                        data-description="'.e($feature->description).'"
                        data-sort_order="'.$feature->sort_order.'"
                        data-is_active="'.($feature->is_active ? '1' : '0').'">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-id="'.$feature->id.'"
                        data-name="'.e($feature->title).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['icon_preview', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'icon'        => ['required', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $feature = Feature::create($validated);

        return response()->json(['success' => true, 'data' => $feature]);
    }

    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validated = $request->validate([
            'icon'        => ['required', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $feature->update($validated);

        return response()->json(['success' => true, 'data' => $feature]);
    }

    public function destroy(Feature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json(['success' => true]);
    }
}
