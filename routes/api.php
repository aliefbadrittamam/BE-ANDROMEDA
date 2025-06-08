<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MateriController;
use App\Http\Controllers\Api\SoalController;
use App\Http\Controllers\Api\KuisController;

Route::prefix('auth')->group(function () {
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
    Route::post('user/login', [AuthController::class, 'userLogin']);
    Route::post('user/register', [AuthController::class, 'userRegister']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('profile', [AdminController::class, 'profile']);
    Route::put('profile', [AdminController::class, 'updateProfile']);
    
    // Materi Management
    Route::apiResource('materi', MateriController::class);
    Route::get('materi/search/{query}', [MateriController::class, 'search']);
    
    // Soal Management
    Route::apiResource('soal', SoalController::class);
    
    // Kuis Management
    Route::apiResource('kuis', KuisController::class);
    Route::post('kuis/{kuis}/add-soal', [KuisController::class, 'addSoal']);
    Route::delete('kuis/{kuis}/remove-soal/{soal}', [KuisController::class, 'removeSoal']);
    
    // User Answers Monitoring
    Route::get('user-answers', [AdminController::class, 'getUserAnswers']);
    Route::get('quiz-results', [AdminController::class, 'getQuizResults']);
});

// User Routes
Route::prefix('user')->middleware(['auth:sanctum', 'user'])->group(function () {
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    
    // Materi Access
    Route::get('materi', [MateriController::class, 'userIndex']);
    Route::get('materi/{id}', [MateriController::class, 'userShow']);
    
    // Quiz Access
    Route::get('kuis', [KuisController::class, 'userIndex']);
    Route::get('kuis/{id}', [KuisController::class, 'userShow']);
    Route::post('kuis/{id}/start', [KuisController::class, 'startQuiz']);
    Route::post('kuis/{kuis}/answer', [KuisController::class, 'submitAnswer']);
    Route::get('kuis/{id}/result', [KuisController::class, 'getResult']);
});

