<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcp_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('mcp_conversations')->cascadeOnDelete();
            $table->string('role');
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->integer('token_count')->default(0);
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_conversation_messages');
    }
};
