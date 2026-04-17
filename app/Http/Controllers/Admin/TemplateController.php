<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TemplateController extends Controller
{
    public function index(): View
    {
        return view('templates.index');
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
                    <a href="'.route('templates.edit', $template).'" class="btn btn-sm btn-warning waves-effect me-1">
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

        return view('templates.show', compact('template'));
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
