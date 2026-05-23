<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * TC-001 – TC-006  Autenticazione (REQ-001 / REQ-NF-005)
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // TC-001  Registrazione con dati validi
    // ─────────────────────────────────────────────────────────────────────────
    public function testValidDataRegistration(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
            'email'                 => 'mario.rossi@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        // Reindirizzamento alla dashboard dopo la registrazione
        $response->assertRedirect(route('projects.index'));

        // L'utente esiste nel DB
        $this->assertDatabaseHas('users', [
            'email'    => 'mario.rossi@example.com',
            'sys_role' => 'user',
        ]);

        // La password è salvata come hash bcrypt (prefisso $2y$)
        $user = User::where('email', 'mario.rossi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertStringStartsWith('$2y$', $user->password);
        $this->assertTrue(Hash::check('Password1', $user->password));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-002  Registrazione con email già esistente
    // ─────────────────────────────────────────────────────────────────────────
    public function testRegistrationWithDuplicateEmail(): void
    {
        // Utente preesistente
        User::factory()->create(['email' => 'mario.rossi@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'Mario Bianchi',
            'email'                 => 'mario.rossi@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        // Errore di validazione sul campo email
        $response->assertSessionHasErrors('email');

        // Nessun secondo utente creato con la stessa email
        $this->assertSame(1, User::where('email', 'mario.rossi@example.com')->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-003  Registrazione con password non conforme
    // ─────────────────────────────────────────────────────────────────────────

    /** Caso A – password troppo corta */
    public function testShortPassword(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
            'email'                 => 'tc003a@example.com',
            'password'              => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'tc003a@example.com']);
    }

    /** Caso B – nessuna maiuscola né numero */
    public function testPasswordWithoutUpperOrNumber(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
            'email'                 => 'tc003b@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'tc003b@example.com']);
    }

    /** Caso C – conferma password non corrisponde */
    public function testPasswordConfirmationMismatch(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
            'email'                 => 'tc003c@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password2',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'tc003c@example.com']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-004  Login con credenziali corrette e logout
    // ─────────────────────────────────────────────────────────────────────────
    public function testLoginAndLogout(): void
    {
        $user = User::factory()->create([
            'email'              => 'mario.rossi@example.com',
            'password'           => Hash::make('Password1'),
            'email_verified_at'  => now(),
        ]);

        // Login
        $loginResponse = $this->post('/login', [
            'email'    => 'mario.rossi@example.com',
            'password' => 'Password1',
        ]);
        $loginResponse->assertRedirect(route('projects.index'));
        $this->assertAuthenticatedAs($user);

        // Accesso a rotta protetta
        $this->actingAs($user)->get(route('projects.index'))->assertOk();

        // Logout
        $this->actingAs($user)->post('/logout');

        // Dopo logout la rotta protetta reindirizza al login
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-005  Login con credenziali errate
    // ─────────────────────────────────────────────────────────────────────────
    public function testLoginWithWrongCredentials(): void
    {
        User::factory()->create([
            'email'    => 'mario.rossi@example.com',
            'password' => Hash::make('Password1'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'mario.rossi@example.com',
            'password' => 'Sbagliata9',
        ]);

        // Messaggio di errore generico (non distingue email / password)
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC-006  Accesso a rotta protetta senza autenticazione (middleware auth)
    // ─────────────────────────────────────────────────────────────────────────
    public function testSafeRouteWithoutAuthentication(): void
    {
        // Visitatore non autenticato → reindirizzamento al login
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }
}
