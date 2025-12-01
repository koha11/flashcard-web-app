<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\UserController;

Route::prefix('collections')->group(function () {
    Route::get('/search', [CollectionController::class, 'search']);
    Route::get('/{id}', [CollectionController::class, 'show']);



    // Require auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [CollectionController::class, 'index']);
        Route::post('/', [CollectionController::class, 'store']);
        Route::post('/extract-paragraph', [CollectionController::class, 'extract']);
        Route::post('/auto-gen', [CollectionController::class, 'autoGenBaseOnDescription']);
        Route::post('/{collection}/flashcards', [CollectionController::class, 'storeFlashcard']);
        Route::post('/{collection}/favorite', [CollectionController::class, 'favorite']);


        Route::put('/{collection}', [CollectionController::class, 'update']);
        Route::put('/{collection}/flashcards', action: [CollectionController::class, 'updateFlashcard']);

        Route::delete("/{collection}", [CollectionController::class, 'destroy']);
        Route::delete("/{collection}/remove-flashcard/{flashcard_id}", [CollectionController::class, 'destroyFlashcard']);
    });

});


Route::prefix('users')->group(function () {
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', action: [UserController::class, 'getByEmail']);
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/login', action: [AuthController::class, 'login']);
    Route::post('/signup', action: [AuthController::class, 'signup']);
    Route::post('/forgot-password', action: [AuthController::class, 'forgotPassword']);
    Route::post('/email/verify', action: [EmailVerificationController::class, 'verify']);
    Route::post('/email/check', action: [EmailVerificationController::class, 'check']);

    // Require auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/change-password', action: [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// // Soft-delete helpers
// Route::get('collections/trashed', [CollectionController::class, 'trashed'])->name('collections.trashed');
// Route::post('collections/{id}/restore', [CollectionController::class, 'restore'])->name('collections.restore');
// Route::delete('collections/{id}/force', [CollectionController::class, 'forceDelete'])->name('collections.force-delete');

// Relationship: collections ↔ flashcards
// Route::get('collections/{collection}', [CollectionFlashcardController::class, 'index'])
//     ->name('collections.flashcards.index');
// Route::post('collections/{collection}/flashcards/{flashcard}', [CollectionFlashcardController::class, 'attach'])
//     ->name('collections.flashcards.attach');
// Route::delete('collections/{collection}/flashcards/{flashcard}', [CollectionFlashcardController::class, 'detach'])
//     ->name('collections.flashcards.detach');
// Route::post('collections/{collection}/flashcards/sync', [CollectionFlashcardController::class, 'sync'])
//     ->name('collections.flashcards.sync');