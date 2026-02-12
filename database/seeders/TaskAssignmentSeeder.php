<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Task_assignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        // Ha nincsenek userek vagy taskok, ne csináljunk semmit
        if ($users->isEmpty() || $tasks->isEmpty()) {
            return;
        }

        // Minden taskhoz hozzárendelünk 1-3 usert
        foreach ($tasks as $task) {
            $assignedUserCount = rand(1, 3);
            $selectedUsers = $users->random(min($assignedUserCount, $users->count()));

            foreach ($selectedUsers as $user) {
                // Eldöntjük, hogy a task státusza alapján befejezett-e az assignment
                $isCompleted = in_array($task->status, ['completed', 'cancelled']);

                if ($isCompleted) {
                    Task_assignment::factory()
                        ->forUser($user->id)
                        ->forTask($task->id)
                        ->completed()
                        ->create();
                } else {
                    // Pending és in_progress taskoknál random, hogy ki fejezte be
                    $randomComplete = rand(1, 100) <= 30; // 30% esély
                    
                    if ($randomComplete) {
                        Task_assignment::factory()
                            ->forUser($user->id)
                            ->forTask($task->id)
                            ->completed()
                            ->create();
                    } else {
                        Task_assignment::factory()
                            ->forUser($user->id)
                            ->forTask($task->id)
                            ->notCompleted()
                            ->create();
                    }
                }
            }
        }
    }
}

