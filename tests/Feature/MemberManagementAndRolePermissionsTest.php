<?php
namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-015 – TC-018  Gestione membri e permessi di ruolo (REQ-004 / REQ-005)
 */
class MemberManagementAndRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    /** Helper: utente verificato con ruolo di sistema 'user' */
    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role' => 'user',
            'email_verified_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-015  Il Project Manager invita un nuovo membro con ruolo Developer
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmInvitesNewMember(): void
    {
        $pm = $this->makeUser();
        $newUser = User::factory()->create(['email' => 'luca.verdi@example.com']);

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $response = $this->actingAs($pm)
            ->postJson(route('projects.members.store', $project), [
                'user_id' => $newUser->id,
                'role' => 'developer',
            ]);

        $response->assertStatus(201);

        // Il nuovo membro esiste nella pivot con ruolo developer
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
            'role' => 'developer',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-016  Il PM modifica il ruolo di un membro e poi lo rimuove
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmUpdatesRoleAndRemovesMember(): void
    {
        $this->withoutExceptionHandling();

        $pm = $this->makeUser();
        $dev = User::factory()->create(['email' => 'luca.verdi@example.com']);

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
        $project->members()->attach($dev->id, ['role' => 'developer']);

        // Modifica ruolo da developer a viewer
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('projects.members.update', [$project, $dev]), [
                'role' => 'viewer',
            ]);
        $updateResponse->assertOk();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $dev->id,
            'role' => 'viewer',
        ]);

        // Rimozione del membro
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('projects.members.destroy', [$project, $dev]));
        $deleteResponse->assertOk();

        // Il membro deve risultare soft-deleted (deleted_at non null) oppure
        // non presente nei membri attivi
        $this->assertSame(
            0,
            $project->members()->where('users.id', $dev->id)->count()
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-017  Il Developer può operare sui task ma non gestire colonne/membri
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeveloperCannotManageColumns(): void
    {
        $pm = $this->makeUser();
        $dev = $this->makeUser();

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
        $project->members()->attach($dev->id, ['role' => 'developer']);

        // Il developer NON può aggiungere una colonna (solo pm)
        $response = $this->actingAs($dev)
            ->postJson(route('projects.columns.store', $project), [
                'column_type_id' => 1,
            ]);

        $response->assertStatus(403);
    }

    public function testDeveloperCannotManageMembers(): void
    {
        $pm = $this->makeUser();
        $dev = $this->makeUser();
        $newUser = $this->makeUser();

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
        $project->members()->attach($dev->id, ['role' => 'developer']);

        // Il developer NON può invitare un membro
        $response = $this->actingAs($dev)
            ->postJson(route('projects.members.store', $project), [
                'user_id' => $newUser->id,
                'role' => 'viewer',
            ]);

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-018  Il Viewer ha accesso in sola lettura (scrittura → 403)
    // ─────────────────────────────────────────────────────────────────────────
    public function testViewerTaskWriteReturns403(): void
    {
        $pm = $this->makeUser();
        $viewer = $this->makeUser();

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
        $project->members()->attach($viewer->id, ['role' => 'viewer']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name' => 'Da fare',
            'position' => 0,
            'is_done' => false,
        ]);

        // Tentativo di creazione task come Viewer
        $response = $this->actingAs($viewer)
            ->postJson(route('projects.tasks.store', $project), [
                'title' => 'Task illecita',
                'priority' => 'medium',
                'column_id' => $column->id,
                'assignee_ids' => [$viewer->id],
            ]);

        $response->assertStatus(403);
    }
}
