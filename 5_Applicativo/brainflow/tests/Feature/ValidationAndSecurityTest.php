<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC-038 – TC-042  Validazione, errori, feedback UI e sicurezza
 *                  (REQ-NF-001 / REQ-NF-002 / REQ-NF-003 / REQ-NF-005)
 */
class ValidationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

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
    // TC-038  Validazione form: errori evidenziati, old input ripopolato,
    //         nessun salvataggio parziale
    // ─────────────────────────────────────────────────────────────────────────
    public function testFormValidationErrorShowsOldInputAndNoPartialSave(): void
    {
        $admin = $this->makeAdmin();

        // POST senza il campo obbligatorio 'name'
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'description' => 'Descrizione prova',
            'priority'    => 'high',
            // 'name' mancante
        ]);

        // La validazione lato server rifiuta la richiesta
        $response->assertSessionHasErrors('name');

        // I valori già inseriti sono conservati nella sessione (old input)
        $response->assertSessionHasInput('description');
        $response->assertSessionHasInput('priority');

        // Nessun progetto parziale salvato nel DB
        $this->assertDatabaseCount('projects', 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-039  Pagine di errore personalizzate HTTP 404 e 403
    //
    // NOTA: L'aspetto grafico (branding, testi custom) richiede verifica visiva.
    //       Il test verifica solo i codici di stato HTTP corretti.
    // ─────────────────────────────────────────────────────────────────────────
    public function testCustom404PageReturned(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get('/rotta-inesistente-per-test-404');

        $response->assertStatus(404);
    }

    public function testCustom403PageReturnedForUnauthorizedRoute(): void
    {
        $user = $this->makeUser();

        // Utente con ruolo 'user' tenta di accedere alla sezione admin
        $response = $this->actingAs($user)->get(route('admin.users'));

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-040  Feedback visivo: flash message di successo dopo le azioni
    //
    // NOTA: Toast e modal UI richiedono verifica visiva nel browser.
    //       Il test verifica che la sessione contenga i messaggi flash attesi.
    // ─────────────────────────────────────────────────────────────────────────
    public function testSuccessFlashMessageAfterProjectCreation(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Flash',
            'priority' => 'medium',
        ]);

        $response->assertSessionHas('success');
    }

    public function testSuccessFlashMessageAfterProjectDeletion(): void
    {
        $admin   = $this->makeAdmin();
        $project = Project::create(['name' => 'Progetto da eliminare', 'priority' => 'low']);
        $project->members()->attach($admin->id, ['role' => 'pm']);

        $response = $this->actingAs($admin)
            ->delete(route('projects.destroy', $project));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-041  Protezione CSRF sui form
    //
    // NOTA: VerifyCsrfToken bypassa la verifica in ambiente PHPUnit
    //       (runningUnitTests() → true). La protezione a runtime è garantita
    //       dalla presenza del middleware nel gruppo 'web'; il comportamento
    //       HTTP 419 a token mancante/errato si verifica solo via browser.
    //       Il test verifica che il middleware CSRF sia registrato nella route.
    // ─────────────────────────────────────────────────────────────────────────
    public function testCsrfMiddlewareIsRegisteredForWebRoutes(): void
    {
        $projectStoreRoute = app('router')->getRoutes()->getByName('projects.store');
        $this->assertNotNull($projectStoreRoute, 'La route projects.store non esiste.');

        // Il gruppo 'web' (che contiene VerifyCsrfToken) deve essere nella middleware list
        $routeMiddleware = $projectStoreRoute->gatherMiddleware();
        $this->assertContains('web', $routeMiddleware,
            'Il middleware "web" (e quindi CSRF) non è applicato alla route projects.store.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-042  Protezione XSS: Blade esegue l'escape dell'output con {{ }}
    //
    // NOTA: La verifica che lo script NON venga eseguito nel browser (nessun
    //       popup alert) richiede verifica visiva. Il test verifica che il
    //       payload sia salvato come testo letterale e che la pagina si carichi
    //       senza errori (Blade non esegue il codice lato server).
    // ─────────────────────────────────────────────────────────────────────────
    public function testXssPayloadInTaskTitleIsStoredLiterally(): void
    {
        $pm      = $this->makeUser();
        $project = Project::create(['name' => 'Progetto XSS', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);

        $column = ProjectColumn::create([
            'project_id' => $project->id,
            'name'       => 'Da fare',
            'position'   => 1,
            'is_done'    => false,
        ]);

        $xssPayload = "<script>alert('XSS')</script>";

        $response = $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => $xssPayload,
                'priority'     => 'medium',
                'column_id'    => $column->id,
                'assignee_ids' => [$pm->id],
            ]);

        $response->assertStatus(201);

        // Il titolo è salvato nel DB come testo letterale, non eseguito
        $task = $project->tasks()->where('title', $xssPayload)->first();
        $this->assertNotNull($task);
        $this->assertSame($xssPayload, $task->title);

        // La pagina del progetto si carica senza errori (Blade effettua l'escape con {{ }})
        $this->actingAs($pm)
            ->get(route('projects.show', $project))
            ->assertOk();
    }
}
