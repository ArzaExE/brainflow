<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'priority',      // 'low' | 'medium' | 'high'
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->using(ProjectUser::class)
            ->withPivot('role', 'deleted_at')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    // Alias per risolvere scopeBinding delle rotte che cerca in automatico users() al posto di members
    public function users()
    {
        return $this->members();
    }

    public function columns()
    {
        return $this->hasMany(ProjectColumn::class)->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────
    // Scopes: query riutilizzabile
    public function scopeDone($query)
    {
        return $query->columns()->where('is_done', true);
    }
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    // ── Computed ───────────────────────────────────────────────────────────────

    public function completionPercent(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $doneColumn = $this->columns()->where('is_done', true)->first();
        if (!$doneColumn) return 0;

        $done = $this->tasks()->where('column_id', $doneColumn->id)->count();
        return (int) round($done / $total * 100);
    }
}
