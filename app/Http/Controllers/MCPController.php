<?php

namespace App\Http\Controllers;

use App\DTOs\MCPMessageDTO;
use App\Http\Requests\MCPMessageRequest;
use App\Http\Resources\MCPMessageResource;
use App\Services\MCP\MCPServer;
use Illuminate\Http\JsonResponse;

class MCPController extends Controller
{
    private MCPServer $mcpServer;

    public function __construct(MCPServer $mcpServer)
    {
        $this->mcpServer = $mcpServer;
    }

    public function handle(MCPMessageRequest $request): MCPMessageResource
    {
        $message = MCPMessageDTO::fromArray($request->validated());

        $response = $this->mcpServer->handle($message);

        return new MCPMessageResource($response);
    }

    public function broadcast(MCPMessageRequest $request): JsonResponse
    {
        $message = MCPMessageDTO::fromArray($request->validated());

        $responses = $this->mcpServer->broadcast($message);

        return response()->json([
            'data' => collect($responses)->map(fn($r, $p) => [
                'provider' => $p,
                'response' => $r instanceof \App\DTOs\AIResponseDTO
                    ? new MCPMessageResource($r)
                    : $r,
            ])->values(),
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'data' => $this->mcpServer->status(),
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'data' => $this->mcpServer->health(),
        ]);
    }

    public function providers(): JsonResponse
    {
        $providers = [];
        foreach ($this->mcpServer->getAvailableProviders() as $name => $provider) {
            $providers[] = [
                'name' => $name,
                'models' => $provider->models(),
                'available' => $provider->isAvailable(),
            ];
        }

        return response()->json([
            'data' => $providers,
        ]);
    }
}
