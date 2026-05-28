<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agent_executions', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name');
            $table->string('agent_type')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->json('task')->nullable();
            $table->json('response')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 12, 6)->default(0);
            $table->decimal('latency_ms', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->json('error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['agent_name', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_executions');
    }
};
