<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TaskController extends Controller
{
    // ============================================================
    // VIEW
    // ============================================================
    public function index(Request $request): View
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        if ($bulan < 1 || $bulan > 12) $bulan = (int) date('n');
        if ($tahun < 2020 || $tahun > 2099) $tahun = (int) date('Y');

        return view('keuangan.tasks', [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    // ============================================================
    // DATA JSON
    // ============================================================
    public function data(Request $request): JsonResponse
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        $tasks = Task::byMonth($bulan, $tahun)
            ->with(['assignedUser:id,name', 'creator:id,name'])
            ->orderByRaw("FIELD(status, 'proses', 'belum', 'selesai', 'dibatalkan')")
            ->orderBy('tanggal_mulai')
            ->get()
            ->map(fn ($t) => $this->transformTask($t));

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tasks' => $tasks,
            'stats' => Task::getStatsByMonth($bulan, $tahun),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        return response()->json([
            'ok' => true,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'by_day' => Task::getCalendarData($bulan, $tahun),
            'stats' => Task::getStatsByMonth($bulan, $tahun),
        ]);
    }

    public function upcoming(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 5);
        if ($limit < 1 || $limit > 20) $limit = 5;

        $tasks = Task::upcoming($limit)
            ->with(['assignedUser:id,name'])
            ->get()
            ->map(fn ($t) => $this->transformTask($t));

        return response()->json([
            'ok' => true,
            'tasks' => $tasks,
        ]);
    }

    public function assignableUsers(): JsonResponse
    {
        $users = User::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'unit'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'role_label' => $u->role_label,
                'unit' => $u->unit,
            ]);

        return response()->json([
            'ok' => true,
            'users' => $users,
        ]);
    }

    // ============================================================
    // CRUD
    // ============================================================
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateTask($request);

        // Auto-generate bulan & tahun dari tanggal_mulai
        $tglMulai = Carbon::parse($validated['tanggal_mulai']);
        $validated['bulan'] = $tglMulai->month;
        $validated['tahun'] = $tglMulai->year;
        $validated['created_by'] = auth()->id();

        // Handle lampiran
        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $this->saveLampiran($request->file('lampiran'));
        }

        $task = Task::create($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Task berhasil ditambahkan.',
            'task' => $this->transformTask($task->load(['assignedUser', 'creator'])),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $validated = $this->validateTask($request, $id);

        // Auto-generate bulan & tahun dari tanggal_mulai
        $tglMulai = Carbon::parse($validated['tanggal_mulai']);
        $validated['bulan'] = $tglMulai->month;
        $validated['tahun'] = $tglMulai->year;

        // Handle lampiran baru
        if ($request->hasFile('lampiran')) {
            $this->deleteLampiran($task->lampiran);
            $validated['lampiran'] = $this->saveLampiran($request->file('lampiran'));
        }

        // Handle hapus lampiran (kalau dikirim flag)
        if ($request->boolean('hapus_lampiran') && $task->lampiran) {
            $this->deleteLampiran($task->lampiran);
            $validated['lampiran'] = null;
        }

        $task->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Task berhasil diupdate.',
            'task' => $this->transformTask($task->fresh()->load(['assignedUser', 'creator'])),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:belum,proses,selesai,dibatalkan',
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json([
            'ok' => true,
            'message' => 'Status berhasil diubah.',
            'task' => $this->transformTask($task->fresh()->load(['assignedUser', 'creator'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        // Hapus lampiran
        $this->deleteLampiran($task->lampiran);

        $task->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Task berhasil dihapus.',
        ]);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Validasi task (store & update).
     */
    protected function validateTask(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:2000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:belum,proses,selesai,dibatalkan',
            'assigned_to' => 'nullable|exists:users,id',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:5120',
        ], [
            'judul.required' => 'Judul task wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'status.in' => 'Status tidak valid.',
            'assigned_to.exists' => 'User yang dipilih tidak ditemukan.',
            'lampiran.mimes' => 'Format file harus PDF, DOC, XLS, PPT, atau gambar.',
            'lampiran.max' => 'Ukuran file maksimal 5 MB.',
        ]);
    }

    /**
     * Simpan lampiran ke storage/app/public/tasks/.
     */
    protected function saveLampiran($file): string
    {
        $filename = 'task-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('tasks', $filename, 'public');
    }

    /**
     * Hapus lampiran dari storage.
     */
    protected function deleteLampiran(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Transform task untuk JSON response.
     */
    protected function transformTask(Task $t): array
    {
        return [
            'id' => $t->id,
            'judul' => $t->judul,
            'deskripsi' => $t->deskripsi,
            'bulan' => $t->bulan,
            'tahun' => $t->tahun,
            'tanggal_mulai' => $t->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $t->tanggal_selesai?->format('Y-m-d'),
            'tanggal_mulai_formatted' => $t->tanggal_mulai?->translatedFormat('d M Y'),
            'tanggal_selesai_formatted' => $t->tanggal_selesai?->translatedFormat('d M Y'),
            'status' => $t->status,
            'status_label' => $t->status_label,
            'status_color' => $t->status_color,
            'is_overdue' => $t->is_overdue,
            'hari_tersisa' => $t->hari_tersisa,
            'assigned_to' => $t->assigned_to,
            'assigned_name' => $t->assignedUser?->name,
            'created_by' => $t->created_by,
            'creator_name' => $t->creator?->name,
            'lampiran' => $t->lampiran,
            'lampiran_url' => $t->lampiran ? asset('storage/' . $t->lampiran) : null,
            'created_at' => $t->created_at?->format('Y-m-d H:i'),
        ];
    }
}