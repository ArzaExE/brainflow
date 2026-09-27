<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignment;
use App\Notifications\TaskDueSoon;
use App\Notifications\TaskMove;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * TC-032 – TC-034  Notifiche email (REQ-013)
 */
class EmailNotificationTest extends TestCase
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
    // TC-032  Notifica email all'assegnazione di un task
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskAssignmentSendsEmailNotification(): void
    {
        Notification::fake();

        $pm   = $this->makeUser();
        $luca = User::factory()->create([
            'email'             => 'luca.verdi@example.com',
            'email_verified_at' => now(),
        ]);

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,   ['role' => 'pm']);
        $project->members()->attach($luca->id, ['role' => 'developer']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => 'Implementare login',
                'priority'     => 'high',
                'column_id'    => $column->id,
                'assignee_ids' => [$luca->id],
            ]);

        Notification::assertSentTo($luca, TaskAssignment::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-033  Notifica email all'avvicinarsi della scadenza (scheduler)
    // ─────────────────────────────────────────────────────────────────────────
    public function testDueSoonNotificationSentByScheduler(): void
    {
        Notification::fake();

        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        // Task con scadenza esattamente domani
        $task = $project->tasks()->create([
            'title'     => 'Task in scadenza',
            'priority'  => 'high',
            'column_id' => $column->id,
            'position'  => 0,
            'due_date'  => Carbon::tomorrow()->toDateString(),
        ]);
        $task->assignees()->attach($pm->id);

        Artisan::call('tasks:notify-due-soon');

        Notification::assertSentTo($pm, TaskDueSoon::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-034  Notifica email al cambio di colonna/stato di un task assegnato
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskMoveSendsEmailNotificationToAssignee(): void
    {
        Notification::fake();

        $pm   = $this->makeUser();
        $luca = User::factory()->create([
            'email'             => 'luca.verdi@example.com',
            'email_verified_at' => now(),
        ]);

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,   ['role' => 'pm']);
        $project->members()->attach($luca->id, ['role' => 'developer']);

        $col1 = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);
        $col2 = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'In corso',
            'position'   => 2,
            'is_done'    => false,
        ]);

        $task = $project->tasks()->create([
            'title'     => 'Task assegnata a Luca',
            'priority'  => 'medium',
            'column_id' => $col1->id,
            'position'  => 0,
        ]);
        $task->assignees()->attach($luca->id);

        // Il PM sposta il task (diverso dall'assegnatario)
        $this->actingAs($pm)
            ->patchJson(route('tasks.move', [$project, $task]), [
                'column_id' => $col2->id,
            ]);

        Notification::assertSentTo($luca, TaskMove::class);
    }
}
