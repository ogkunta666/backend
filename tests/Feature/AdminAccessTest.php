<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access admin users list
     */
    public function test_admin_can_access_users_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        User::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    '*' => ['id', 'name', 'email', 'is_admin']
                ]
            ]);
    }

    /**
     * Test non-admin cannot access admin users list
     */
    public function test_non_admin_cannot_access_users_list(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Forbidden. Admin access required.'
            ]);
    }

    /**
     * Test unauthenticated user cannot access admin routes
     */
    public function test_unauthenticated_cannot_access_admin_routes(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }

    /**
     * Test admin can create new user
     */
    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'department' => 'IT',
            'phone' => '+36 30 999 8888',
            'is_admin' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test non-admin cannot create user
     */
    public function test_non_admin_cannot_create_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can access tasks list
     */
    public function test_admin_can_access_tasks_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        Task::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    '*' => ['id', 'title', 'description', 'priority']
                ]
            ]);
    }

    /**
     * Test non-admin cannot access admin tasks list
     */
    public function test_non_admin_cannot_access_admin_tasks(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/tasks');

        $response->assertStatus(403);
    }

    /**
     * Test admin can delete user (soft delete)
     */
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;
        
        $userToDelete = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/users/{$userToDelete->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully'
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $userToDelete->id,
        ]);
    }

    /**
     * Test non-admin cannot delete user
     */
    public function test_non_admin_cannot_delete_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-token')->plainTextToken;
        
        $userToDelete = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/users/{$userToDelete->id}");

        $response->assertStatus(403);
    }

    /**
     * Test admin can create task
     */
    public function test_admin_can_create_task(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => 'high',
            'due_date' => '2026-03-01',
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'task' => ['id', 'title', 'priority']
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'priority' => 'high',
        ]);
    }
}
