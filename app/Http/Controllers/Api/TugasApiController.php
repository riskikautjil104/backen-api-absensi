<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TugasApiController extends Controller
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    // =========================================================================
    // 1. ENDPOINTS GURU (PENDIDIK)
    // =========================================================================

    /**
     * List all Tugas created by the authenticated Guru
     * GET /api/guru/tugas
     */
    public function guruList(Request $request)
    {
        $user = $request->user();
        $kelasId = $request->query('kelas_id');
        $mapelId = $request->query('mapel_id');

        $query = Tugas::with(['kelas', 'mapel', 'pengumpulan'])
            ->where('guru_id', $user->id);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }
        if ($mapelId) {
            $query->where('mapel_id', $mapelId);
        }

        $tugasList = $query->orderBy('created_at', 'desc')->get()->map(function ($t) {
            $totalStudents = Siswa::where('kelas_id', $t->kelas_id)->count();
            $submittedCount = $t->pengumpulan->whereIn('status', ['dikumpulkan', 'dinilai', 'terlambat'])->count();
            $gradedCount = $t->pengumpulan->where('status', 'dinilai')->count();

            return [
                'id' => $t->id,
                'judul' => $t->judul,
                'deskripsi' => $t->deskripsi,
                'tipe_pengumpulan' => $t->tipe_pengumpulan, // 'online' atau 'langsung'
                'deadline' => $t->deadline ? $t->deadline->format('Y-m-d H:i:s') : null,
                'deadline_formatted' => $t->deadline ? Carbon::parse($t->deadline)->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : 'Tidak ada deadline',
                'file_lampiran' => $t->file_lampiran ? url('storage/' . $t->file_lampiran) : null,
                'poin_maksimal' => $t->poin_maksimal,
                'status' => $t->status,
                'kelas_id' => $t->kelas_id,
                'class_name' => $t->kelas->nama_kelas ?? '-',
                'mapel_id' => $t->mapel_id,
                'subject_name' => $t->mapel->nama_mapel ?? '-',
                'teacher_name' => $t->guru->name ?? '-',
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_submitted' => $submittedCount,
                    'total_graded' => $gradedCount,
                    'total_pending' => max(0, $totalStudents - $submittedCount),
                ],
                'created_at' => $t->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas guru berhasil diambil.',
            'data' => $tugasList,
        ]);
    }

    /**
     * Create a new Tugas (Guru)
     * POST /api/guru/tugas
     */
    public function guruStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'mapel_id' => 'required|integer|exists:mata_pelajaran,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe_pengumpulan' => 'required|in:online,langsung',
            'deadline' => 'nullable|date',
            'poin_maksimal' => 'nullable|integer|min:1|max:100',
            'file_lampiran' => 'nullable|file|max:20480', // Max 20MB
        ]);

        $user = $request->user();

        $lampiranPath = null;
        if ($request->hasFile('file_lampiran')) {
            $lampiranPath = $request->file('file_lampiran')->store('tugas_lampiran', 'public');
        }

        $tugas = Tugas::create([
            'guru_id' => $user->id,
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe_pengumpulan' => $request->tipe_pengumpulan,
            'deadline' => $request->deadline ? Carbon::parse($request->deadline) : null,
            'file_lampiran' => $lampiranPath,
            'poin_maksimal' => $request->poin_maksimal ?? 100,
            'status' => 'aktif',
        ]);

        // Push Notification ke semua siswa di kelas yang dituju
        try {
            $students = Siswa::where('kelas_id', $request->kelas_id)->with('user')->get();
            $tokens = $students->pluck('user.fcm_token')->filter()->values()->toArray();
            $mapelName = MataPelajaran::where('id', $request->mapel_id)->value('nama_mapel') ?? 'Pelajaran';
            $deadlineStr = $tugas->deadline ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : 'Fleksibel';

            if (!empty($tokens)) {
                $this->fcmService->sendToMultiple(
                    $tokens,
                    "📚 Tugas Baru: {$mapelName}",
                    "{$user->name} memberikan tugas baru \"{$tugas->judul}\". Tenggat: {$deadlineStr}",
                    [
                        'type' => 'new_tugas',
                        'tugas_id' => (string)$tugas->id,
                        'kelas_id' => (string)$request->kelas_id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim FCM new_tugas: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas baru berhasil diterbitkan dan notifikasi telah dikirim ke siswa.',
            'data' => $tugas,
        ], 201);
    }

    /**
     * Show detail of a Tugas with all student submissions (Guru)
     * GET /api/guru/tugas/{id}
     */
    public function guruShow(Request $request, $id)
    {
        $user = $request->user();
        $tugas = Tugas::with(['kelas', 'mapel', 'pengumpulan.siswa.user'])
            ->where('guru_id', $user->id)
            ->findOrFail($id);

        // Fetch all students in the class
        $students = Siswa::where('kelas_id', $tugas->kelas_id)->with('user')->get();

        $submissionMap = $tugas->pengumpulan->keyBy('siswa_id');

        $studentList = $students->map(function ($s) use ($submissionMap, $tugas) {
            $sub = $submissionMap->get($s->id);
            $isSubmitted = $sub != null;
            $status = $isSubmitted ? $sub->status : 'belum';

            return [
                'siswa_id' => $s->id,
                'name' => $s->user->name ?? '-',
                'nis' => $s->user->nis ?? '-',
                'phone' => $s->nomor_hp ?? '-',
                'status' => $status, // 'belum', 'dikumpulkan', 'dinilai', 'terlambat'
                'submission_id' => $sub?->id,
                'tipe_pengumpulan' => $sub?->tipe_pengumpulan ?? $tugas->tipe_pengumpulan,
                'waktu_kumpul' => $sub?->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->format('Y-m-d H:i:s') : null,
                'waktu_kumpul_formatted' => $sub?->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                'file_tugas' => $sub?->file_tugas ? url('storage/' . $sub->file_tugas) : null,
                'catatan_siswa' => $sub?->catatan_siswa,
                'nilai' => $sub?->nilai,
                'catatan_guru' => $sub?->catatan_guru,
                'waktu_dinilai' => $sub?->waktu_dinilai ? Carbon::parse($sub->waktu_dinilai)->format('Y-m-d H:i:s') : null,
            ];
        });

        $totalStudents = $students->count();
        $submittedCount = $studentList->whereIn('status', ['dikumpulkan', 'dinilai', 'terlambat'])->count();
        $gradedCount = $studentList->where('status', 'dinilai')->count();

        return response()->json([
            'success' => true,
            'message' => 'Detail tugas berhasil diambil.',
            'data' => [
                'tugas' => [
                    'id' => $tugas->id,
                    'judul' => $tugas->judul,
                    'deskripsi' => $tugas->deskripsi,
                    'tipe_pengumpulan' => $tugas->tipe_pengumpulan,
                    'deadline' => $tugas->deadline ? $tugas->deadline->format('Y-m-d H:i:s') : null,
                    'deadline_formatted' => $tugas->deadline ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : 'Tidak ada deadline',
                    'file_lampiran' => $tugas->file_lampiran ? url('storage/' . $tugas->file_lampiran) : null,
                    'poin_maksimal' => $tugas->poin_maksimal,
                    'status' => $tugas->status,
                    'kelas_id' => $tugas->kelas_id,
                    'class_name' => $tugas->kelas->nama_kelas ?? '-',
                    'mapel_id' => $tugas->mapel_id,
                    'subject_name' => $tugas->mapel->nama_mapel ?? '-',
                ],
                'summary' => [
                    'total_students' => $totalStudents,
                    'total_submitted' => $submittedCount,
                    'total_graded' => $gradedCount,
                    'total_pending' => max(0, $totalStudents - $submittedCount),
                ],
                'students' => $studentList,
            ],
        ]);
    }

    /**
     * Grade a student's submission (Guru)
     * POST /api/guru/tugas/{tugas_id}/nilai/{siswa_id}
     */
    public function guruGrade(Request $request, $tugasId, $siswaId)
    {
        $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'catatan_guru' => 'nullable|string',
            'tipe_pengumpulan' => 'nullable|in:online,langsung',
        ]);

        $user = $request->user();
        $tugas = Tugas::where('guru_id', $user->id)->findOrFail($tugasId);
        $siswa = Siswa::with('user')->findOrFail($siswaId);

        $now = Carbon::now('Asia/Jayapura');

        $submission = PengumpulanTugas::updateOrCreate(
            [
                'tugas_id' => $tugasId,
                'siswa_id' => $siswaId,
            ],
            [
                'nilai' => $request->nilai,
                'catatan_guru' => $request->catatan_guru,
                'status' => 'dinilai',
                'tipe_pengumpulan' => $request->tipe_pengumpulan ?? $tugas->tipe_pengumpulan,
                'waktu_dinilai' => $now,
                'waktu_kumpul' => $now,
            ]
        );

        // Kirim Push Notification ke Siswa bahwa tugas telah dinilai
        try {
            if ($siswa->user?->fcm_token) {
                $mapelName = $tugas->mapel?->nama_mapel ?? 'Mata Pelajaran';
                $this->fcmService->sendToDevice(
                    $siswa->user->fcm_token,
                    "🌟 Tugas Dinilai: {$mapelName}",
                    "Tugas \"{$tugas->judul}\" telah dinilai oleh {$user->name}. Nilai Anda: {$request->nilai}/{$tugas->poin_maksimal}",
                    [
                        'type' => 'tugas_graded',
                        'tugas_id' => (string)$tugasId,
                        'nilai' => (string)$request->nilai,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim FCM tugas_graded: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Nilai tugas untuk {$siswa->user->name} berhasil disimpan.",
            'data' => $submission,
        ]);
    }

    /**
     * Delete a Tugas (Guru)
     * DELETE /api/guru/tugas/{id}
     */
    public function guruDestroy(Request $request, $id)
    {
        $user = $request->user();
        $tugas = Tugas::where('guru_id', $user->id)->findOrFail($id);

        if ($tugas->file_lampiran) {
            Storage::disk('public')->delete($tugas->file_lampiran);
        }

        $tugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus.',
        ]);
    }

    /**
     * Rekap Nilai Tugas Siswa per Kelas & Mapel (Guru)
     * GET /api/guru/tugas/rekap/kelas/{kelas_id}/mapel/{mapel_id}
     */
    public function guruRekap(Request $request, $kelasId, $mapelId)
    {
        $user = $request->user();
        $kelas = Kelas::findOrFail($kelasId);
        $mapel = MataPelajaran::findOrFail($mapelId);

        // Fetch all assignments for this class and mapel by this guru
        $allTugas = Tugas::where('guru_id', $user->id)
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->orderBy('created_at', 'asc')
            ->get();

        $students = Siswa::where('kelas_id', $kelasId)->with('user')->get();

        $rekapRows = $students->map(function ($s) use ($allTugas) {
            $tugasScores = [];
            $totalScore = 0;
            $gradedCount = 0;

            foreach ($allTugas as $t) {
                $sub = PengumpulanTugas::where('tugas_id', $t->id)
                    ->where('siswa_id', $s->id)
                    ->first();

                $score = $sub && $sub->nilai !== null ? (float)$sub->nilai : null;
                if ($score !== null) {
                    $totalScore += $score;
                    $gradedCount++;
                }

                $tugasScores[] = [
                    'tugas_id' => $t->id,
                    'judul' => $t->judul,
                    'nilai' => $score,
                    'status' => $sub ? $sub->status : 'belum',
                ];
            }

            $average = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0;

            return [
                'siswa_id' => $s->id,
                'name' => $s->user->name ?? '-',
                'nis' => $s->user->nis ?? '-',
                'tugas_scores' => $tugasScores,
                'total_graded' => $gradedCount,
                'average_score' => $average,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Rekapitulasi nilai tugas berhasil diambil.',
            'data' => [
                'kelas_name' => $kelas->nama_kelas,
                'subject_name' => $mapel->nama_mapel,
                'total_assignments' => $allTugas->count(),
                'assignments_list' => $allTugas->map(fn($t) => ['id' => $t->id, 'judul' => $t->judul]),
                'students' => $rekapRows,
            ],
        ]);
    }

    // =========================================================================
    // 2. ENDPOINTS SISWA (STUDENT)
    // =========================================================================

    /**
     * List all Tugas for the authenticated Siswa
     * GET /api/siswa/tugas
     */
    public function siswaList(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $allTugas = Tugas::with(['guru', 'mapel', 'kelas'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->get();

        $tugasIdList = $allTugas->pluck('id')->toArray();
        $mySubmissions = PengumpulanTugas::whereIn('tugas_id', $tugasIdList)
            ->where('siswa_id', $siswa->id)
            ->get()
            ->keyBy('tugas_id');

        $mapped = $allTugas->map(function ($t) use ($mySubmissions) {
            $sub = $mySubmissions->get($t->id);
            $status = $sub ? $sub->status : 'belum';

            $now = Carbon::now('Asia/Jayapura');
            $isOverdue = $t->deadline && $now->isAfter(Carbon::parse($t->deadline)) && $status === 'belum';

            return [
                'id' => $t->id,
                'judul' => $t->judul,
                'deskripsi' => $t->deskripsi,
                'tipe_pengumpulan' => $t->tipe_pengumpulan, // 'online' atau 'langsung'
                'deadline' => $t->deadline ? $t->deadline->format('Y-m-d H:i:s') : null,
                'deadline_formatted' => $t->deadline ? Carbon::parse($t->deadline)->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : 'Tidak ada batas waktu',
                'is_overdue' => $isOverdue,
                'file_lampiran' => $t->file_lampiran ? url('storage/' . $t->file_lampiran) : null,
                'poin_maksimal' => $t->poin_maksimal,
                'subject_name' => $t->mapel->nama_mapel ?? '-',
                'teacher_name' => $t->guru->name ?? '-',
                'class_name' => $t->kelas->nama_kelas ?? '-',
                'my_submission' => $sub ? [
                    'id' => $sub->id,
                    'status' => $sub->status, // 'dikumpulkan', 'dinilai', 'terlambat'
                    'tipe_pengumpulan' => $sub->tipe_pengumpulan,
                    'waktu_kumpul' => $sub->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->format('Y-m-d H:i:s') : null,
                    'waktu_kumpul_formatted' => $sub->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                    'file_tugas' => $sub->file_tugas ? url('storage/' . $sub->file_tugas) : null,
                    'catatan_siswa' => $sub->catatan_siswa,
                    'nilai' => $sub->nilai,
                    'catatan_guru' => $sub->catatan_guru,
                    'waktu_dinilai' => $sub->waktu_dinilai ? Carbon::parse($sub->waktu_dinilai)->format('Y-m-d H:i:s') : null,
                ] : null,
                'created_at' => $t->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas siswa berhasil diambil.',
            'data' => $mapped,
        ]);
    }

    /**
     * Detail Tugas for Siswa
     * GET /api/siswa/tugas/{id}
     */
    public function siswaShow(Request $request, $id)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $tugas = Tugas::with(['guru', 'mapel', 'kelas'])
            ->where('kelas_id', $siswa->kelas_id)
            ->findOrFail($id);

        $sub = PengumpulanTugas::where('tugas_id', $id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $now = Carbon::now('Asia/Jayapura');
        $isOverdue = $tugas->deadline && $now->isAfter(Carbon::parse($tugas->deadline)) && !$sub;

        return response()->json([
            'success' => true,
            'message' => 'Detail tugas berhasil diambil.',
            'data' => [
                'id' => $tugas->id,
                'judul' => $tugas->judul,
                'deskripsi' => $tugas->deskripsi,
                'tipe_pengumpulan' => $tugas->tipe_pengumpulan,
                'deadline' => $tugas->deadline ? $tugas->deadline->format('Y-m-d H:i:s') : null,
                'deadline_formatted' => $tugas->deadline ? Carbon::parse($tugas->deadline)->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : 'Tidak ada batas waktu',
                'is_overdue' => $isOverdue,
                'file_lampiran' => $tugas->file_lampiran ? url('storage/' . $tugas->file_lampiran) : null,
                'poin_maksimal' => $tugas->poin_maksimal,
                'subject_name' => $tugas->mapel->nama_mapel ?? '-',
                'teacher_name' => $tugas->guru->name ?? '-',
                'class_name' => $tugas->kelas->nama_kelas ?? '-',
                'my_submission' => $sub ? [
                    'id' => $sub->id,
                    'status' => $sub->status,
                    'tipe_pengumpulan' => $sub->tipe_pengumpulan,
                    'waktu_kumpul' => $sub->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->format('Y-m-d H:i:s') : null,
                    'waktu_kumpul_formatted' => $sub->waktu_kumpul ? Carbon::parse($sub->waktu_kumpul)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                    'file_tugas' => $sub->file_tugas ? url('storage/' . $sub->file_tugas) : null,
                    'catatan_siswa' => $sub->catatan_siswa,
                    'nilai' => $sub->nilai,
                    'catatan_guru' => $sub->catatan_guru,
                    'waktu_dinilai' => $sub->waktu_dinilai ? Carbon::parse($sub->waktu_dinilai)->format('Y-m-d H:i:s') : null,
                ] : null,
            ],
        ]);
    }

    /**
     * Submit a Tugas by Siswa (Online file upload / Catatan / Kumpul Langsung)
     * POST /api/siswa/tugas/{id}/kumpul
     */
    public function siswaSubmit(Request $request, $id)
    {
        $request->validate([
            'catatan_siswa' => 'nullable|string',
            'file_tugas' => 'nullable|file|max:20480', // Max 20MB
            'tipe_pengumpulan' => 'nullable|in:online,langsung',
        ]);

        $user = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $tugas = Tugas::with('guru', 'mapel')->where('kelas_id', $siswa->kelas_id)->findOrFail($id);

        $now = Carbon::now('Asia/Jayapura');
        $isLate = $tugas->deadline && $now->isAfter(Carbon::parse($tugas->deadline));
        $status = $isLate ? 'terlambat' : 'dikumpulkan';

        $filePath = null;
        if ($request->hasFile('file_tugas')) {
            $filePath = $request->file('file_tugas')->store('tugas_siswa', 'public');
        }

        $submission = PengumpulanTugas::updateOrCreate(
            [
                'tugas_id' => $id,
                'siswa_id' => $siswa->id,
            ],
            [
                'status' => $status,
                'tipe_pengumpulan' => $request->tipe_pengumpulan ?? $tugas->tipe_pengumpulan,
                'waktu_kumpul' => $now,
                'file_tugas' => $filePath ?? ($submission->file_tugas ?? null),
                'catatan_siswa' => $request->catatan_siswa,
            ]
        );

        // Push Notification ke Guru bahwa siswa telah mengumpulkan tugas
        try {
            if ($tugas->guru?->fcm_token) {
                $statusText = $isLate ? '(Terlambat)' : '(Tepat Waktu)';
                $this->fcmService->sendToDevice(
                    $tugas->guru->fcm_token,
                    "📥 Tugas Dikumpulkan: {$user->name}",
                    "{$user->name} (NIS: {$user->nis}) telah mengumpulkan tugas \"{$tugas->judul}\" {$statusText}.",
                    [
                        'type' => 'tugas_submitted',
                        'tugas_id' => (string)$id,
                        'siswa_id' => (string)$siswa->id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim FCM tugas_submitted: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas Anda berhasil dikumpulkan!',
            'data' => $submission,
        ]);
    }
}

// akhir batas suci yang kamu ubah
