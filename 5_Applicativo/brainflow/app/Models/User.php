<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\InviteMember;
use App\Notifications\TaskAssignment;
use App\Notifications\TaskDueSoon;
use App\Notifications\TaskMove;
use App\Notifications\UserRegistrationFromAdmin;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'sys_role',  // 'user' | 'admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // casts() converte automaticamente gli attributi del database in tipi specifici quando si leggono/scrivono
    // viene fatto solamente se c'è una conversione da fare
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function projects()
    {
        // belongsToMany è una relazione molti-a-molti
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    public function activityLogs()
    {
        // hasMany è una relazione uno-a-molti
        return $this->hasMany(ActivityLog::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->sys_role === 'admin';
    }

    // Estrae le prime lettere di nome e cognome, p.es Matvej Rossi --> MR
    public function initials(): string
    {
        return collect(explode(' ', $this->name)) // Spezza la stringa restituendo un array
            // map: applica una funzione a ogni elemento della collection e restituisce una nuova collection
            // mb_substr prende solo il primo carattere, viene utilizzato mb_substr (invece di substr) per gestire i caratteri Unicode
            ->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode(''); // Unisce i due elementi dell'array (p.es ["M", "R"]) in una stringa MR
    }

    // ── Notifiche custom ───────────────────────────────────────────────────────


    // Sovrascrive il metodo predefinito di Laravel per la notifica della mail.
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }

    // Sovrascrive il metodo predefinito di Laravel per il reset della password.
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function sendInviteNotification(Project $project, User $inviter): void
    {
        $this->notify(new InviteMember($project, $inviter));
    }

    public function sendTaskAssignmentNotification(Task $task, User $inviter): void
    {
        $this->notify(new TaskAssignment($task, $inviter));
    }

    public function sendTaskMoveNotification(Task $task, User $mover, string $fromColumn,string $toColumn): void
    {
        $this->notify(new TaskMove($task, $mover, $fromColumn, $toColumn));
    }

    public function sendTaskDueSoonNotification(Task $task): void
    {
        $this->notify(new TaskDueSoon($task));
    }

    public function sendUserRegistrationNotification(User $registeredBy, string $temporaryPassword): void
    {
        $this->notify(new UserRegistrationFromAdmin($registeredBy, $temporaryPassword));
    }
}
