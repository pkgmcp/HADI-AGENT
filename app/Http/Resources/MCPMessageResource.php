<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MCPMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'total_tokens' => $this->whenHas('totalTokens', fn() => $this->totalTokens()),
            'cost' => $this->cost,
            'latency_ms' => $this->latencyMs,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
