<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recent_collections', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->timestamp('viewed_date');
            $table->primary(['collection_id', 'user_id']);
            $table->index('viewed_date');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recent_collections');
    }
};
