<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-029 – TC-031  Commenti/menzioni e activity log (REQ-011 / REQ-012)
 */
class ActivityLogTest extends TestCase
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
    // TC-029  Descrizione task con menzione @username salvata correttamente
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskDescriptionMentionIsPersisted(): void
    {
        $mario = $this->makeUser();
        $luca  = User::factory()->create([
            'name'              => 'luca.verdi',
            'email'             => 'luca.verdi@example.com',
            'email_verified_at' => now(),
        ]);

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($mario->id, ['role' => 'pm']);
        $project->members()->attach($luca->id,  ['role' => 'developer']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        $task = $project->tasks()->create([
            'title'     => 'Task di test',
            'priority'  => 'medium',
            'column_id' => $column->id,
            'position'  => 0,
        ]);
        $task->assignees()->attach($mario->id);

        $mentionText = '@luca.verdi puoi verificare?';

        $response = $this->actingAs($mario)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'description'  => $mentionText,
                'priority'     => $task->priority,
                'assignee_ids' => [$mario->id],
            ]);

        $response->assertOk();

        // La menzione deve essere riconosciuta e registrata.
        // Fallisce finché il parsing e la persistenza delle menzioni non esistono.
        $this->assertDatabaseHas('task_mentions', [
            'task_id' => $task->id,
            'user_id' => $luca->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-030  Registrazione degli eventi rilevanti nell'activity log
    // ─────────────────────────────────────────────────────────────────────────
    public function testActivityLogRecordsTaskEvents(): void
    {
        $pm      = $this->makeUser();
        $dev     = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,  ['role' => 'pm']);
        $project->members()->attach($dev->id, ['role' => 'developer']);

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

        // 1. Creazione task → log 'task.created'
        $createResponse = $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => 'Task log test',
                'priority'     => 'medium',
                'column_id'    => $col1->id,
                'assignee_ids' => [$dev->id],
            ]);

        $createResponse->assertStatus(201);
        $task = $project->tasks()->where('title', 'Task log test')->first();

        $this->assertDatabaseHas('activity_logs', [
            'project_id' => $project->id,
            'task_id'    => $task->id,
            'user_id'    => $pm->id,
            'type'       => 'task.created',
        ]);

        // 2. Spostamento task → log 'task.moved'
        $this->actingAs($pm)
            ->patchJson(route('tasks.move', [$project, $task]), [
                'column_id' => $col2->id,
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'project_id' => $project->id,
            'task_id'    => $task->id,
            'type'       => 'task.moved',
        ]);

        // 3. Aggiornamento con nuovo assegnatario → log 'task.assigned'
        //    Il PM viene aggiunto come nuovo assegnatario: addedIds = [$pm->id]
        $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
                'assignee_ids' => [$dev->id, $pm->id],
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'project_id' => $project->id,
            'task_id'    => $task->id,
            'type'       => 'task.assigned',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-031  Activity log accessibile a tutti i membri, incluso il Viewer
    // ─────────────────────────────────────────────────────────────────────────
    public function testActivityLogIsAccessibleToViewer(): void
    {
        $pm     = $this->makeUser();
        $viewer = $this->makeUser();

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,     ['role' => 'pm']);
        $project->members()->attach($viewer->id, ['role' => 'viewer']);

        // Voce di log pre-esistente
        ActivityLog::create([
            'project_id' => $project->id,
            'task_id'    => null,
            'user_id'    => $pm->id,
            'type'       => 'task.created',
            'meta'       => ['task_title' => 'Task di esempio'],
        ]);

        // Il Viewer può aprire la pagina del progetto (che include il log)
        $response = $this->actingAs($viewer)
            ->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertViewHas('activity');

        // Le voci nel log appartengono al progetto corrente
        $activityInView = $response->viewData('activity');
        $this->assertNotEmpty($activityInView);

        $types = collect($activityInView)->pluck('type')->toArray();
        $this->assertContains('task.created', $types);
    }
}
