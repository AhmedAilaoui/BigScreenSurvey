<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\StatisticsController;

// Routes publiques admin
Route::middleware(['web'])->prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);
});

// Routes protégées admin
Route::middleware(['web', 'auth:sanctum', 'admin.auth'])->prefix('admin')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/me', [AdminController::class, 'me']);
    Route::get('/statistics', [StatisticsController::class, 'index']);
    Route::get('/statistics/{type}', [StatisticsController::class, 'getSpecificStats']);
});

// Routes publiques pour les surveys et les questions
Route::middleware(['web'])->group(function () {
    // Route publique pour consulter les réponses
    Route::get('/responses/{token}', [ResponseController::class, 'showPublic']);

    // Routes pour les surveys
    Route::prefix('surveys')->group(function () {
        Route::post('/', [SurveyController::class, 'store']);
        Route::get('/', [SurveyController::class, 'index']);
        Route::get('/{token}', [SurveyController::class, 'show']);
        Route::put('/{token}/complete', [SurveyController::class, 'complete']);
        Route::delete('/{token}', [SurveyController::class, 'destroy']);

        // Routes pour les réponses
        Route::get('/{token}/responses', [ResponseController::class, 'show']);
        Route::post('/{token}/responses', [ResponseController::class, 'store']);
    });

    // Route pour les questions
    Route::get('/questions', [ResponseController::class, 'getQuestions']);

    // Routes pour les réponses
    Route::get('/{token}/responses', [ResponseController::class, 'show']);
    Route::post('/{token}/responses', [ResponseController::class, 'store']);
});

// Routes publiques pour les questions - Utilisation du middleware web pour CSRF
Route::middleware(['web'])->group(function () {
    Route::get('/questions', [ResponseController::class, 'getQuestions']);
});
