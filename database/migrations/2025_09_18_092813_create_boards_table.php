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
        Schema::create('boards', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->string('name');
            $blueprint->text('description')->nullable();
            $blueprint->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
