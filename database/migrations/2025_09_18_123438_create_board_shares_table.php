<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('board_shares', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignUuid('board_id')->constrained()->onDelete('cascade');
            $blueprint->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $blueprint->timestamps();

            // Ensure a user can only be shared a board once
            $blueprint->unique(['board_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_shares');
    }
};
