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
        Schema::create('repositories', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('board_id')->unique()->constrained()->onDelete('cascade');
            $blueprint->string('provider')->default('github');
            $blueprint->string('full_name'); // e.g. "owner/repo"
            $blueprint->string('default_branch')->default('main');
            $blueprint->text('access_token'); // encrypted at rest via model cast
            $blueprint->foreignUuid('connected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
