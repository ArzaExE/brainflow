<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'column_id',
        'title',
        'description',
        'priority',    // 'low' | 'medium' | 'high'
        'due_date',
        'position'
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function column()
    {
        return $this->belongsTo(ProjectColumn::class, 'column_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'label_task');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class)->orderBy('created_at');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! $this->column?->is_done;
    }

    public function subtasksProgress(): array
    {
        $total = $this->subtasks()->count();
        $done  = $this->subtasks()->where('done', true)->count();
        return compact('total', 'done');
    }
}
