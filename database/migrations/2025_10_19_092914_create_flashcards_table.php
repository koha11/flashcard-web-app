<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->text('definition');
            $table->timestamps();
            $table->softDeletes(); // <— NEW
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};
