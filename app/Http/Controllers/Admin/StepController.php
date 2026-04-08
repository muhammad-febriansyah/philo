<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Step;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StepController extends Controller
{
    public function index(): View
    {
        return view('steps.index');
    }

    public function data(): JsonResponse
    {
        $query = Step::query()->select(['id', 'number', 'title', 'description', 'sort_order', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no', fn (Step $step) => '')
            ->addColumn('status', fn (Step $step) => $step->is_active
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Nonaktif</span>')
            ->addColumn('actions', function (Step $step) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect me-1 btn-edit"
                        data-id="'.$step->id.'"
                        data-number="'.e($step->number).'"
                        data-title="'.e($step->title).'"
                        data-description="'.e($step->description).'"
                        data-sort_order="'.$step->sort_order.'"
                        data-is_active="'.($step->is_active ? '1' : '0').'">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-id="'.$step->id.'"
                        data-name="'.e($step->title).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'      => ['required', 'string', 'max:10'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $step = Step::create($validated);

        return response()->json(['success' => true, 'data' => $step]);
    }

    public function update(Request $request, Step $step): JsonResponse
    {
        $validated = $request->validate([
            'number'      => ['required', 'string', 'max:10'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $step->update($validated);

        return response()->json(['success' => true, 'data' => $step]);
    }

    public function destroy(Step $step): JsonResponse
    {
        $step->delete();

        return response()->json(['success' => true]);
    }
}
