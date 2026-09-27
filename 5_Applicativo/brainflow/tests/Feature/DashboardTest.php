<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-035 – TC-036  Dashboard riepilogativa del progetto (REQ-014 / REQ-NF-004)
 */
class DashboardTest extends TestCase
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
    // TC-035  Dashboard con statistiche corrette
    // ─────────────────────────────────────────────────────────────────────────
    public function testDashboardShowsCorrectStatistics(): void
    {
        $pm      = $this->makeUser();
        $dev     = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id,  ['role' => 'pm']);
        $project->members()->attach($dev->id, ['role' => 'developer']);

        $doneCol = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Completato',
            'position'   => 3,
            'is_done'    => true,
        ]);
        $activeCol = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        // 2 task in colonna "Completato" → assegnati al PM
        $t1 = $project->tasks()->create(['title' => 'Task 1', 'priority' => 'high',   'column_id' => $doneCol->id,   'position' => 0]);
        $t2 = $project->tasks()->create(['title' => 'Task 2', 'priority' => 'medium', 'column_id' => $doneCol->id,   'position' => 1]);
        $t1->assignees()->attach($pm->id);
        $t2->assignees()->attach($pm->id);

        // 1 task attivo → assegnato al dev
        $t3 = $project->tasks()->create(['title' => 'Task 3', 'priority' => 'low', 'column_id' => $activeCol->id, 'position' => 0]);
        $t3->assignees()->attach($dev->id);

        // 1 task scaduto (due_date ieri, colonna non-done) → assegnato al dev
        $t4 = $project->tasks()->create([
            'title'     => 'Task scaduto',
            'priority'  => 'high',
            'column_id' => $activeCol->id,
            'position'  => 1,
            'due_date'  => Carbon::yesterday()->toDateString(),
        ]);
        $t4->assignees()->attach($dev->id);

        // Percentuale completamento: 2 completati su 4 totali = 50 %
        $this->assertSame(50, $project->completionPercent());

        // La pagina del progetto (dashboard) è raggiungibile dal PM
        $response = $this->actingAs($pm)
            ->get(route('projects.show', $project));

        $response->assertOk();
        $response->assertViewHas('project');

        // Task scaduti: 1 (in colonna non-done con due_date passata)
        $overdueTasks = $project->tasks()
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereHas('column', fn ($q) => $q->where('is_done', false))
            ->get();

        $this->assertSame(1, $overdueTasks->count());
        $this->assertSame('Task scaduto', $overdueTasks->first()->title);

        // Task per membro
        $pmTaskCount  = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $pm->id))->count();
        $devTaskCount = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $dev->id))->count();

        $this->assertSame(2, $pmTaskCount);
        $this->assertSame(2, $devTaskCount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-036  Dashboard con progetto senza task (caso limite – nessuna divisione per 0)
    // ─────────────────────────────────────────────────────────────────────────
    public function testDashboardWithEmptyProjectShowsZeroWithoutError(): void
    {
        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto Vuoto', 'priority' => 'low']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        // Nessuna colonna, nessun task: completionPercent() deve restituire 0
        $this->assertSame(0, $project->completionPercent());

        // La dashboard si carica correttamente senza errori 500
        $response = $this->actingAs($pm)
            ->get(route('projects.show', $project));

        $response->assertOk();
    }
}
