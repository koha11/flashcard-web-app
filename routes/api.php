<?php

use App\Http\Controllers\CollectionFlashcardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HealthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FlashcardController;

Route::prefix('collections')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::get('/{collection}', [CollectionController::class, 'show']);

    Route::post('/new', [CollectionController::class, 'store']);
    Route::post('/extract-paragraph', [CollectionController::class, 'extract']);
    Route::post('/{collection}/add-flashcards', [CollectionController::class, 'storeFlashcards']);

    Route::put('/{collection}/edit', [CollectionController::class, 'update']);
    Route::put('/{collection}/edit-flashcard', action: [CollectionController::class, 'updateFlashcard']);

    Route::delete("/{collection}/remove", [CollectionController::class, 'destroy']);
    Route::delete("/{collection}/remove-flashcard/{flashcard_id}", [CollectionController::class, 'destroyFlashcard']);
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