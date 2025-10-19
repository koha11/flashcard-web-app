<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->string('front_side');
            $table->text('back_side');
            $table->string('tags')->nullable(); // "English,Dev,Remote job"
            $table->timestamps();
            $table->softDeletes(); // <— NEW
            $table->index('user_id');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
