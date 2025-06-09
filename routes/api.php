<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\SoalController;
use App\Http\Controllers\Api\KuisController;

// Public routes
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/user/login', [AuthController::class, 'userLogin']);
Route::post('/user/register', [AuthController::class, 'userRegister']);

// Admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::put('/profile', [AdminController::class, 'updateProfile']);
    Route::get('/user-answers', [AdminController::class, 'getUserAnswers']);
    Route::get('/quiz-results', [AdminController::class, 'getQuizResults']);
    
    // Materi management
    Route::apiResource('materi', MateriController::class);
    Route::get('materi/search/{query}', [MateriController::class, 'search']);
    
    // Soal management
    Route::apiResource('soal', SoalController::class);
    
    // Kuis management
    Route::apiResource('kuis', KuisController::class);
    Route::post('kuis/{kuisId}/soal', [KuisController::class, 'addSoal']);
    Route::delete('kuis/{kuisId}/soal/{soalId}', [KuisController::class, 'removeSoal']);
});

// User routes
Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
    // Materi untuk user
    Route::get('/materi', [MateriController::class, 'userIndex']);
    Route::get('/materi/{id}', [MateriController::class, 'userShow']);
    
    // Kuis untuk user
    Route::get('/kuis', [KuisController::class, 'userIndex']);
    Route::get('/kuis/{id}', [KuisController::class, 'userShow']);
    Route::post('/kuis/{id}/start', [KuisController::class, 'startQuiz']);
    Route::post('/kuis/{kuisId}/submit', [KuisController::class, 'submitAnswer']);
    Route::get('/kuis/{id}/result', [KuisController::class, 'getResult']);
});

// Logout route (untuk semua user)
Route::middleware(['auth:sanctum'])->post('/logout', [AuthController::class, 'logout']);