<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('faqs.index');
    }

    public function data(): JsonResponse
    {
        $query = Faq::query()->select(['id', 'question', 'answer', 'sort_order', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status', fn (Faq $faq) => $faq->is_active
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Nonaktif</span>')
            ->addColumn('actions', function (Faq $faq) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect me-1 btn-edit"
                        data-id="'.$faq->id.'"
                        data-question="'.e($faq->question).'"
                        data-answer="'.e($faq->answer).'"
                        data-sort_order="'.$faq->sort_order.'"
                        data-is_active="'.($faq->is_active ? '1' : '0').'">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-id="'.$faq->id.'"
                        data-name="'.e($faq->question).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:1200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $faq = Faq::create($validated);

        return response()->json(['success' => true, 'data' => $faq]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:1200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $faq->update($validated);

        return response()->json(['success' => true, 'data' => $faq]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json(['success' => true]);
    }
}
