<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tags')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->enum('access_level', ['private', 'public', 'restrict'])->default('private');
            $table->unsignedInteger('played_count')->default(0);
            $table->unsignedInteger('favorited_count')->default(0);
            $table->timestamps();
            $table->softDeletes(); // <— NEW
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
