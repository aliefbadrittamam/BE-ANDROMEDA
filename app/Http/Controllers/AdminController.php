<?php
// AdminController.php - Fixed Version
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\UserJawaban;
use App\Models\HasilKuis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function profile()
    {
        $admin = Auth::user();
        if (!$admin || !($admin instanceof Admin)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        return response()->json(['data' => $admin]);
    }

    public function updateProfile(Request $request)
    {
        // Ambil admin dari database langsung
        $adminId = Auth::id();
        if (!$adminId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:admin,email,' . $admin->id,
            'nama_lengkap' => 'nullable|max:100',
            'password' => 'nullable|min:6',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['email', 'nama_lengkap']);

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto_profile')) {
            $data['foto_profile'] = $request->file('foto_profile')->store('admin/profile', 'public');
        }

        // Update dengan cara yang aman
        $admin->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $admin->fresh()
        ]);
    }

    public function getUserAnswers(Request $request)
    {
        $query = UserJawaban::with(['user', 'kuis', 'soal'])
            ->latest('waktu_jawab');

        if ($request->kuis_id) {
            $query->where('kuis_id', $request->kuis_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $userAnswers = $query->paginate(20);

        return response()->json($userAnswers);
    }

    public function getQuizResults(Request $request)
    {
        $query = HasilKuis::with(['user', 'kuis'])
            ->latest('created_at');

        if ($request->kuis_id) {
            $query->where('kuis_id', $request->kuis_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $results = $query->paginate(20);

        return response()->json($results);
    }
}