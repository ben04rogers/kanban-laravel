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
        Schema::create('comments', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->text('content');
            $blueprint->foreignUuid('card_id')->constrained()->onDelete('cascade');
            $blueprint->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
