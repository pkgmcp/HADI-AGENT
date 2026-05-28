<?php

namespace App\Services\MCP;

use App\DTOs\AIRequestDTO;
use App\DTOs\AIResponseDTO;
use App\DTOs\MCPMessageDTO;
use Illuminate\Support\Facades\Http;

class MCPClient
{
    private string $endpoint;

    private array $headers = [];

    private int $timeout;

    public function __construct()
    {
        $this->endpoint = config('mcp.client_endpoint', 'http://localhost:8000/api/mcp');
        $this->timeout = config('mcp.client_timeout', 30);
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function send(MCPMessageDTO $message): AIResponseDTO
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post("{$this->endpoint}/messages", $message->toArray());

        if ($response->failed()) {
            throw new \RuntimeException(
                "MCP Client request failed: {$response->status()} - {$response->body()}"
            );
        }

        return AIResponseDTO::fromArray($response->json());
    }

    public function stream(MCPMessageDTO $message): iterable
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->withOptions(['stream' => true])
            ->post("{$this->endpoint}/messages/stream", $message->toArray());

        if ($response->failed()) {
            throw new \RuntimeException(
                "MCP Client stream failed: {$response->status()} - {$response->body()}"
            );
        }

        $body = $response->toPsrResponse()->getBody();

        while (!$body->eof()) {
            $chunk = $body->read(1024);
            if ($chunk) {
                yield $chunk;
            }
        }
    }

    public function broadcast(MCPMessageDTO $message): array
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->post("{$this->endpoint}/broadcast", $message->toArray());

        if ($response->failed()) {
            throw new \RuntimeException(
                "MCP Client broadcast failed: {$response->status()} - {$response->body()}"
            );
        }

        return $response->json();
    }

    public function status(): array
    {
        $response = Http::withHeaders($this->headers)
            ->timeout($this->timeout)
            ->get("{$this->endpoint}/status");

        if ($response->failed()) {
            return ['error' => "Status check failed: {$response->status()}"];
        }

        return $response->json();
    }

    public function health(): array
    {
        $response = Http::withHeaders($this->headers)
            ->timeout(5)
            ->get("{$this->endpoint}/health");

        return [
            'reachable' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->successful() ? $response->json() : null,
        ];
    }
}
