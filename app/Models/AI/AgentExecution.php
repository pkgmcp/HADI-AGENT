<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentExecution extends Model
{
    use HasFactory;

    protected $table = 'ai_agent_executions';

    protected $fillable = [
        'agent_name',
        'agent_type',
        'session_id',
        'task',
        'response',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'cost',
        'latency_ms',
        'status',
        'error',
        'metadata',
    ];

    protected $casts = [
        'task' => 'array',
        'response' => 'array',
        'metadata' => 'array',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'float',
        'latency_ms' => 'float',
    ];

    public function scopeByAgent($query, string $agentName)
    {
        return $query->where('agent_name', $agentName);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
