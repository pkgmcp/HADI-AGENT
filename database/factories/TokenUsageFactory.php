<?php

namespace Database\Factories;

use App\Models\MCP\TokenUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TokenUsageFactory extends Factory
{
    protected $model = TokenUsage::class;

    public function definition(): array
    {
        return [
            'provider' => $this->faker->randomElement(['openai', 'anthropic', 'ollama']),
            'model' => $this->faker->randomElement(['gpt-4o', 'claude-3', 'llama2']),
            'input_tokens' => $this->faker->numberBetween(10, 5000),
            'output_tokens' => $this->faker->numberBetween(10, 2000),
            'cost' => $this->faker->randomFloat(6, 0, 0.1),
            'latency_ms' => $this->faker->randomFloat(2, 50, 5000),
            'session_id' => 'sess_' . $this->faker->uuid,
            'action' => $this->faker->randomElement(['mcp.handle', 'agent.execute', 'test']),
            'metadata' => [],
        ];
    }
}
