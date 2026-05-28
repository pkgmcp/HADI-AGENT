<?php

namespace Database\Factories;

use App\Models\MCP\ConversationMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationMessageFactory extends Factory
{
    protected $model = ConversationMessage::class;

    public function definition(): array
    {
        return [
            'role' => $this->faker->randomElement(['user', 'assistant', 'system']),
            'content' => $this->faker->paragraph,
            'metadata' => [],
            'token_count' => $this->faker->numberBetween(0, 1000),
            'provider' => $this->faker->randomElement(['openai', 'anthropic']),
            'model' => $this->faker->randomElement(['gpt-4o', 'claude-3']),
        ];
    }

    public function user(): static
    {
        return $this->state(fn(array $attrs) => ['role' => 'user']);
    }

    public function assistant(): static
    {
        return $this->state(fn(array $attrs) => ['role' => 'assistant']);
    }
}
