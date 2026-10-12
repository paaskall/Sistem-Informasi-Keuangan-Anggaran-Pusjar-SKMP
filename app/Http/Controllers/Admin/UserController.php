<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Halaman list user.
     */
    public function index(): View
    {
        return view('admin.users');
    }

    /**
     * Ambil data user via AJAX.
     */
    public function data(): JsonResponse
    {
        $users = User::orderBy('id')->get()->map(fn ($u) => [
            'id' => $u->id,
            'username' => $u->username,
            'name' => $u->name,
            'role' => $u->role,
            'role_label' => $u->role_label,
            'unit' => $u->unit,
            'active' => $u->active,
            'initial' => $u->initial,
        ]);

        return response()->json([
            'ok' => true,
            'users' => $users,
            'current_user_id' => auth()->id(),
        ]);
    }

    /**
     * Simpan user baru.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['active'] = true;

        $user = User::create($data);

        return response()->json([
            'ok' => true,
            'message' => 'User berhasil ditambahkan.',
            'user' => $user,
        ]);
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // ⭐ SECURITY: Kalau ganti password user LAIN → butuh konfirmasi admin
        if ($request->filled('password') && $user->id !== auth()->id()) {
            if (!$this->hasRecentPasswordConfirmation()) {
                return response()->json([
                    'ok' => false,
                    'require_confirmation' => true,
                    'message' => 'Perubahan password user lain memerlukan konfirmasi password Anda.',
                ], 423);
            }
        }

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'User berhasil diupdate.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Toggle status aktif user.
     */
    public function toggleActive(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak bisa menonaktifkan akun sendiri.',
            ], 422);
        }

        // ⭐ SECURITY: Butuh konfirmasi admin
        if (!$this->hasRecentPasswordConfirmation()) {
            return response()->json([
                'ok' => false,
                'require_confirmation' => true,
                'message' => 'Aksi ini memerlukan konfirmasi password Anda.',
            ], 423);
        }

        $user->update(['active' => !$user->active]);

        return response()->json([
            'ok' => true,
            'message' => $user->active ? 'User diaktifkan.' : 'User dinonaktifkan.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Hapus user (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak bisa menghapus akun sendiri.',
            ], 422);
        }

        // ⭐ SECURITY: Butuh konfirmasi admin
        if (!$this->hasRecentPasswordConfirmation()) {
            return response()->json([
                'ok' => false,
                'require_confirmation' => true,
                'message' => 'Penghapusan user memerlukan konfirmasi password Anda.',
            ], 423);
        }

        $user->delete();

        return response()->json([
            'ok' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }

    /**
     * Cek apakah admin sudah konfirmasi password (dalam 5 menit terakhir).
     */
    protected function hasRecentPasswordConfirmation(int $timeoutMinutes = 5): bool
    {
        $confirmedAt = session('password_confirmed_at');

        if (!$confirmedAt) {
            return false;
        }

        return now()->diffInMinutes($confirmedAt) < $timeoutMinutes;
    }
}
