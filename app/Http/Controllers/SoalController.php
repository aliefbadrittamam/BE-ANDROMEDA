<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SoalController extends Controller
{
    public function index()
    {
        $soal = Soal::with('admin')->latest()->get();
        return response()->json(['data' => $soal]);
    }

    public function store(Request $request)
    {
        $admin = Auth::user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:200',
            'pertanyaan' => 'required',
            'pilihan_a' => 'required|max:500',
            'pilihan_b' => 'required|max:500',
            'pilihan_c' => 'required|max:500',
            'pilihan_d' => 'required|max:500',
            'jawaban_benar' => 'required|in:A,B,C,D',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'judul', 'pertanyaan', 'pilihan_a', 'pilihan_b', 
            'pilihan_c', 'pilihan_d', 'jawaban_benar'
        ]);
        $data['admin_id'] = $admin->id;

        // Handle file uploads
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('soal/images', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('soal/videos', 'public');
        }

        $soal = Soal::create($data);

        return response()->json([
            'message' => 'Soal created successfully',
            'data' => $soal->load('admin')
        ], 201);
    }

    public function show($id)
    {
        $soal = Soal::with('admin')->find($id);
        
        if (!$soal) {
            return response()->json(['message' => 'Soal not found'], 404);
        }
        
        return response()->json(['data' => $soal]);
    }

    public function update(Request $request, $id)
    {
        $soal = Soal::find($id);
        
        if (!$soal) {
            return response()->json(['message' => 'Soal not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|max:200',
            'pertanyaan' => 'required',
            'pilihan_a' => 'required|max:500',
            'pilihan_b' => 'required|max:500',
            'pilihan_c' => 'required|max:500',
            'pilihan_d' => 'required|max:500',
            'jawaban_benar' => 'required|in:A,B,C,D',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only([
            'judul', 'pertanyaan', 'pilihan_a', 'pilihan_b', 
            'pilihan_c', 'pilihan_d', 'jawaban_benar'
        ]);

        // Handle file uploads
        if ($request->hasFile('gambar')) {
            if ($soal->gambar) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('soal/images', 'public');
        }

        if ($request->hasFile('video')) {
            if ($soal->video) {
                Storage::disk('public')->delete($soal->video);
            }
            $data['video'] = $request->file('video')->store('soal/videos', 'public');
        }

        $soal->update($data);

        return response()->json([
            'message' => 'Soal updated successfully',
            'data' => $soal->load('admin')
        ]);
    }

    public function destroy($id)
    {
        $soal = Soal::find($id);
        
        if (!$soal) {
            return response()->json(['message' => 'Soal not found'], 404);
        }

        // Delete files
        if ($soal->gambar) {
            Storage::disk('public')->delete($soal->gambar);
        }
        if ($soal->video) {
            Storage::disk('public')->delete($soal->video);
        }

        $soal->delete();

        return response()->json(['message' => 'Soal deleted successfully']);
    }
}