<?php

use App\Http\Controllers\CollectionFlashcardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HealthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FlashcardController;



Route::prefix('v1')->group(function () {
    Route::apiResource('flashcards', FlashcardController::class);

    Route::get('flashcards/trashed', [FlashcardController::class, 'trashed'])->name('flashcards.trashed');
    Route::post('flashcards/{id}/restore', [FlashcardController::class, 'restore'])->name('flashcards.restore');
    Route::delete('flashcards/{id}/force', [FlashcardController::class, 'forceDelete'])->name('flashcards.force-delete');


    // CRUD
    Route::apiResource('collections', CollectionController::class);

    // Soft-delete helpers
    Route::get('collections/trashed', [CollectionController::class, 'trashed'])->name('collections.trashed');
    Route::post('collections/{id}/restore', [CollectionController::class, 'restore'])->name('collections.restore');
    Route::delete('collections/{id}/force', [CollectionController::class, 'forceDelete'])->name('collections.force-delete');

    // Relationship: collections ↔ flashcards
    Route::get('collections/{collection}/flashcards', [CollectionFlashcardController::class, 'index'])
        ->name('collections.flashcards.index');
    Route::post('collections/{collection}/flashcards/{flashcard}', [CollectionFlashcardController::class, 'attach'])
        ->name('collections.flashcards.attach');
    Route::delete('collections/{collection}/flashcards/{flashcard}', [CollectionFlashcardController::class, 'detach'])
        ->name('collections.flashcards.detach');
    Route::post('collections/{collection}/flashcards/sync', [CollectionFlashcardController::class, 'sync'])
        ->name('collections.flashcards.sync');
});