<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Task_assignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can view their profile
     */
    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);
    }

    /**
     * Test unauthenticated user cannot view profile
     */
    public function test_unauthenticated_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /**
     * Test user can view their assigned tasks
     */
    public function test_user_can_view_own_tasks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $task1 = Task::factory()->create(['title' => 'User Task 1']);
        $task2 = Task::factory()->create(['title' => 'User Task 2']);
        $otherTask = Task::factory()->create(['title' => 'Other User Task']);

        Task_assignment::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task1->id,
        ]);
        Task_assignment::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task2->id,
        ]);
        
        // Another user's task
        $otherUser = User::factory()->create();
        Task_assignment::factory()->create([
            'user_id' => $otherUser->id,
            'task_id' => $otherTask->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-tasks');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'tasks')
            ->assertJsonFragment(['title' => 'User Task 1'])
            ->assertJsonFragment(['title' => 'User Task 2'])
            ->assertJsonMissing(['title' => 'Other User Task']);
    }

    /**
     * Test user can update their task status
     */
    public function test_user_can_update_task_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $task = Task::factory()->create();
        $assignment = Task_assignment::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_assignments', [
            'id' => $assignment->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test user cannot update status of task not assigned to them
     */
    public function test_user_cannot_update_other_users_task(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $otherUser = User::factory()->create();
        $task = Task::factory()->create();
        Task_assignment::factory()->create([
            'user_id' => $otherUser->id,
            'task_id' => $task->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test completed status automatically sets completed_at timestamp
     */
    public function test_completed_status_sets_timestamp(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $task = Task::factory()->create();
        $assignment = Task_assignment::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);

        $assignment->refresh();
        $this->assertNotNull($assignment->completed_at);
    }
}
