<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Halaman profile.
     */
    public function index(): View
    {
        return view('profile');
    }

    /**
     * Ambil data profile user aktif.
     */
    public function data(): JsonResponse
    {
        $user = auth()->user();

        // Log aktivitas user sendiri
        $logs = AuditTrail::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'action', 'module', 'record_id', 'created_at', 'user_name']);

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'role' => $user->role,
                'role_label' => $user->role_label,
                'unit' => $user->unit,
                'initial' => $user->initial,
                'avatar' => $user->avatar ?? null,
                'active' => $user->active,
                'created_at' => $user->created_at?->translatedFormat('d F Y'),
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Update profile (nama, username, email, phone).
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Profile berhasil diupdate.',
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Update password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();
        $user->update([
            'password' => $request->new_password,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    /**
     * Upload avatar (opsional).
     */
    public function uploadAvatar(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        $file = $request->file('avatar');

        // Simpan ke storage/app/public/avatars
        $filename = 'avatar-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');

        // Hapus avatar lama
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => $path]);

        return response()->json([
            'ok' => true,
            'message' => 'Avatar berhasil diupload.',
            'avatar_url' => asset('storage/' . $path),
        ]);
    }
}