<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collection_access_users', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->restrictOnUpdate();
            $table->boolean('can_edit')->default(false);
            $table->softDeletes();
            $table->primary(['collection_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_access_users');
    }
};
