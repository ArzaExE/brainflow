<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project)
    {
        $validated = $request->validated();

        $task = $project->tasks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'column_id'   => $validated['column_id'],
            'due_date'    => $validated['due_date'] ?? null,
            'position'    => ($project->tasks()
                    ->where('column_id', $validated['column_id'])
                    ->max('position') ?? -1) + 1,
        ]);

        // sync allinea la relazione all'elenco esatto di id (aggiunge/rimuove/mantiene)
        $task->assignees()->sync($validated['assignee_ids']);
        $task->labels()->sync($validated['label_ids'] ?? []); // se l'utente non seleziona etichette passa array vuoto

        if (!empty($validated['subtasks'])) {
            foreach ($validated['subtasks'] as $sub) {
                $task->subtasks()->create([
                    'title' => $sub['title'],
                    'done'  => $sub['done'] ?? false,
                ]);
            }
        }

        // Utenti assegnati alla task
        $assigneeUsers = User::whereIn('id', $validated['assignee_ids'])->get();
        $assigneeNames = $assigneeUsers->pluck('name')->implode(', '); // Vengono estratti i nomi in formato stringa per fornirli al log

        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'type'    => 'task.created',
            'meta'    => [
                'task_title'  => $task->title,
                'assigned_to' => $assigneeNames,
            ],
        ]);

        $emailSent = true;
        foreach ($assigneeUsers as $user) {
            try {
                $user->sendTaskAssignmentNotification($task, $request->user());
            } catch (\Throwable $e) {
                $emailSent = false;
                report($e);
            }
        }

        return response()->json([
            'task'       => $task->load(['assignees', 'labels', 'subtasks']),
            'activity'   => $activity->load('user'),
            'email_sent' => $emailSent,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $validated = $request->validated();

        $oldTitle = null;
        // Se cambia il nome salva quello vecchio per il log
        if ($validated['title'] !== $task->title) {
            $oldTitle = $task->title;
        }

        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'due_date'    => $validated['due_date'] ?? null,
        ]);

        $names = null;
        $emailSent = true;

        if (!empty($validated['assignee_ids'])) {
            // Estrae tutti gli ID degli utenti assegnati alla task
            $previousIds = $task->assignees()->pluck('users.id')->all();
            // Imposta i nuovi assegnatari alla task
            $task->assignees()->sync($validated['assignee_ids']);
            // Salva i cambiamenti che ci sono stati
            $addedIds = array_diff($validated['assignee_ids'], $previousIds);
            // Se ci sono stati cambiamenti manda per ogni nuovo utente assegnato alla task una mail
            if (!empty($addedIds)) {
                $names = User::whereIn('id', $addedIds)->pluck('name')->implode(', ');
                $newAssigned = User::whereIn('id', $addedIds)->get();
                foreach ($newAssigned as $new) {
                    try {
                        $new->sendTaskAssignmentNotification($task, $request->user());
                    } catch (\Throwable $e) {
                        $emailSent = false;
                        report($e);
                    }
                }
            }
        }

        // Sincronizza le etichette se ci sono stati cambiamenti
        if (!empty($validated['label_ids'])) {
            $task->labels()->sync($validated['label_ids']);
        }

        // Sincronizza le subtasks se ci sono stati cambiamenti
        if (!empty($validated['subtasks'])) {
            $this->syncSubtasks($task, $validated['subtasks']);
        }

        // Genera messaggio per il log
        $meta = ['task_title' => $task->title];
        $type = 'task.updated';

        if ($names) {
            $meta['assigned_to'] = $names;
            $type = 'task.assigned';
        }
        if ($oldTitle) {
            $meta['old_title'] = $oldTitle;
        }

        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'type'    => $type,
            'meta'      => $meta
        ]);

        return response()->json([
            'task'      => $task->load(['assignees', 'labels', 'subtasks']),
            'activity'  => $activity->load('user'),
            'email_sent' => $emailSent,
        ]);
    }

    public function move(Request $request, Project $project,Task $task)
    {
        $validated = $request->validate([
            'column_id' => ['required', 'integer',
                // Controllo sullo spostamento della task solamente al'interno del priopio progetto
                Rule::exists('project_columns', 'id')->where('project_id', $project->id),
            ],
        ]);

        $fromColumn = $task->column->name; // Salva vecchia colonna per log

        $task->update(['column_id' => $validated['column_id']]);

        $task->load('column'); // Carica la relazione column nella task
        $toColumn = $task->column->name; // Nuova colonna

        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'type'    => 'task.moved',
            'meta'    => [
                'task_title' => $task->title,
                'from_column' => $fromColumn,
                'to_column'   => $toColumn,
            ],
        ]);

        $assignees = $task->assignees()->get(); // Estrae gli assegnari della task
        $emailSent = true;

        foreach ($assignees as $user) {
            try {
                $user->sendTaskMoveNotification($task, $request->user(), $fromColumn, $toColumn);
            } catch (\Throwable $e) {
                $emailSent = false;
                report($e);
            }
        }

        return response()->json([
            'task'     => $task->load(['assignees', 'labels', 'subtasks']),
            'activity' => $activity->load('user'),
            'email_sent' => $emailSent,
        ]);
    }

    public function destroy(Project $project, Task $task)
    {
        $title = $task->title; // Salva titolo della task per log

        $task->delete();

        $activity = $project->activityLogs()->create([
            'task_id' => null,  // la task non esiste più
            'user_id' => auth()->id(),
            'type'    => 'task.deleted',
            'meta'    => ['task_title' => $title],
        ]);

        return response()->json([
            'activity' => $activity->load('user'),
        ]);
    }

    // Metodo che sincronizza le subtask (non si può utilizzare il metodo sync per via della relazione delle subtask che è one-to-may)
    private function syncSubtasks(Task $task, array $subtasks): void
    {
        $task->subtasks()->delete(); // Cancella tutte le subtask associate alla task

        // Ricrea da zero tutte le subtask aggiornate
        foreach ($subtasks as $sub) {
            $task->subtasks()->create([
                'title' => $sub['title'],
                'done'  => $sub['done'] ?? false,
            ]);
        }
    }
}
