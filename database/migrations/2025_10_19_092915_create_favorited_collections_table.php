<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('favorited_collections', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->timestamp('favorited_date');
            $table->primary(['collection_id', 'user_id']);
            $table->softDeletes();
            $table->index('favorited_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorited_collections');
    }
};
