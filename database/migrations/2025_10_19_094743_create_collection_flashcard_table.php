<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collection_flashcard', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreignId('flashcard_id')->constrained('flashcards')->cascadeOnDelete()->restrictOnUpdate();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['collection_id', 'flashcard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_flashcard');
    }
};
