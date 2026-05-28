<?php

namespace App\Models\MCP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMessage extends Model
{
    use HasFactory;

    protected $table = 'mcp_conversation_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
        'token_count',
        'provider',
        'model',
    ];

    protected $casts = [
        'metadata' => 'array',
        'token_count' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
