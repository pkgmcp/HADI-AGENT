<?php

namespace Database\Factories;

use App\Models\MCP\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'session_id' => 'sess_' . $this->faker->uuid,
            'title' => $this->faker->sentence,
            'provider' => $this->faker->randomElement(['openai', 'anthropic', 'ollama']),
            'model' => $this->faker->randomElement(['gpt-4o', 'claude-3', 'llama2']),
            'context' => [],
            'metadata' => [],
            'token_count' => $this->faker->numberBetween(0, 10000),
            'cost' => $this->faker->randomFloat(6, 0, 1),
            'status' => 'active',
        ];
    }

    public function archived(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'archived']);
    }
}
