<?php

namespace App\Models\MCP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenUsage extends Model
{
    use HasFactory;

    protected $table = 'mcp_token_usage';

    protected $fillable = [
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'cost',
        'latency_ms',
        'session_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'float',
        'latency_ms' => 'float',
        'metadata' => 'array',
    ];

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
