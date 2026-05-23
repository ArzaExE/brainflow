<?php

namespace Tests\Feature;

use App\Models\ColumnType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-010 – TC-014  Gestione progetti (REQ-003 / REQ-NF-001 / REQ-NF-005)
 */
class ProjectCRUDAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    // Seeder minimo: crea i ColumnType necessari alla store del progetto
    protected function setUp(): void
    {
        parent::setUp();

        // I ColumnType non vengono creati dai factory; li inseriamo manualmente
        // perché ProjectController::store() li utilizza (via seeder in produzione)
        $columnTypes = [
            ['name' => 'Backlog',      'is_done' => false, 'position' => 1],
            ['name' => 'Da fare',      'is_done' => false, 'position' => 2],
            ['name' => 'In corso',     'is_done' => false, 'position' => 3],
            ['name' => 'In revisione', 'is_done' => false, 'position' => 5],
            ['name' => 'Testing',      'is_done' => false, 'position' => 7],
            ['name' => 'Completato',   'is_done' => true,  'position' => 8],
        ];
        foreach ($columnTypes as $ct) {
            ColumnType::create($ct);
        }
    }

    /** Helper: crea un utente admin verificato */
    private function makeAdmin(): User
    {
        return User::factory()->create([
            'sys_role'          => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    /** Helper: crea un utente standard verificato */
    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-010  Creazione progetto con dati validi
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectCreationWithValidData(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name'        => 'Progetto Alfa',
            'description' => 'Descrizione di prova',
            'priority'    => 'high',
        ]);

        $response->assertRedirect(route('projects.index'));

        // Progetto esiste nel DB
        $this->assertDatabaseHas('projects', [
            'name'        => 'Progetto Alfa',
            'archived_at' => null,
        ]);

        // Il creatore (admin) risulta membro con ruolo pm
        $project = Project::where('name', 'Progetto Alfa')->first();
        $this->assertNotNull($project);

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id'    => $admin->id,
            'role'       => 'pm',
        ]);

        // Etichette di sistema create (BACKEND, FRONTEND, ecc.)
        $this->assertSame(6, $project->labels()->where('is_system', true)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-011  Creazione progetto con titolo duplicato
    //         (il titolo è univoco nella tabella projects)
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectCreationWithDuplicateName(): void
    {
        $admin = $this->makeAdmin();

        // Primo progetto
        $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Alfa',
            'priority' => 'medium',
        ]);

        // Secondo tentativo con lo stesso nome
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Alfa',
            'priority' => 'low',
        ]);

        // La validazione (unique su `name`) deve bloccare la creazione
        $response->assertSessionHasErrors('name');

        // Nel DB esiste un solo progetto con quel nome
        $this->assertSame(1, Project::where('name', 'Progetto Alfa')->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-012  Archiviazione progetto (soft delete / cambio archived_at)
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectArchiving(): void
    {
        $admin = $this->makeAdmin();
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($admin->id, ['role' => 'pm']);

        $response = $this->actingAs($admin)
            ->patch(route('projects.archive', $project));

        $response->assertRedirect(route('projects.show', $project));

        // Lo stato nel DB è "archiviato" (archived_at non null)
        $this->assertNotNull($project->fresh()->archived_at);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-013  Modifica di un progetto archiviato non consentita lato server
    // ─────────────────────────────────────────────────────────────────────────
    public function testArchivedProjectUpdateIsBlocked(): void
    {
        $admin = $this->makeAdmin();
        $project = Project::create([
            'name'        => 'Progetto Alfa',
            'priority'    => 'medium',
            'archived_at' => now(),
        ]);
        $project->members()->attach($admin->id, ['role' => 'pm']);

        // Il middleware project.access:pm permette l'accesso fisico alla rotta;
        // ma il controller deve respingere la modifica su un progetto archiviato.
        // Verifichiamo che i dati NON vengano alterati nel DB.
        $this->actingAs($admin)->patch(route('projects.update', $project), [
            'name'     => 'Titolo Modificato',
            'priority' => 'low',
        ]);

        // Il nome originale è rimasto invariato
        $this->assertDatabaseHas('projects', ['name' => 'Progetto Alfa']);
        $this->assertDatabaseMissing('projects', ['name' => 'Titolo Modificato']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-014  Un Viewer non può eliminare o modificare un progetto (403)
    // ─────────────────────────────────────────────────────────────────────────
    public function testViewerCannotDeleteProject(): void
    {
        $pm     = $this->makeUser();
        $viewer = $this->makeUser();

        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,     ['role' => 'pm']);
        $project->members()->attach($viewer->id, ['role' => 'viewer']);

        // Tentativo di eliminazione da parte del Viewer
        $response = $this->actingAs($viewer)
            ->delete(route('projects.destroy', $project));

        $response->assertStatus(403);

        // Il progetto esiste ancora
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
