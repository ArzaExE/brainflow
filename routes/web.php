<?php

use App\Http\Controllers\ProjectColumnsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\AdminController;


// TODO: Add a landing page
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('projects.index')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])
        ->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])
        ->name('projects.store')
        ->middleware('system.role:admin');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show')
        ->middleware('project.access:pm,developer,viewer');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])
        ->name('projects.update')
        ->middleware('project.access:pm');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->name('projects.destroy')
        ->middleware('project.access:pm');
    Route::patch('/projects/{project}/archive', [ProjectController::class, 'archive'])
        ->name('projects.archive')
        ->middleware('project.access:pm');
    Route::patch('/projects/{project}/unarchive', [ProjectController::class, 'unarchive'])
        ->name('projects.unarchive')
        ->middleware('project.access:pm');

    Route::prefix('projects/{project}')
        ->scopeBindings()
        ->group(function () {
            // Colonne
            Route::post('columns', [ProjectColumnsController::class, 'store'])
                ->name('projects.columns.store')
                ->middleware('project.access:pm');
            Route::get('columns/available-types', [ProjectColumnsController::class, 'availableTypes'])
                ->name('projects.columns.available-types')
                ->middleware('project.access:pm');
            Route::patch('columns/reorder', [ProjectColumnsController::class, 'reorder'])
                ->name('projects.columns.reorder')
                ->middleware('project.access:pm');
            Route::delete('columns/{column}',[ProjectColumnsController::class, 'destroy'])
                ->name('projects.columns.destroy')
                ->middleware('project.access:pm');

            // Tasks
            Route::post('tasks', [TaskController::class, 'store'])
                ->name('projects.tasks.store')
                ->middleware('project.access:pm,developer');
            Route::patch('tasks/{task}', [TaskController::class, 'update'])
                ->name('tasks.update')
                ->middleware('project.access:pm,developer');
            Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
                ->name('tasks.destroy')
                ->middleware('project.access:pm');
            Route::patch('tasks/{task}/move', [TaskController::class, 'move'])
                ->name('tasks.move')
                ->middleware('project.access:pm,developer');

            // Members
            Route::post('members', [MemberController::class, 'store'])
                ->name('projects.members.store')
                ->middleware('project.access:pm');
            Route::get('members/available-types', [MemberController::class, 'availableUsers'])
                ->name('projects.members.availableUsers')
                ->middleware('project.access:pm');
            Route::patch('members/{user}', [MemberController::class, 'update'])
                ->name('projects.members.update')
                ->middleware('project.access:pm');
            Route::delete('members/{user}', [MemberController::class, 'destroy'])
                ->name('projects.members.destroy')
                ->middleware('project.access:pm');

            // Labels di progetto
            Route::post('labels', [LabelController::class, 'store'])
                ->name('projects.labels.store')
                ->middleware('project.access:pm,developer');
            Route::patch('labels/{label}', [LabelController::class, 'update'])
                ->name('projects.labels.update')
                ->middleware('project.access:pm,developer');
            Route::delete('labels/{label}', [LabelController::class, 'destroy'])
                ->name('projects.labels.destroy')
                ->middleware('project.access:pm,developer');
        });

    // Admin
    Route::post('/admin/users', [AdminController::class, 'store'])
        ->name('admin.users.store')
        ->middleware('system.role:admin');
    Route::get('/admin/users', [AdminController::class, 'index'])
        ->name('admin.users')
        ->middleware('system.role:admin');
    Route::patch('/admin/users/{user}', [AdminController::class, 'update'])
        ->name('admin.users.update')
        ->middleware('system.role:admin');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])
        ->name('projects.users.destroy')
        ->middleware('system.role:admin');
});

require __DIR__.'/auth.php';
