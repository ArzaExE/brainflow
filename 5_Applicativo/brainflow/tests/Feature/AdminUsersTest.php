<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * TC-007 – TC-009  Gestione utenti Admin (REQ-002 / REQ-NF-005)
 */
class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // TC-007  L'Admin accede alla pagina di amministrazione utenti
    // ─────────────────────────────────────────────────────────────────────────
    public function testAdminCanAccessUsersPage(): void
    {
        $admin = User::factory()->create([
            'sys_role'          => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users'));

        $response->assertOk();
        $response->assertViewIs('admin.users');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-008  L'utente standard non può accedere all'amministrazione utenti
    // ─────────────────────────────────────────────────────────────────────────
    public function testStandardUserCannotAccessAdminPage(): void
    {
        $user = User::factory()->create([
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.users'));

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-009  L'Admin modifica il ruolo di sistema di un utente
    // ─────────────────────────────────────────────────────────────────────────
    public function testAdminCanUpdateUserRole(): void
    {
        $admin = User::factory()->create([
            'name'     => 'Luca Verdi',
            'sys_role'          => 'admin',
            'email_verified_at' => now(),
        ]);
        $target = User::factory()->create([
            'name'     => 'Mario Rossi',
            'email'    => 'luca.verdi@example.com',
            'sys_role' => 'user',
        ]);

        $response = $this->actingAs($admin)->patchJson(route('admin.users.update', $target), [
            'name'     => $target->name,
            'email'    => $target->email,
            'sys_role' => 'admin',
        ]);

        $response->assertOk();

        // Il ruolo nel DB è aggiornato ad admin
        $this->assertDatabaseHas('users', [
            'email'    => 'luca.verdi@example.com',
            'sys_role' => 'admin',
        ]);
    }
}
