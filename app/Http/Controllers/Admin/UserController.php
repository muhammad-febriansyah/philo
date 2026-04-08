<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index');
    }

    public function data(): JsonResponse
    {
        $query = User::with('branch')->select(['id', 'name', 'email', 'role', 'phone', 'avatar_path', 'branch_id', 'created_at']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('avatar', function (User $user) {
                if ($user->avatar_path) {
                    return '<img src="'.Storage::url($user->avatar_path).'" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;" alt="">';
                }

                return '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="mdi mdi-account text-white fs-5"></i></div>';
            })
            ->addColumn('role_badge', function (User $user) {
                return match ($user->role) {
                    'admin' => '<span class="badge bg-primary">Admin</span>',
                    'cabang' => '<span class="badge bg-warning">Cabang</span>',
                    default => '<span class="badge bg-info">Operator</span>',
                };
            })
            ->addColumn('branch_name', fn (User $user) => e($user->branch?->name ?? '-'))
            ->addColumn('actions', function (User $user) {
                return '<a href="'.route('users.edit', $user).'" class="btn btn-sm btn-info waves-effect me-1">
                        <i class="mdi mdi-pencil me-1"></i> Edit
                    </a>
                    <button type="button" class="btn btn-sm btn-danger waves-effect btn-delete"
                        data-name="'.e($user->name).'"
                        data-url="'.route('users.destroy', $user).'">
                        <i class="mdi mdi-trash-can me-1"></i> Hapus
                    </button>';
            })
            ->rawColumns(['avatar', 'role_badge', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('users.create', compact('branches'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($data['avatar'], $data['password_confirmation']);

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('users.edit', compact('user', 'branches'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->input('remove_avatar') === '1' && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = null;
        }

        unset($data['avatar']);

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}
