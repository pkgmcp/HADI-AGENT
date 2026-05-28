<?php

namespace App\Models\MCP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;
    protected $table = 'mcp_conversations';

    protected $fillable = [
        'session_id',
        'title',
        'provider',
        'model',
        'context',
        'metadata',
        'token_count',
        'cost',
        'status',
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'token_count' => 'integer',
        'cost' => 'float',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'conversation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
