<?php

namespace Database\Factories;

use App\Models\AI\AgentExecution;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentExecutionFactory extends Factory
{
    protected $model = AgentExecution::class;

    public function definition(): array
    {
        return [
            'agent_name' => $this->faker->randomElement([
                'PlannerAgent', 'CoderAgent', 'ReviewAgent', 'DebuggerAgent',
            ]),
            'agent_type' => $this->faker->randomElement(['planner', 'coder', 'reviewer', 'debugger']),
            'session_id' => 'sess_' . $this->faker->uuid,
            'task' => ['description' => $this->faker->sentence],
            'response' => ['output' => $this->faker->paragraph],
            'provider' => $this->faker->randomElement(['openai', 'anthropic']),
            'model' => $this->faker->randomElement(['gpt-4o', 'claude-3']),
            'input_tokens' => $this->faker->numberBetween(10, 5000),
            'output_tokens' => $this->faker->numberBetween(10, 2000),
            'cost' => $this->faker->randomFloat(6, 0, 0.1),
            'latency_ms' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement(['pending', 'running', 'completed', 'failed']),
            'error' => null,
            'metadata' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'completed']);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'failed',
            'error' => 'Something went wrong',
        ]);
    }
}
