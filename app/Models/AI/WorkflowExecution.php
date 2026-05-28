<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowExecution extends Model
{
    use HasFactory;

    protected $table = 'ai_workflow_executions';

    protected $fillable = [
        'name',
        'steps',
        'context',
        'results',
        'errors',
        'status',
        'current_step',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'steps' => 'array',
        'context' => 'array',
        'results' => 'array',
        'errors' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function scopeRunning($query)
    {
        return $query->whereIn('status', ['running', 'created']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
