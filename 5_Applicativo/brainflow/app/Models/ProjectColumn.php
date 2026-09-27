<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectColumn extends Model
{
    protected $table = 'project_columns';

    protected $fillable = [
        'project_id',
        'column_type_id',
        'name',
        'position',
        'is_done',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'column_id');
    }

    public function columnType()
    {
        return $this->belongsTo(ColumnType::class, 'column_type_id');
    }

}
