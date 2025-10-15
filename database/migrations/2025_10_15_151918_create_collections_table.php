<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users', 'id')->cascadeOnDelete()->restrictOnUpdate();
            $table->string('name');
            $table->string('tags')->nullable();
            $table->string('access_level');
            $table->integer('played_count')->default(0);
            $table->integer('favorited_count')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index(columns: ['owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
