<?php

namespace Database\Seeders;

Use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Létrehozunk 10 task-ot különböző státuszokkal
        Task::factory()->pending()->create();
        Task::factory()->pending()->create();
        Task::factory()->inProgress()->create();
        Task::factory()->inProgress()->create();
        Task::factory()->inProgress()->create();
        Task::factory()->completed()->create();
        Task::factory()->completed()->create();
        Task::factory()->completed()->create();
        Task::factory()->cancelled()->create();
        Task::factory()->highPriority()->inProgress()->create();
    }
}
