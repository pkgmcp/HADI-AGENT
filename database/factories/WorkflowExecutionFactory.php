<?php

namespace Database\Factories;

use App\Models\AI\WorkflowExecution;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowExecutionFactory extends Factory
{
    protected $model = WorkflowExecution::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'steps' => [
                ['name' => 'step_1', 'type' => 'prompt', 'prompt' => $this->faker->sentence],
            ],
            'context' => ['key' => 'value'],
            'results' => [],
            'errors' => [],
            'status' => 'created',
            'current_step' => 0,
            'started_at' => null,
            'completed_at' => null,
            'metadata' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'completed',
            'results' => ['step_1' => ['content' => $this->faker->paragraph]],
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attrs) => [
            'status' => 'failed',
            'errors' => [['step' => 'step_1', 'error' => 'Something went wrong']],
            'completed_at' => now(),
        ]);
    }
}
