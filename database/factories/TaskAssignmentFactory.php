<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Task;
use App\Models\Task_assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task_assignment>
 */
class TaskAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task_assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assignedAt = fake()->dateTimeBetween('-2 months', 'now');
        $isCompleted = fake()->boolean(40); // 40% esély, hogy befejezett

        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'assigned_at' => $assignedAt,
            'completed_at' => $isCompleted ? fake()->dateTimeBetween($assignedAt, 'now') : null,
        ];
    }

    /**
     * Indicate that the assignment is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $assignedAt = $attributes['assigned_at'] ?? now()->subDays(10);
            
            return [
                'completed_at' => fake()->dateTimeBetween($assignedAt, 'now'),
            ];
        });
    }

    /**
     * Indicate that the assignment is not completed.
     */
    public function notCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the assignment is for a specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Indicate that the assignment is for a specific task.
     */
    public function forTask(int $taskId): static
    {
        return $this->state(fn (array $attributes) => [
            'task_id' => $taskId,
        ]);
    }
}
