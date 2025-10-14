<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->restrictOnUpdate();
            $table->string('front_side');
            $table->string('back_side');
            $table->string('tags')->nullable()->comment('CSV e.g. "English,Dev,Remote job"');
            $table->softDeletes();                 // ← merged in
            $table->timestamps();

            $table->index(['user_id']);            // keep any indexes you need
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
