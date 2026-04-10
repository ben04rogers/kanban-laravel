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
        Schema::create('cards', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->string('title');
            $blueprint->text('description')->nullable();
            $blueprint->integer('position')->default(0);
            $blueprint->foreignUuid('board_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('board_column_id')->constrained()->onDelete('cascade');
            $blueprint->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
