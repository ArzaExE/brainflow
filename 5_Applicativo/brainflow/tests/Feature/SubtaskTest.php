<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-028  Gestione sotto-attività (checklist) (REQ-010)
 */
class SubtaskTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-028  Aggiunta, completamento e conteggio delle sotto-attività
    // ─────────────────────────────────────────────────────────────────────────
    public function testSubtaskAddCompleteAndCount(): void
    {
        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        $task = $project->tasks()->create([
            'title'     => 'Implementare login',
            'priority'  => 'high',
            'column_id' => $column->id,
            'position'  => 0,
        ]);
        $task->assignees()->attach($pm->id);

        // Aggiunta di due sotto-attività con titoli validi
        $addResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
                'assignee_ids' => [$pm->id],
                'subtasks'     => [
                    ['title' => 'Creare form',         'done' => false],
                    ['title' => 'Validare credenziali', 'done' => false],
                ],
            ]);

        $addResponse->assertOk();
        $this->assertSame(2, $task->fresh()->subtasks()->count());

        // Tentativo di aggiungere una sotto-attività con titolo vuoto → 422
        $invalidResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
                'assignee_ids' => [$pm->id],
                'subtasks'     => [
                    ['title' => '', 'done' => false],
                ],
            ]);

        $invalidResponse->assertStatus(422);

        // Contrassegna "Creare form" come completata mantenendo l'altra invariata
        $updatedSubtasks = $task->fresh()->subtasks->map(fn ($s) => [
            'title' => $s->title,
            'done'  => $s->title === 'Creare form',
        ])->values()->toArray();

        $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
                'assignee_ids' => [$pm->id],
                'subtasks'     => $updatedSubtasks,
            ]);

        $progress = $task->fresh()->subtasksProgress();
        $this->assertSame(2, $progress['total']);
        $this->assertSame(1, $progress['done']);
    }
}
