<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('steps');
            $table->json('context')->nullable();
            $table->json('results')->nullable();
            $table->json('errors')->nullable();
            $table->string('status')->default('created');
            $table->integer('current_step')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_workflow_executions');
    }
};
