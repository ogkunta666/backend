<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Task_assignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can assign task to user
     */
    public function test_admin_can_assign_task_to_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/assignments', [
            'user_id' => $user->id,
            'task_id' => $task->id,
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'assignment' => ['id', 'user_id', 'task_id', 'status']
            ]);

        $this->assertDatabaseHas('task_assignments', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
    }

    /**
     * Test non-admin cannot assign tasks
     */
    public function test_non_admin_cannot_assign_tasks(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-token')->plainTextToken;

        $otherUser = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/assignments', [
            'user_id' => $otherUser->id,
            'task_id' => $task->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can view all assignments
     */
    public function test_admin_can_view_all_assignments(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $users = User::factory()->count(3)->create();
        $tasks = Task::factory()->count(3)->create();

        foreach ($users as $index => $user) {
            Task_assignment::factory()->create([
                'user_id' => $user->id,
                'task_id' => $tasks[$index]->id,
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/assignments');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'assignments');
    }

    /**
     * Test admin can get assignments for specific task
     */
    public function test_admin_can_get_task_assignments(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $task = Task::factory()->create();
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Task_assignment::factory()->create([
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);
        }

        // Create assignment for different task
        $otherTask = Task::factory()->create();
        Task_assignment::factory()->create([
            'user_id' => $users[0]->id,
            'task_id' => $otherTask->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin/tasks/{$task->id}/assignments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'assignments');
    }

    /**
     * Test admin can get assignments for specific user
     */
    public function test_admin_can_get_user_assignments(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $user = User::factory()->create();
        $tasks = Task::factory()->count(2)->create();

        foreach ($tasks as $task) {
            Task_assignment::factory()->create([
                'user_id' => $user->id,
                'task_id' => $task->id,
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin/users/{$user->id}/assignments");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'assignments');
    }

    /**
     * Test cannot assign same task to user twice
     */
    public function test_cannot_duplicate_task_assignment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $user = User::factory()->create();
        $task = Task::factory()->create();

        Task_assignment::factory()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/assignments', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Task assignment already exists'
            ]);
    }

    /**
     * Test admin can update assignment
     */
    public function test_admin_can_update_assignment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $assignment = Task_assignment::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/assignments/{$assignment->id}", [
            'status' => 'completed',
            'completed_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_assignments', [
            'id' => $assignment->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test admin can delete assignment
     */
    public function test_admin_can_delete_assignment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $assignment = Task_assignment::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/assignments/{$assignment->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('task_assignments', [
            'id' => $assignment->id,
        ]);
    }
}
