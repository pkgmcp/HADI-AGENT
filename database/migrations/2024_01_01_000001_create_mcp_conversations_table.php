<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->string('title')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->json('context')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('token_count')->default(0);
            $table->decimal('cost', 12, 6)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_conversations');
    }
};
