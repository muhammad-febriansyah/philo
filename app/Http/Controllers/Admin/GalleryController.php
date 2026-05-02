<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::query()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(6);

        return view('galleries.index', compact('galleries'));
    }

    public function data(): JsonResponse
    {
        $query = Gallery::query()->select(['id', 'title', 'description', 'image_path', 'sort_order', 'is_active']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image_preview', function (Gallery $gallery) {
                return '<img src="'.Storage::url($gallery->image_path).'" class="rounded" style="width:48px;height:48px;object-fit:cover;" alt="">';
            })
            ->addColumn('status', fn (Gallery $gallery) => $gallery->is_active
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Nonaktif</span>')
            ->addColumn('actions', function (Gallery $gallery) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect me-1 btn-edit"
                        data-id="'.$gallery->id.'"
                        data-title="'.e($gallery->title).'"
                        data-description="'.e($gallery->description).'"
                        data-image_url="'.Storage::url($gallery->image_path).'"
                        data-sort_order="'.$gallery->sort_order.'"
                        data-is_active="'.($gallery->is_active ? '1' : '0').'">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-id="'.$gallery->id.'"
                        data-name="'.e($gallery->title).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['image_preview', 'status', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.required' => 'Foto wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto tidak boleh melebihi 10 MB.',
            'image.uploaded' => 'Foto gagal diunggah. Pastikan ukuran file tidak melebihi 10 MB.',
        ]);

        $validated['image_path'] = $request->file('image')->store('galleries', 'public');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        unset($validated['image']);

        $gallery = Gallery::create($validated);

        return response()->json(['success' => true, 'data' => $gallery]);
    }

    public function update(Request $request, Gallery $gallery): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto tidak boleh melebihi 10 MB.',
            'image.uploaded' => 'Foto gagal diunggah. Pastikan ukuran file tidak melebihi 10 MB.',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($gallery->image_path);
            $validated['image_path'] = $request->file('image')->store('galleries', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        unset($validated['image']);

        $gallery->update($validated);

        return response()->json(['success' => true, 'data' => $gallery]);
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();

        return response()->json(['success' => true]);
    }
}
