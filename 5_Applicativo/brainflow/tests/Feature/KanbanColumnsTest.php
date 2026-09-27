<?php

namespace Tests\Feature;

use App\Models\ColumnType;
use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-019 – TC-021  Gestione colonne kanban (REQ-006)
 */
class KanbanColumnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // ColumnType necessari sia per le colonne di default (TC-019) sia per
        // aggiungere/eliminare colonne (TC-020 / TC-021)
        $columnTypes = [
            ['name' => 'Backlog',      'is_done' => false, 'position' => 1],
            ['name' => 'Da fare',      'is_done' => false, 'position' => 2],
            ['name' => 'In corso',     'is_done' => false, 'position' => 3],
            ['name' => 'In revisione', 'is_done' => false, 'position' => 4],
            ['name' => 'Testing',      'is_done' => false, 'position' => 5],
            ['name' => 'Completato',   'is_done' => true,  'position' => 6],
            ['name' => 'In attesa',    'is_done' => false, 'position' => 7],
        ];

        foreach ($columnTypes as $ct) {
            ColumnType::create($ct);
        }
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'sys_role'          => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-019  Selezione tipologie colonne di default
    //
    // Verifica che all'apertura del modale di creazione colonna siano presenti
    // le 6 tipologie di default nell'ordine corretto (per campo position).
    // ─────────────────────────────────────────────────────────────────────────
    public function testDefaultColumnTypesAvailableForSelection(): void
    {
        // Prerequisito: admin crea il progetto (ProjectController::store lo
        // attacca automaticamente come PM del progetto)
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Beta',
            'priority' => 'medium',
        ]);

        $project = Project::where('name', 'Progetto Beta')->first();
        $this->assertNotNull($project);

        // Apertura del modale "crea colonna": l'endpoint restituisce i tipi disponibili
        $response = $this->actingAs($admin)
            ->getJson(route('projects.columns.available-types', $project));

        $response->assertOk();

        $defaultNames = ['Backlog', 'Da fare', 'In corso', 'In revisione', 'Testing', 'Completato'];

        $returnedNames = collect($response->json())->pluck('name')->toArray();

        // Tutti e 6 i tipi di default devono essere presenti
        foreach ($defaultNames as $name) {
            $this->assertContains($name, $returnedNames,
                "La tipologia di default '{$name}' non è presente nell'elenco.");
        }

        // I 6 tipi di default devono comparire nell'ordine corretto (per position)
        $orderedDefaults = collect($response->json())
            ->filter(fn ($t) => in_array($t['name'], $defaultNames))
            ->pluck('name')
            ->values()
            ->toArray();

        $this->assertSame($defaultNames, $orderedDefaults,
            'Le tipologie di default non sono restituite nell\'ordine corretto.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-020  Il PM aggiunge, riordina ed elimina una colonna
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmAddsReordersAndDeletesColumn(): void
    {
        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Beta', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $dafareType = ColumnType::where('name', 'Da fare')->first();
        $col1 = $project->columns()->create([
            'column_type_id' => $dafareType->id,
            'name'           => 'Da fare',
            'position'       => 1,
            'is_done'        => false,
        ]);

        $inAttesaType = ColumnType::where('name', 'In attesa')->first();

        // 1. Aggiunta colonna "In attesa"
        $addResponse = $this->actingAs($pm)
            ->postJson(route('projects.columns.store', $project), [
                'column_type_id' => (string) $inAttesaType->id,
            ]);

        $addResponse->assertOk();
        $newColumn = $project->columns()->where('name', 'In attesa')->first();
        $this->assertNotNull($newColumn, 'La colonna "In attesa" non è stata creata.');

        // 2. Riordino: "Da fare" → posizione 0, "In attesa" → posizione 1
        $reorderResponse = $this->actingAs($pm)
            ->patchJson(route('projects.columns.reorder', $project), [
                'columns' => [$col1->id, $newColumn->id],
            ]);

        $reorderResponse->assertOk();
        $this->assertDatabaseHas('project_columns', ['id' => $col1->id,      'position' => 0]);
        $this->assertDatabaseHas('project_columns', ['id' => $newColumn->id, 'position' => 1]);

        // 3. Eliminazione colonna "In attesa" (vuota)
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('projects.columns.destroy', [$project, $newColumn]));

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('project_columns', ['id' => $newColumn->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-021  Eliminazione colonna con task: conferma ed eliminazione in cascata
    //
    // Verifica che l'eliminazione di una colonna contenente task riesca
    // (il PM ha confermato l'operazione) e che i task presenti nella colonna
    // vengano eliminati in cascata insieme ad essa.
    //
    // NOTA: La richiesta di conferma tramite modale/dialog è una funzionalità
    //       dell'interfaccia utente e richiede verifica visiva manuale nel browser.
    //       Il test verifica il comportamento lato server: DELETE va a buon fine
    //       e la colonna con i suoi task è rimossa dal database.
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeleteColumnWithTasksAlsoDeletesTasks(): void
    {
        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Beta', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $inCorsoType = ColumnType::where('name', 'In corso')->first();

        $column = $project->columns()->create([
            'column_type_id' => $inCorsoType->id,
            'name'           => 'In corso',
            'position'       => 1,
            'is_done'        => false,
        ]);

        // Due task nella colonna da eliminare
        $task1 = $project->tasks()->create([
            'title'     => 'Task A',
            'priority'  => 'medium',
            'column_id' => $column->id,
            'position'  => 0,
        ]);
        $task2 = $project->tasks()->create([
            'title'     => 'Task B',
            'priority'  => 'low',
            'column_id' => $column->id,
            'position'  => 1,
        ]);

        // Il PM elimina la colonna (dopo aver confermato il dialog nel browser)
        $response = $this->actingAs($pm)
            ->deleteJson(route('projects.columns.destroy', [$project, $column]));

        $response->assertOk();

        // La colonna deve essere rimossa dal database
        $this->assertDatabaseMissing('project_columns', ['id' => $column->id]);

        // I task appartenenti alla colonna devono essere eliminati in cascata
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    }
}
