<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Yajra\DataTables\Facades\DataTables;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $base = Template::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $q->where('name', 'like', $term);
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->when($request->filled('size'), function ($q) use ($request) {
                $q->where('print_size', $request->string('size')->toString());
            });

        $templates = (clone $base)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $counts = [
            'all' => Template::count(),
            'active' => Template::where('is_active', true)->count(),
            'inactive' => Template::where('is_active', false)->count(),
            'sizes' => Template::selectRaw('print_size, COUNT(*) as total')
                ->groupBy('print_size')
                ->pluck('total', 'print_size')
                ->all(),
        ];

        return view('templates.index', [
            'templates' => $templates,
            'counts' => $counts,
            'filters' => [
                'q' => (string) $request->string('q'),
                'status' => (string) $request->string('status'),
                'size' => (string) $request->string('size'),
            ],
        ]);
    }

    public function data(): JsonResponse
    {
        $query = Template::query()->select(['id', 'name', 'thumbnail_path', 'frame_path', 'print_size', 'photo_slots', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('thumbnail_html', function (Template $template) {
                $src = $template->thumbnail_path
                    ? Storage::url($template->thumbnail_path)
                    : ($template->frame_path ? Storage::url($template->frame_path) : null);

                if ($src) {
                    return '<img src="'.$src.'" class="rounded" style="width:50px;height:50px;object-fit:contain;background:#f8f9fa;" alt="">';
                }

                return '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="mdi mdi-image-frame text-muted fs-5"></i></div>';
            })
            ->addColumn('status', function (Template $template) {
                return $template->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-danger">Nonaktif</span>';
            })
            ->addColumn('actions', function (Template $template) {
                return '
                    <a href="'.route('templates.show', $template).'" class="btn btn-sm btn-info waves-effect me-1">
                        <i class="mdi mdi-eye me-1"></i> Detail
                    </a>
                    <a href="'.route('templates.builder.edit', $template).'" class="btn btn-sm btn-warning waves-effect me-1">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-name="'.e($template->name).'"
                        data-url="'.route('templates.destroy', $template).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['thumbnail_html', 'status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('templates.create');
    }

    public function builder(): InertiaResponse
    {
        return Inertia::render('admin/templates/builder', [
            'template' => null,
            'printSizes' => $this->printSizeOptions(),
        ]);
    }

    public function builderEdit(Template $template): InertiaResponse
    {
        return Inertia::render('admin/templates/builder', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'print_size' => $template->print_size,
                'photo_slots' => $template->photo_slots,
                'slot_positions' => $template->slot_positions,
                'is_active' => $template->is_active,
                'frame_url' => $template->frame_path ? Storage::url($template->frame_path) : null,
            ],
            'printSizes' => $this->printSizeOptions(),
        ]);
    }

    /**
     * @return array<int, array{value: string, label: string, ratio: float, dimensions: string}>
     */
    private function printSizeOptions(): array
    {
        return [
            ['value' => 'strip', 'label' => 'Strip', 'ratio' => 1 / 3, 'dimensions' => '2" × 6"'],
            ['value' => '4R', 'label' => '4R', 'ratio' => 4 / 6, 'dimensions' => '4" × 6"'],
            ['value' => 'A4', 'label' => 'A4', 'ratio' => 210 / 297, 'dimensions' => '210 × 297mm'],
            ['value' => 'A3', 'label' => 'A3', 'ratio' => 297 / 420, 'dimensions' => '297 × 420mm'],
        ];
    }

    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['thumbnail']);
        $data['is_active'] = $request->boolean('is_active', true);

        $data['slot_positions'] = $request->filled('slot_positions')
            ? json_decode($request->input('slot_positions'), true) ?? []
            : [];

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('templates/thumbnails', 'public');
            $data['thumbnail_path'] = $path;
            $data['frame_path'] = $path;
        }

        Template::create($data);

        return redirect()->route('templates.index')->with('success', 'Template berhasil ditambahkan.');
    }

    public function show(Template $template): View
    {
        $template->frame_url = $template->frame_path ? Storage::url($template->frame_path) : null;
        $template->thumbnail_url = $template->thumbnail_path ? Storage::url($template->thumbnail_path) : null;

        $template->load(['packages:id,name,price,is_active,print_copies']);

        $stats = [
            'sessions_total' => $template->photoSessions()->count(),
            'sessions_completed' => $template->photoSessions()->where('status', 'completed')->count(),
            'packages_count' => $template->packages->count(),
            'last_used_at' => $template->photoSessions()->latest('created_at')->value('created_at'),
        ];

        return view('templates.show', compact('template', 'stats'));
    }

    public function edit(Template $template): View
    {
        $template->frame_url = $template->frame_path ? Storage::url($template->frame_path) : null;
        $template->thumbnail_url = $template->thumbnail_path ? Storage::url($template->thumbnail_path) : null;

        return view('templates.edit', compact('template'));
    }

    public function update(UpdateTemplateRequest $request, Template $template): RedirectResponse
    {
        $data = $request->safe()->except(['thumbnail']);
        $data['is_active'] = $request->boolean('is_active', true);

        $data['slot_positions'] = $request->filled('slot_positions')
            ? json_decode($request->input('slot_positions'), true) ?? []
            : [];

        if ($request->hasFile('thumbnail')) {
            if ($template->thumbnail_path) {
                Storage::disk('public')->delete($template->thumbnail_path);
            }
            $path = $request->file('thumbnail')->store('templates/thumbnails', 'public');
            $data['thumbnail_path'] = $path;
            $data['frame_path'] = $path;
        } elseif ($request->input('remove_thumbnail') === '1' && $template->thumbnail_path) {
            Storage::disk('public')->delete($template->thumbnail_path);
            $data['thumbnail_path'] = null;
            $data['frame_path'] = null;
        } else {
            unset($data['thumbnail_path'], $data['frame_path']);
        }

        $template->update($data);

        return redirect()->route('templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Template $template): JsonResponse
    {
        if ($template->frame_path) {
            Storage::disk('public')->delete($template->frame_path);
        }
        if ($template->thumbnail_path) {
            Storage::disk('public')->delete($template->thumbnail_path);
        }

        $template->delete();

        return response()->json(['message' => 'Template berhasil dihapus.']);
    }
}
