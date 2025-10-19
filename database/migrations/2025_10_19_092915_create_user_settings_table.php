<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            // $table->json('preferences')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('user_id'); // one row per user (optional)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
