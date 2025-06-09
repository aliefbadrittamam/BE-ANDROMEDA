<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MateriController extends Controller
{
    // Helper method untuk log activity
    private function logUserActivity($activityType, $description)
    {
        $userId = Auth::id();
        if ($userId) {
            try {
                UserActivityLog::create([
                    'user_id' => $userId,
                    'activity_type' => $activityType,
                    'activity_description' => $description
                ]);
            } catch (\Exception $e) {
                // \Log::warning('Failed to log user activity: ' . $e->getMessage());
                error_log('Failed to log user activity: ' . $e->getMessage());
            }
        }
    }

    public function store(Request $request)
    {
        // Ambil admin ID langsung
        $adminId = Auth::id();
        if (!$adminId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:200',
            'konten_materi' => 'nullable',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['judul', 'konten_materi']);
        $data['admin_id'] = $adminId;

        // Handle file uploads
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('materi/images', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('materi/videos', 'public');
        }

        $materi = Materi::create($data);

        return response()->json([
            'message' => 'Materi created successfully',
            'data' => $materi->load('admin')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $materi = Materi::find($id);
        
        if (!$materi) {
            return response()->json(['message' => 'Materi not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:200',
            'konten_materi' => 'nullable',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['judul', 'konten_materi']);

        // Handle file uploads
        if ($request->hasFile('gambar')) {
            if ($materi->gambar) {
                Storage::disk('public')->delete($materi->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('materi/images', 'public');
        }

        if ($request->hasFile('video')) {
            if ($materi->video) {
                Storage::disk('public')->delete($materi->video);
            }
            $data['video'] = $request->file('video')->store('materi/videos', 'public');
        }

        // Update dengan cara yang aman
        $updated = $materi->update($data);

        if (!$updated) {
            return response()->json(['message' => 'Failed to update materi'], 500);
        }

        return response()->json([
            'message' => 'Materi updated successfully',
            'data' => $materi->fresh()->load('admin')
        ]);
    }

    // Rest of the methods remain the same...
    public function index()
    {
        $materi = Materi::with('admin')->latest()->get();
        return response()->json(['data' => $materi]);
    }

    public function show($id)
    {
        $materi = Materi::with('admin')->find($id);
        
        if (!$materi) {
            return response()->json(['message' => 'Materi not found'], 404);
        }
        
        return response()->json(['data' => $materi]);
    }

    public function destroy($id)
    {
        $materi = Materi::find($id);
        
        if (!$materi) {
            return response()->json(['message' => 'Materi not found'], 404);
        }

        // Delete files
        if ($materi->gambar) {
            Storage::disk('public')->delete($materi->gambar);
        }
        if ($materi->video) {
            Storage::disk('public')->delete($materi->video);
        }

        $materi->delete();

        return response()->json(['message' => 'Materi deleted successfully']);
    }

    public function search($query)
    {
        $materi = Materi::with('admin')
            ->where('judul', 'LIKE', "%{$query}%")
            ->orWhere('konten_materi', 'LIKE', "%{$query}%")
            ->get();

        return response()->json(['data' => $materi]);
    }

    public function userIndex()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $materi = Materi::select('id', 'judul', 'konten_materi', 'gambar', 'created_at')
            ->latest()
            ->get();

        $this->logUserActivity('view_materi', 'User accessed materi list');

        return response()->json(['data' => $materi]);
    }

    public function userShow($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $materi = Materi::select('id', 'judul', 'konten_materi', 'gambar', 'video', 'created_at')
            ->find($id);

        if (!$materi) {
            return response()->json(['message' => 'Materi not found'], 404);
        }

        $this->logUserActivity('view_materi', "User viewed materi: {$materi->judul}");

        return response()->json(['data' => $materi]);
    }
}