<?php

// awal batas suci yang kamu ubah

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BahanAjar;
use App\Models\EvaluasiBahanAjar;
use App\Models\SoalEvaluasi;
use App\Models\JawabanEvaluasi;
use App\Models\DetailJawabanEvaluasi;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BahanAjarApiController extends Controller
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * List Bahan Ajar milik Guru
     * GET /api/guru/bahan-ajar
     */
    public function guruIndex(Request $request)
    {
        $user = $request->user();
        $query = BahanAjar::with(['kelas', 'mapel', 'evaluasi.jawaban'])
            ->where('guru_id', $user->id);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        $items = $query->orderBy('id', 'desc')->get();

        $data = $items->map(function ($item) {
            $totalStudents = Siswa::where('kelas_id', $item->kelas_id)->count();
            $evaluasi = $item->evaluasi;
            $submittedCount = ($evaluasi && $evaluasi->jawaban) ? $evaluasi->jawaban->count() : 0;
            $gradedCount = ($evaluasi && $evaluasi->jawaban) ? $evaluasi->jawaban->where('status', 'dinilai')->count() : 0;

            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'tipe_materi' => $item->tipe_materi,
                'url_materi' => $item->url_materi,
                'embed_url' => $item->embed_url,
                'file_materi' => $item->file_materi ? url('storage/' . $item->file_materi) : null,
                'status' => $item->status,
                'kelas_id' => $item->kelas_id,
                'class_name' => $item->kelas->nama_kelas ?? '-',
                'mapel_id' => $item->mapel_id,
                'subject_name' => $item->mapel->nama_mapel ?? '-',
                'has_evaluasi' => $evaluasi !== null,
                'evaluasi_id' => $evaluasi?->id,
                'total_students' => $totalStudents,
                'total_submitted_evaluasi' => $submittedCount,
                'total_graded_evaluasi' => $gradedCount,
                'created_at' => $item->created_at ? $item->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar bahan ajar berhasil dimuat.',
            'data' => $data,
        ]);
    }

    /**
     * Upload & Buat Bahan Ajar Baru (Guru)
     * POST /api/guru/bahan-ajar
     */
    public function guruStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe_materi' => 'required|in:google_docs,google_slides,pdf,youtube,link',
            'url_materi' => 'nullable|string',
            'file_materi' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:25600',
            'has_evaluasi' => 'nullable|boolean',
            'judul_evaluasi' => 'nullable|string|max:255',
            'instruksi_evaluasi' => 'nullable|string',
            'poin_maksimal' => 'nullable|numeric|min:1',
            'soal' => 'nullable|array',
            'soal.*.pertanyaan' => 'required_with:soal|string',
            'soal.*.tipe_soal' => 'nullable|in:esai,pilihan_ganda',
            'soal.*.opsi_a' => 'nullable|string',
            'soal.*.opsi_b' => 'nullable|string',
            'soal.*.opsi_c' => 'nullable|string',
            'soal.*.opsi_d' => 'nullable|string',
            'soal.*.opsi_e' => 'nullable|string',
            'soal.*.kunci_jawaban' => 'nullable|string|max:5',
            'soal.*.poin' => 'nullable|numeric|min:1',
        ]);

        $user = $request->user();

        $filePath = null;
        if ($request->hasFile('file_materi')) {
            $filePath = $request->file('file_materi')->store('bahan_ajar', 'public');
        }

        $url = $request->url_materi;
        $embedUrl = BahanAjar::generateEmbedUrl($url, $request->tipe_materi);

        DB::beginTransaction();
        try {
            $bahanAjar = BahanAjar::create([
                'guru_id' => $user->id,
                'kelas_id' => $request->kelas_id,
                'mapel_id' => $request->mapel_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tipe_materi' => $request->tipe_materi,
                'url_materi' => $url,
                'embed_url' => $embedUrl,
                'file_materi' => $filePath,
                'status' => 'aktif',
            ]);

            // Jika ada evaluasi / kuis pemahaman
            if ($request->has_evaluasi || !empty($request->soal)) {
                $evaluasi = EvaluasiBahanAjar::create([
                    'bahan_ajar_id' => $bahanAjar->id,
                    'judul_evaluasi' => $request->judul_evaluasi ?? 'Evaluasi Pemahaman: ' . $bahanAjar->judul,
                    'instruksi' => $request->instruksi_evaluasi ?? 'Jawablah soal-soal berikut untuk menguji pemahaman materi yang telah dipelajari.',
                    'poin_maksimal' => $request->poin_maksimal ?? 100,
                ]);

                if (!empty($request->soal)) {
                    $no = 1;
                    foreach ($request->soal as $s) {
                        SoalEvaluasi::create([
                            'evaluasi_id' => $evaluasi->id,
                            'nomor_urut' => $no++,
                            'pertanyaan' => $s['pertanyaan'],
                            'tipe_soal' => $s['tipe_soal'] ?? 'esai',
                            'opsi_a' => $s['opsi_a'] ?? null,
                            'opsi_b' => $s['opsi_b'] ?? null,
                            'opsi_c' => $s['opsi_c'] ?? null,
                            'opsi_d' => $s['opsi_d'] ?? null,
                            'opsi_e' => $s['opsi_e'] ?? null,
                            'kunci_jawaban' => isset($s['kunci_jawaban']) ? strtoupper($s['kunci_jawaban']) : null,
                            'poin' => $s['poin'] ?? 10,
                        ]);
                    }
                }
            }

            DB::commit();

            // Push Notification FCM ke Siswa
            try {
                $students = Siswa::where('kelas_id', $request->kelas_id)->with('user')->get();
                $tokens = $students->pluck('user.fcm_token')->filter()->values()->toArray();

                if (empty($tokens)) {
                    $tokens = User::where('role', 'siswa')->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
                }

                $mapelName = MataPelajaran::where('id', $request->mapel_id)->value('nama_mapel') ?? 'Pelajaran';
                $notifTitle = "📖 Bahan Ajar Baru: {$mapelName}";
                $notifBody = "{$user->name} menerbitkan bahan ajar \"{$bahanAjar->judul}\". Buka aplikasi untuk mempelajari materi & evaluasi.";
                $notifData = [
                    'type' => 'new_bahan_ajar',
                    'bahan_ajar_id' => (string)$bahanAjar->id,
                    'kelas_id' => (string)$request->kelas_id,
                ];

                if (!empty($tokens)) {
                    $this->fcmService->sendToMultiple($tokens, $notifTitle, $notifBody, $notifData);
                }

                $this->fcmService->sendToTopic("kelas_{$request->kelas_id}", $notifTitle, $notifBody, $notifData);
                $this->fcmService->sendToTopic("siswa", $notifTitle, $notifBody, $notifData);
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim FCM new_bahan_ajar: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Bahan ajar berhasil diterbitkan dan notifikasi telah dikirim ke siswa.',
                'data' => $bahanAjar->load(['kelas', 'mapel', 'evaluasi.soal']),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan bahan ajar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail Bahan Ajar & Rekap Siswa (Guru)
     * GET /api/guru/bahan-ajar/{id}
     */
    public function guruShow(Request $request, $id)
    {
        $user = $request->user();
        $item = BahanAjar::with(['kelas', 'mapel', 'evaluasi.soal', 'evaluasi.jawaban.siswa.user', 'evaluasi.jawaban.details.soal'])
            ->where('guru_id', $user->id)
            ->findOrFail($id);

        $evaluasi = $item->evaluasi;

        // Ambil semua siswa di kelas tersebut + siswa yang sudah submit evaluasi
        $classStudentIds = Siswa::where('kelas_id', $item->kelas_id)->pluck('id');
        $submittedStudentIds = $evaluasi ? $evaluasi->jawaban->pluck('siswa_id') : collect();
        $allStudentIds = $classStudentIds->merge($submittedStudentIds)->unique()->filter();

        $students = Siswa::whereIn('id', $allStudentIds)->with('user')->get();
        if ($students->isEmpty()) {
            $students = Siswa::with('user')->get();
        }

        $jawabanMap = $evaluasi ? $evaluasi->jawaban->keyBy('siswa_id') : collect();

        $studentList = $students->map(function ($s) use ($jawabanMap, $evaluasi) {
            $ans = $jawabanMap->get($s->id);
            $isSubmitted = $ans != null;

            return [
                'siswa_id' => $s->id,
                'name' => $s->user->name ?? '-',
                'nis' => $s->user->nis ?? '-',
                'phone' => $s->nomor_hp ?? '-',
                'status' => $isSubmitted ? $ans->status : 'belum_dikerjakan', // belum_dikerjakan, dikerjakan, dinilai
                'jawaban_id' => $ans?->id,
                'total_nilai' => $ans?->total_nilai,
                'catatan_guru' => $ans?->catatan_guru,
                'waktu_submit' => $ans?->waktu_submit ? Carbon::parse($ans->waktu_submit)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                'waktu_dinilai' => $ans?->waktu_dinilai ? Carbon::parse($ans->waktu_dinilai)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                'details' => $ans ? $ans->details->map(function ($d) {
                    return [
                        'soal_id' => $d->soal_evaluasi_id,
                        'nomor_urut' => $d->soal->nomor_urut ?? 1,
                        'pertanyaan' => $d->soal->pertanyaan ?? '-',
                        'tipe_soal' => $d->soal->tipe_soal ?? 'esai',
                        'jawaban_siswa' => $d->jawaban_siswa,
                        'kunci_jawaban' => $d->soal->kunci_jawaban ?? null,
                        'poin_maksimal' => $d->soal->poin ?? 10,
                        'nilai' => $d->nilai,
                    ];
                }) : [],
            ];
        });

        $totalStudents = $students->count();
        $submittedCount = $studentList->whereIn('status', ['dikerjakan', 'dinilai'])->count();
        $gradedCount = $studentList->where('status', 'dinilai')->count();

        return response()->json([
            'success' => true,
            'message' => 'Detail bahan ajar berhasil diambil.',
            'data' => [
                'bahan_ajar' => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'tipe_materi' => $item->tipe_materi,
                    'url_materi' => $item->url_materi,
                    'embed_url' => $item->embed_url,
                    'file_materi' => $item->file_materi ? url('storage/' . $item->file_materi) : null,
                    'status' => $item->status,
                    'kelas_id' => $item->kelas_id,
                    'class_name' => $item->kelas->nama_kelas ?? '-',
                    'mapel_id' => $item->mapel_id,
                    'subject_name' => $item->mapel->nama_mapel ?? '-',
                    'created_at' => $item->created_at ? $item->created_at->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : '-',
                ],
                'evaluasi' => $evaluasi ? [
                    'id' => $evaluasi->id,
                    'judul_evaluasi' => $evaluasi->judul_evaluasi,
                    'instruksi' => $evaluasi->instruksi,
                    'poin_maksimal' => $evaluasi->poin_maksimal,
                    'soal' => $evaluasi->soal->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'nomor_urut' => $s->nomor_urut,
                            'pertanyaan' => $s->pertanyaan,
                            'tipe_soal' => $s->tipe_soal,
                            'opsi_a' => $s->opsi_a,
                            'opsi_b' => $s->opsi_b,
                            'opsi_c' => $s->opsi_c,
                            'opsi_d' => $s->opsi_d,
                            'opsi_e' => $s->opsi_e,
                            'kunci_jawaban' => $s->kunci_jawaban,
                            'poin' => $s->poin,
                        ];
                    }),
                ] : null,
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
     * Hapus Bahan Ajar (Guru)
     * DELETE /api/guru/bahan-ajar/{id}
     */
    public function guruDestroy(Request $request, $id)
    {
        $user = $request->user();
        $item = BahanAjar::where('guru_id', $user->id)->findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bahan ajar berhasil dihapus.',
        ]);
    }

    /**
     * Beri Nilai Evaluasi Siswa (Guru)
     * POST /api/guru/bahan-ajar/{bahan_ajar_id}/evaluasi/{siswa_id}/nilai
     */
    public function guruGradeEvaluasi(Request $request, $bahanAjarId, $siswaId)
    {
        $request->validate([
            'total_nilai' => 'required|numeric|min:0|max:100',
            'catatan_guru' => 'nullable|string',
            'detail_nilai' => 'nullable|array', // ['soal_id' => nilai]
        ]);

        $user = $request->user();
        $bahanAjar = BahanAjar::where('guru_id', $user->id)->findOrFail($bahanAjarId);
        $evaluasi = EvaluasiBahanAjar::where('bahan_ajar_id', $bahanAjarId)->firstOrFail();
        $siswa = Siswa::with('user')->findOrFail($siswaId);

        $now = Carbon::now('Asia/Jayapura');

        $jawaban = JawabanEvaluasi::updateOrCreate(
            [
                'evaluasi_id' => $evaluasi->id,
                'siswa_id' => $siswaId,
            ],
            [
                'total_nilai' => $request->total_nilai,
                'catatan_guru' => $request->catatan_guru,
                'status' => 'dinilai',
                'waktu_dinilai' => $now,
            ]
        );

        // Update detail nilai per soal jika disediakan
        if (!empty($request->detail_nilai)) {
            foreach ($request->detail_nilai as $soalId => $score) {
                DetailJawabanEvaluasi::where('jawaban_evaluasi_id', $jawaban->id)
                    ->where('soal_evaluasi_id', $soalId)
                    ->update(['nilai' => $score]);
            }
        }

        // Push Notification ke Siswa
        try {
            $studentToken = $siswa->user?->fcm_token ?? User::where('id', $siswa->user_id)->value('fcm_token');
            if ($studentToken) {
                $mapelName = $bahanAjar->mapel?->nama_mapel ?? 'Mata Pelajaran';
                $this->fcmService->sendToDevice(
                    $studentToken,
                    "🌟 Nilai Evaluasi: {$mapelName}",
                    "Evaluasi \"{$bahanAjar->judul}\" telah dinilai oleh {$user->name}. Nilai Anda: {$request->total_nilai}/{$evaluasi->poin_maksimal}",
                    [
                        'type' => 'evaluasi_graded',
                        'bahan_ajar_id' => (string)$bahanAjarId,
                        'total_nilai' => (string)$request->total_nilai,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim FCM evaluasi_graded: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Nilai evaluasi untuk {$siswa->user->name} berhasil disimpan.",
            'data' => $jawaban->fresh()->load('details'),
        ]);
    }

    // ==========================================
    // SISWA ENDPOINTS
    // ==========================================

    /**
     * List Bahan Ajar untuk Siswa
     * GET /api/siswa/bahan-ajar
     */
    public function siswaIndex(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa ?? Siswa::where('user_id', $user->id)->first();

        // Auto-provision siswa record if missing
        if (!$siswa) {
            $defaultClassId = Kelas::value('id');
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $defaultClassId,
            ]);
        }

        $query = BahanAjar::with(['guru', 'kelas', 'mapel', 'evaluasi.jawaban'])
            ->where('status', 'aktif');

        if ($siswa && $siswa->kelas_id) {
            $query->where(function($q) use ($siswa) {
                $q->where('kelas_id', $siswa->kelas_id)
                  ->orWhereNull('kelas_id');
            });
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        $items = $query->orderBy('id', 'desc')->get();

        // If class filter returned empty but there are active materials, fallback to all active materials
        if ($items->isEmpty()) {
            $items = BahanAjar::with(['guru', 'kelas', 'mapel', 'evaluasi.jawaban'])
                ->where('status', 'aktif')
                ->orderBy('id', 'desc')
                ->get();
        }

        $data = $items->map(function ($item) use ($siswa) {
            $evaluasi = $item->evaluasi;
            $myJawaban = null;
            if ($evaluasi && $siswa && $evaluasi->jawaban) {
                $myJawaban = $evaluasi->jawaban->firstWhere('siswa_id', $siswa->id);
            }

            $statusEvaluasi = 'tidak_ada';
            if ($evaluasi) {
                if ($myJawaban) {
                    $statusEvaluasi = $myJawaban->status; // 'dikerjakan' atau 'dinilai'
                } else {
                    $statusEvaluasi = 'belum_dikerjakan';
                }
            }

            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'tipe_materi' => $item->tipe_materi,
                'url_materi' => $item->url_materi,
                'embed_url' => $item->embed_url,
                'file_materi' => $item->file_materi ? url('storage/' . $item->file_materi) : null,
                'teacher_name' => $item->guru->name ?? 'Guru',
                'class_name' => $item->kelas->nama_kelas ?? '-',
                'mapel_id' => $item->mapel_id,
                'subject_name' => $item->mapel->nama_mapel ?? '-',
                'has_evaluasi' => $evaluasi !== null,
                'status_evaluasi' => $statusEvaluasi,
                'nilai' => $myJawaban?->total_nilai,
                'created_at' => $item->created_at ? $item->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar bahan ajar berhasil diambil.',
            'data' => $data,
        ]);
    }

    /**
     * Detail Bahan Ajar untuk Siswa (In-App Document Viewer + Evaluasi Quiz)
     * GET /api/siswa/bahan-ajar/{id}
     */
    public function siswaShow(Request $request, $id)
    {
        $user = $request->user();
        $siswa = Siswa::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nis' => $user->nis ?? '0000',
                'kelas_id' => 1,
                'jenis_kelamin' => 'L',
            ]
        );

        $item = BahanAjar::with(['guru', 'kelas', 'mapel', 'evaluasi.soal'])
            ->where('status', 'aktif')
            ->findOrFail($id);

        $evaluasi = $item->evaluasi;
        $myJawaban = null;
        if ($evaluasi) {
            $myJawaban = JawabanEvaluasi::with('details.soal')
                ->where('evaluasi_id', $evaluasi->id)
                ->where('siswa_id', $siswa->id)
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail bahan ajar berhasil diambil.',
            'data' => [
                'bahan_ajar' => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'tipe_materi' => $item->tipe_materi,
                    'url_materi' => $item->url_materi,
                    'embed_url' => $item->embed_url,
                    'file_materi' => $item->file_materi ? url('storage/' . $item->file_materi) : null,
                    'teacher_name' => $item->guru->name ?? 'Guru Pengampu',
                    'class_name' => $item->kelas->nama_kelas ?? '-',
                    'subject_name' => $item->mapel->nama_mapel ?? '-',
                    'created_at' => $item->created_at ? $item->created_at->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm') . ' WIT' : '-',
                ],
                'evaluasi' => $evaluasi ? [
                    'id' => $evaluasi->id,
                    'judul_evaluasi' => $evaluasi->judul_evaluasi,
                    'instruksi' => $evaluasi->instruksi,
                    'poin_maksimal' => $evaluasi->poin_maksimal,
                    'total_soal' => $evaluasi->soal->count(),
                    'soal' => $evaluasi->soal->map(function ($s) use ($myJawaban) {
                        $myDetail = $myJawaban ? $myJawaban->details->firstWhere('soal_evaluasi_id', $s->id) : null;
                        return [
                            'id' => $s->id,
                            'nomor_urut' => $s->nomor_urut,
                            'pertanyaan' => $s->pertanyaan,
                            'tipe_soal' => $s->tipe_soal,
                            'opsi_a' => $s->opsi_a,
                            'opsi_b' => $s->opsi_b,
                            'opsi_c' => $s->opsi_c,
                            'opsi_d' => $s->opsi_d,
                            'opsi_e' => $s->opsi_e,
                            'poin' => $s->poin,
                            'jawaban_saya' => $myDetail?->jawaban_siswa,
                            'nilai_soal' => $myDetail?->nilai,
                        ];
                    }),
                ] : null,
                'my_submission' => $myJawaban ? [
                    'id' => $myJawaban->id,
                    'status' => $myJawaban->status, // dikerjakan, dinilai
                    'total_nilai' => $myJawaban->total_nilai,
                    'catatan_guru' => $myJawaban->catatan_guru,
                    'waktu_submit' => $myJawaban->waktu_submit ? Carbon::parse($myJawaban->waktu_submit)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                    'waktu_dinilai' => $myJawaban->waktu_dinilai ? Carbon::parse($myJawaban->waktu_dinilai)->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIT' : null,
                ] : null,
            ],
        ]);
    }

    /**
     * Submit Jawaban Evaluasi (Siswa)
     * POST /api/siswa/bahan-ajar/{id}/evaluasi/submit
     */
    public function siswaSubmitEvaluasi(Request $request, $id)
    {
        $request->validate([
            'jawaban' => 'required|array', // [['soal_id' => 1, 'jawaban' => 'A']]
            'jawaban.*.soal_id' => 'required|exists:soal_evaluasi,id',
            'jawaban.*.jawaban' => 'nullable|string',
        ]);

        $user = $request->user();
        $siswa = Siswa::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nis' => $user->nis ?? '0000',
                'kelas_id' => 1,
                'jenis_kelamin' => 'L',
            ]
        );

        $bahanAjar = BahanAjar::with(['guru', 'evaluasi.soal'])->findOrFail($id);
        $evaluasi = $bahanAjar->evaluasi;

        if (!$evaluasi) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan ajar ini tidak memiliki lembar evaluasi.',
            ], 404);
        }

        $now = Carbon::now('Asia/Jayapura');

        DB::beginTransaction();
        try {
            $jawabanEvaluasi = JawabanEvaluasi::updateOrCreate(
                [
                    'evaluasi_id' => $evaluasi->id,
                    'siswa_id' => $siswa->id,
                ],
                [
                    'status' => 'dikerjakan',
                    'waktu_submit' => $now,
                ]
            );

            $autoScoreTotal = 0;
            $hasEssay = false;

            $soalList = $evaluasi->soal->keyBy('id');

            foreach ($request->jawaban as $item) {
                $soalId = $item['soal_id'];
                $jawabanSiswa = $item['jawaban'] ?? null;
                $soal = $soalList->get($soalId);

                $nilaiSoal = null;

                // Auto-grading untuk Pilihan Ganda
                if ($soal && $soal->tipe_soal === 'pilihan_ganda' && !empty($soal->kunci_jawaban)) {
                    if (strtoupper(trim($jawabanSiswa ?? '')) === strtoupper(trim($soal->kunci_jawaban))) {
                        $nilaiSoal = $soal->poin;
                        $autoScoreTotal += $soal->poin;
                    } else {
                        $nilaiSoal = 0;
                    }
                } elseif ($soal && $soal->tipe_soal === 'esai') {
                    $hasEssay = true;
                }

                DetailJawabanEvaluasi::updateOrCreate(
                    [
                        'jawaban_evaluasi_id' => $jawabanEvaluasi->id,
                        'soal_evaluasi_id' => $soalId,
                    ],
                    [
                        'jawaban_siswa' => $jawabanSiswa,
                        'nilai' => $nilaiSoal,
                    ]
                );
            }

            // Jika semua soal adalah Pilihan Ganda, status langsung dinilai otomatis!
            if (!$hasEssay && $evaluasi->soal->count() > 0) {
                $jawabanEvaluasi->update([
                    'total_nilai' => $autoScoreTotal,
                    'status' => 'dinilai',
                    'waktu_dinilai' => $now,
                ]);
            }

            DB::commit();

            // Push Notification ke Guru
            try {
                $teacherToken = $bahanAjar->guru?->fcm_token ?? User::where('id', $bahanAjar->guru_id)->value('fcm_token');
                if ($teacherToken) {
                    $this->fcmService->sendToDevice(
                        $teacherToken,
                        "📝 Evaluasi Dikerjakan: {$user->name}",
                        "{$user->name} telah menyelesaikan evaluasi bahan ajar \"{$bahanAjar->judul}\". Periksa hasil pengerjaannya di aplikasi.",
                        [
                            'type' => 'evaluasi_submitted',
                            'bahan_ajar_id' => (string)$bahanAjar->id,
                            'siswa_id' => (string)$siswa->id,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim FCM evaluasi_submitted: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Jawaban evaluasi berhasil dikirimkan ke Guru.',
                'data' => $jawabanEvaluasi->fresh()->load('details'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim jawaban evaluasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}

// akhir batas suci yang kamu ubah
