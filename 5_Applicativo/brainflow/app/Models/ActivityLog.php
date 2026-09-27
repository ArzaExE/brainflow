<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'type',  // 'task.created' | 'task.moved' | 'task.assigned' | 'comment.added'
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function project()
    {
        // belongsTo: relazione inversa
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
