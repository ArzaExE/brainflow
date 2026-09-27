<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-022 – TC-025  Gestione task CRUD e spostamento (REQ-007 / REQ-008)
 */
class TaskCRUDAndMoveTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
    }

    /** Helper: progetto con PM e colonna "Da fare" */
    private function makeProjectWithColumn(User $pm): array
    {
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        return [$project, $column];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-022  Creazione task con creatore come assegnatario di default
    //
    // Il frontend invia sempre almeno un assignee_id (default: il creatore).
    // Il test verifica che il task venga salvato con il creatore come assegnatario.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithCreatorAsDefaultAssignee(): void
    {
        $pm = $this->makeUser();
        [$project, $column] = $this->makeProjectWithColumn($pm);

        $response = $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => 'Implementare login',
                'priority'     => 'high',
                'column_id'    => $column->id,
                'assignee_ids' => [$pm->id],   // creatore come assegnatario di default
            ]);

        $response->assertStatus(201);

        $task = $project->tasks()->where('title', 'Implementare login')->first();
        $this->assertNotNull($task);
        $this->assertSame($column->id, $task->column_id);

        // L'assegnatario registrato nella pivot è il creatore
        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $pm->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-023  Creazione task con assegnatario non membro del progetto
    //
    // StoreTaskRequest verifica che ogni assignee_id sia presente nella pivot
    // project_user con il project_id corretto e senza soft-delete.
    // Un utente non membro deve causare un errore 422.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithNonMemberAssigneeIsRejected(): void
    {
        $pm        = $this->makeUser();
        $nonMember = User::factory()->create(['email_verified_at' => now()]);

        [$project, $column] = $this->makeProjectWithColumn($pm);

        $response = $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => 'Task con assegnatario esterno',
                'priority'     => 'medium',
                'column_id'    => $column->id,
                'assignee_ids' => [$nonMember->id],
            ]);

        // L'assegnatario non è membro del progetto: la creazione deve essere rifiutata
        $response->assertStatus(422);
        $this->assertDatabaseMissing('tasks', ['title' => 'Task con assegnatario esterno']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-024  Modifica ed eliminazione task
    //
    // NOTA: La conferma prima dell'eliminazione è un comportamento UI (modal);
    //       il test verifica che la richiesta DELETE rimuova il task dal DB.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskUpdateAndDeletion(): void
    {
        $pm = $this->makeUser();
        [$project, $column] = $this->makeProjectWithColumn($pm);

        $task = $project->tasks()->create([
            'title'     => 'Implementare login',
            'priority'  => 'high',
            'column_id' => $column->id,
            'position'  => 0,
        ]);
        $task->assignees()->attach($pm->id);

        // Modifica descrizione e priorità
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => 'Implementare login',
                'description'  => 'Form con validazione',
                'priority'     => 'medium',
                'assignee_ids' => [$pm->id],
            ]);

        $updateResponse->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'description' => 'Form con validazione',
            'priority'    => 'medium',
        ]);

        // Eliminazione task (solo PM)
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('tasks.destroy', [$project, $task]));

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-025  Spostamento task tra colonne dall'interfaccia
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskMoveBetweenColumns(): void
    {
        $pm = $this->makeUser();
        [$project, $fromCol] = $this->makeProjectWithColumn($pm);

        $toCol = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'In corso',
            'position'   => 2,
            'is_done'    => false,
        ]);

        $task = $project->tasks()->create([
            'title'     => 'Implementare login',
            'priority'  => 'high',
            'column_id' => $fromCol->id,
            'position'  => 0,
        ]);
        $task->assignees()->attach($pm->id);

        $response = $this->actingAs($pm)
            ->patchJson(route('tasks.move', [$project, $task]), [
                'column_id' => $toCol->id,
            ]);

        $response->assertOk();

        // Nel DB il column_id è aggiornato alla colonna di destinazione
        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'column_id' => $toCol->id,
        ]);
    }
}
