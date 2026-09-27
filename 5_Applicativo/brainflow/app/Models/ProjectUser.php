<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectUser extends Pivot
{
    use SoftDeletes;

    protected $table = 'project_user';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
    ];
}
