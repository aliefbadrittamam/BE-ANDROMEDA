<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kuis;
use App\Models\Soal;
use App\Models\UserJawaban;
use App\Models\HasilKuis;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KuisController extends Controller
{
    // Helper method untuk log activity
    private function logUserActivity($activityType, $description)
    {
        $user = Auth::user();
        if ($user) {
            try {
                UserActivityLog::create([
                    'user_id' => $user->id,
                    'activity_type' => $activityType,
                    'activity_description' => $description
                ]);
            } catch (\Exception $e) {
                // Log error tapi jangan stop eksekusi
                // \Log::warning('Failed to log user activity: ' . $e->getMessage());
                error_log('Failed to log user activity: ' . $e->getMessage());}
        }
    }

    // Admin methods
    public function index()
    {
        $kuis = Kuis::with(['admin', 'soal'])->latest()->get();
        return response()->json(['data' => $kuis]);
    }

    public function store(Request $request)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'nama_kuis' => 'required|max:200',
            'deskripsi' => 'nullable',
            'deadline' => 'nullable|date|after:now',
            'durasi_menit' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nama_kuis', 'deskripsi', 'deadline', 'durasi_menit', 'status']);
        $data['admin_id'] = $admin->id;

        $kuis = Kuis::create($data);

        return response()->json([
            'message' => 'Kuis created successfully',
            'data' => $kuis->load('admin')
        ], 201);
    }

    public function show($id)
    {
        $kuis = Kuis::with(['admin', 'soal'])->find($id);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }
        
        return response()->json(['data' => $kuis]);
    }

    public function update(Request $request, $id)
    {
        $kuis = Kuis::find($id);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kuis' => 'required|max:200',
            'deskripsi' => 'nullable',
            'deadline' => 'nullable|date',
            'durasi_menit' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['nama_kuis', 'deskripsi', 'deadline', 'durasi_menit', 'status']);
        $kuis->update($data);

        return response()->json([
            'message' => 'Kuis updated successfully',
            'data' => $kuis->load('admin')
        ]);
    }

    public function destroy($id)
    {
        $kuis = Kuis::find($id);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }
        
        $kuis->delete();

        return response()->json(['message' => 'Kuis deleted successfully']);
    }

    public function addSoal(Request $request, $kuisId)
    {
        $validator = Validator::make($request->all(), [
            'soal_ids' => 'required|array',
            'soal_ids.*' => 'exists:soal,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kuis = Kuis::find($kuisId);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }
        
        foreach ($request->soal_ids as $index => $soalId) {
            $kuis->soal()->syncWithoutDetaching([
                $soalId => ['urutan' => $index + 1]
            ]);
        }

        return response()->json([
            'message' => 'Soal added to kuis successfully',
            'data' => $kuis->load('soal')
        ]);
    }

    public function removeSoal($kuisId, $soalId)
    {
        $kuis = Kuis::find($kuisId);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }
        
        $kuis->soal()->detach($soalId);

        return response()->json(['message' => 'Soal removed from kuis successfully']);
    }

    // User methods
    public function userIndex()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $kuis = Kuis::where('status', 'published')
            ->with(['soal' => function($query) {
                $query->select('soal.id', 'judul');
            }])
            ->select('id', 'nama_kuis', 'deskripsi', 'deadline', 'durasi_menit')
            ->latest()
            ->get();

        // Log activity dengan method yang aman
        $this->logUserActivity('view_kuis', 'User accessed kuis list');

        return response()->json(['data' => $kuis]);
    }

    public function userShow($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $kuis = Kuis::where('status', 'published')
            ->with(['soal' => function($query) {
                $query->select('soal.id', 'judul', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'gambar', 'video')
                      ->orderBy('kuis_soal.urutan');
            }])
            ->find($id);

        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }

        // Check if quiz is expired
        if ($kuis->isExpired()) {
            return response()->json(['message' => 'Kuis sudah melewati deadline'], 403);
        }

        // Check if user already completed this quiz
        $hasilKuis = HasilKuis::where('user_id', $user->id)
            ->where('kuis_id', $id)
            ->where('status', 'completed')
            ->first();

        if ($hasilKuis) {
            return response()->json(['message' => 'Anda sudah menyelesaikan kuis ini'], 403);
        }

        return response()->json(['data' => $kuis]);
    }

    public function startQuiz(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $kuis = Kuis::where('status', 'published')->find($id);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }

        // Check if quiz is expired
        if ($kuis->isExpired()) {
            return response()->json(['message' => 'Kuis sudah melewati deadline'], 403);
        }

        // Check if user already started this quiz
        $hasilKuis = HasilKuis::where('user_id', $user->id)
            ->where('kuis_id', $id)
            ->first();

        if ($hasilKuis && $hasilKuis->status !== 'ongoing') {
            return response()->json(['message' => 'Anda sudah menyelesaikan kuis ini'], 403);
        }

        // Create or update hasil_kuis record
        $hasilKuis = HasilKuis::updateOrCreate(
            [
                'user_id' => $user->id,
                'kuis_id' => $id
            ],
            [
                'waktu_mulai' => now(),
                'status' => 'ongoing',
                'total_soal' => $kuis->soal()->count()
            ]
        );

        // Log activity dengan method yang aman
        $this->logUserActivity('start_kuis', "User started kuis: {$kuis->nama_kuis}");

        return response()->json([
            'message' => 'Kuis started successfully',
            'data' => $hasilKuis
        ]);
    }

    public function submitAnswer(Request $request, $kuisId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'soal_id' => 'required|exists:soal,id',
            'jawaban_user' => 'required|in:A,B,C,D',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kuis = Kuis::find($kuisId);
        
        if (!$kuis) {
            return response()->json(['message' => 'Kuis not found'], 404);
        }
        
        // Check if quiz is expired
        if ($kuis->isExpired()) {
            return response()->json(['message' => 'Kuis sudah melewati deadline'], 403);
        }

        $soal = Soal::find($request->soal_id);
        
        if (!$soal) {
            return response()->json(['message' => 'Soal not found'], 404);
        }
        
        // Check if this soal belongs to the kuis
        if (!$kuis->soal()->where('soal.id', $request->soal_id)->exists()) {
            return response()->json(['message' => 'Soal tidak ditemukan dalam kuis ini'], 404);
        }

        $isCorrect = $request->jawaban_user === $soal->jawaban_benar;

        // Save user answer
        $userJawaban = UserJawaban::updateOrCreate(
            [
                'user_id' => $user->id,
                'kuis_id' => $kuisId,
                'soal_id' => $request->soal_id
            ],
            [
                'jawaban_user' => $request->jawaban_user,
                'is_correct' => $isCorrect,
                'waktu_jawab' => now()
            ]
        );

        // Update hasil kuis
        $this->updateHasilKuis($user->id, $kuisId);

        // Log activity dengan method yang aman
        $this->logUserActivity('submit_answer', "User answered question in kuis: {$kuis->nama_kuis}");

        return response()->json([
            'message' => 'Answer submitted successfully',
            'is_correct' => $isCorrect
        ]);
    }

    public function getResult($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $hasilKuis = HasilKuis::with('kuis')
            ->where('user_id', $user->id)
            ->where('kuis_id', $id)
            ->first();

        if (!$hasilKuis) {
            return response()->json(['message' => 'Hasil kuis not found'], 404);
        }

        $userJawaban = UserJawaban::with('soal')
            ->where('user_id', $user->id)
            ->where('kuis_id', $id)
            ->get();

        return response()->json([
            'hasil_kuis' => $hasilKuis,
            'detail_jawaban' => $userJawaban
        ]);
    }

    private function updateHasilKuis($userId, $kuisId)
    {
        $totalJawaban = UserJawaban::where('user_id', $userId)
            ->where('kuis_id', $kuisId)
            ->count();

        $jawaban_benar = UserJawaban::where('user_id', $userId)
            ->where('kuis_id', $kuisId)
            ->where('is_correct', true)
            ->count();

        $totalSoal = Kuis::find($kuisId)->soal()->count();
        $skor = $totalSoal > 0 ? round(($jawaban_benar / $totalSoal) * 100, 2) : 0;

        $status = $totalJawaban >= $totalSoal ? 'completed' : 'ongoing';

        HasilKuis::where('user_id', $userId)
            ->where('kuis_id', $kuisId)
            ->update([
                'jawaban_benar' => $jawaban_benar,
                'jawaban_salah' => $totalJawaban - $jawaban_benar,
                'skor' => $skor,
                'status' => $status,
                'waktu_selesai' => $status === 'completed' ? now() : null
            ]);
    }
}