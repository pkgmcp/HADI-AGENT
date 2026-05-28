<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'config' => $this->getConfig()->toArray(),
            'can_handle' => $this->canHandle($request->input('task', '')),
        ];
    }
}
