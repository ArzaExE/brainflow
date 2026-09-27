<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColumnType extends Model
{
    protected $fillable = ['name', 'is_done', 'position'];

    protected function casts(): array
    {
        return ['is_done' => 'boolean'];
    }

    public function columns()
    {
        return $this->hasMany(ProjectColumn::class);
    }
}
