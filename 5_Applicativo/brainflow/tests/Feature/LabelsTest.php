<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-026 – TC-027  Gestione etichette (REQ-009 / REQ-NF-001)
 */
class LabelsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
    }

    /** Helper: progetto con etichette di sistema già create */
    private function makeProjectWithSystemLabels(User $pm): Project
    {
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        // Stesso set di etichette usato da ProjectController::systemLabels()
        $project->labels()->createMany([
            ['name' => 'DIAGRAMMA',      'color' => '#6366f1', 'is_system' => true],
            ['name' => 'ANALISI',        'color' => '#8b5cf6', 'is_system' => true],
            ['name' => 'BACKEND',        'color' => '#3b82f6', 'is_system' => true],
            ['name' => 'FRONTEND',       'color' => '#ec4899', 'is_system' => true],
            ['name' => 'DOCUMENTAZIONE', 'color' => '#f97316', 'is_system' => true],
            ['name' => 'TEST',           'color' => '#22c55e', 'is_system' => true],
        ]);

        return $project;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-026  Etichette di sistema presenti e non modificabili/eliminabili
    // ─────────────────────────────────────────────────────────────────────────
    public function testSystemLabelsArePresentAndProtected(): void
    {
        $pm      = $this->makeUser();
        $project = $this->makeProjectWithSystemLabels($pm);

        // Tutte le etichette di default devono essere presenti
        $expected = ['DIAGRAMMA', 'ANALISI', 'BACKEND', 'FRONTEND', 'DOCUMENTAZIONE', 'TEST'];
        $names    = $project->labels()->where('is_system', true)->pluck('name')->toArray();

        foreach ($expected as $name) {
            $this->assertContains($name, $names, "Etichetta di sistema '{$name}' mancante.");
        }

        $backend = $project->labels()->where('name', 'BACKEND')->first();

        // Tentativo di eliminazione di un'etichetta di sistema → 422
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('projects.labels.destroy', [$project, $backend]));

        $deleteResponse->assertStatus(422);
        $this->assertDatabaseHas('labels', ['id' => $backend->id]);

        // Tentativo di modifica di un'etichetta di sistema → 422
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('projects.labels.update', [$project, $backend]), [
                'color' => '#000000',
            ]);

        $updateResponse->assertStatus(422);

        // Il colore originale deve essere rimasto invariato
        $this->assertDatabaseHas('labels', [
            'id'    => $backend->id,
            'color' => '#3b82f6',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-027  Creazione etichetta personalizzata con validazione colore hex
    // ─────────────────────────────────────────────────────────────────────────
    public function testCustomLabelCreationWithColorValidation(): void
    {
        $pm      = $this->makeUser();
        $project = $this->makeProjectWithSystemLabels($pm);

        // Caso A – colore esadecimale valido
        $validResponse = $this->actingAs($pm)
            ->postJson(route('projects.labels.store', $project), [
                'name'  => 'Urgente',
                'color' => '#FF0000',
            ]);

        $validResponse->assertOk();
        $this->assertDatabaseHas('labels', [
            'project_id' => $project->id,
            'name'       => 'URGENTE',   // il controller salva in uppercase
            'color'      => '#FF0000',
            'is_system'  => false,
        ]);

        // Caso B – colore non esadecimale → 422
        $invalidResponse = $this->actingAs($pm)
            ->postJson(route('projects.labels.store', $project), [
                'name'  => 'Colore invalido',
                'color' => 'rosso',
            ]);

        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors('color');
        $this->assertDatabaseMissing('labels', ['name' => 'COLORE INVALIDO']);
    }
}
