<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ColumnType;
use App\Models\Label;
use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Column types (global) ─────────────────────────────────────────────
        $typeData = [
            ['name' => 'Backlog',       'is_done' => false, 'position' => 1],
            ['name' => 'Da fare',       'is_done' => false, 'position' => 2],
            ['name' => 'In corso',      'is_done' => false, 'position' => 3],
            ['name' => 'In analisi',    'is_done' => false, 'position' => 4],
            ['name' => 'In revisione',  'is_done' => false, 'position' => 5],
            ['name' => 'In attesa',     'is_done' => false, 'position' => 6],
            ['name' => 'QA / Test',     'is_done' => false, 'position' => 7],
            ['name' => 'Completato',    'is_done' => true,  'position' => 8],
            ['name' => 'Annullato',     'is_done' => false, 'position' => 9],
        ];
        $types = collect($typeData)->mapWithKeys(function ($t) {
            $model = ColumnType::create($t);
            return [$t['name'] => $model];
        });

        // ── Users ─────────────────────────────────────────────────────────────
        $christian = User::create([
            'name'     => 'Christian Arzani',
            'email'    => 'christian@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'admin',
        ]);
        $sofia = User::create([
            'name'     => 'Sofia Russo',
            'email'    => 'sofia@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'user',
        ]);
        $luca = User::create([
            'name'     => 'Luca Bianchi',
            'email'    => 'luca@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'user',
        ]);
        $marta = User::create([
            'name'     => 'Marta Ferrari',
            'email'    => 'marta@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'user',
        ]);
        $davide = User::create([
            'name'     => 'Davide Conti',
            'email'    => 'davide@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'user',
        ]);
        $elena = User::create([
            'name'     => 'Elena Marino',
            'email'    => 'elena@brainflow.it',
            'password' => Hash::make('Password1'),
            'sys_role' => 'user',
        ]);

        // ── Project 1: BrainFlow ──────────────────────────────────────────────
        $p1 = Project::create([
            'name'        => 'BrainFlow',
            'description' => 'Sviluppo dell\'applicativo kanban BrainFlow.',
            'priority'    => 'high',
        ]);

        $p1->members()->attach([
            $christian->id => ['role' => 'pm'],
            $sofia->id     => ['role' => 'pm'],
            $luca->id      => ['role' => 'developer'],
            $marta->id     => ['role' => 'developer'],
            $davide->id    => ['role' => 'developer'],
            $elena->id     => ['role' => 'viewer'],
        ]);

        $cols1 = collect(['Da fare', 'In corso', 'In revisione', 'Completato'])
            ->map(fn ($name, $i) => $p1->columns()->create([
                'column_type_id' => $types[$name]->id,
                'name'           => $name,
                'position'       => $i,
                'is_done'        => $types[$name]->is_done,
            ]));

        $systemLabels = [
            ['name' => 'DIAGRAMMA',    'color' => '#6366f1'],
            ['name' => 'ANALISI',      'color' => '#8b5cf6'],
            ['name' => 'BACKEND',      'color' => '#3b82f6'],
            ['name' => 'FRONTEND',     'color' => '#ec4899'],
            ['name' => 'DOCUMENTAZIONE','color' => '#f97316'],
            ['name' => 'TEST',         'color' => '#22c55e'],
        ];

        $p1Labels = collect($systemLabels)->map(fn ($l) =>
            $p1->labels()->create(array_merge($l, ['is_system' => true]))
        );

        $tasks1 = [
            ['title' => 'Definire architettura DB', 'column' => 0, 'priority' => 'high', 'assignees' => [$luca->id], 'labels' => [0, 2]],
            ['title' => 'Progettare wireframe UI', 'column' => 3, 'priority' => 'medium', 'assignees' => [$marta->id], 'labels' => [0, 3]],
            ['title' => 'Implementare auth JWT', 'column' => 1, 'priority' => 'high', 'assignees' => [$luca->id, $davide->id], 'labels' => [2]],
            ['title' => 'Board drag-and-drop', 'column' => 1, 'priority' => 'high', 'assignees' => [$marta->id], 'labels' => [3], 'due_date' => now()->addDays(3)->toDateString()],
            ['title' => 'Dashboard KPI', 'column' => 0, 'priority' => 'medium', 'assignees' => [$davide->id], 'labels' => [3, 5]],
            ['title' => 'Documentare API', 'column' => 2, 'priority' => 'low', 'assignees' => [$sofia->id], 'labels' => [4]],
            ['title' => 'Test di integrazione', 'column' => 0, 'priority' => 'medium', 'assignees' => [$davide->id], 'labels' => [5], 'due_date' => now()->subDays(2)->toDateString()],
            ['title' => 'Deploy su VPS', 'column' => 0, 'priority' => 'high', 'assignees' => [$luca->id], 'labels' => [2]],
        ];

        foreach ($tasks1 as $td) {
            $task = $p1->tasks()->create([
                'column_id' => $cols1[$td['column']]->id,
                'title'     => $td['title'],
                'priority'  => $td['priority'],
                'due_date'  => $td['due_date'] ?? null,
            ]);
            $task->assignees()->attach($td['assignees']);
            $task->labels()->attach(collect($td['labels'])->map(fn ($i) => $p1Labels[$i]->id)->toArray());

            if ($td['title'] === 'Implementare auth JWT') {
                $task->subtasks()->createMany([
                    ['title' => 'Configurare Guard', 'done' => true],
                    ['title' => 'Endpoint /login',   'done' => true],
                    ['title' => 'Endpoint /register','done' => false],
                    ['title' => 'Refresh token',     'done' => false],
                ]);
            }
        }

        // ── Project 2: App Mobile Cliente ─────────────────────────────────────
        $p2 = Project::create([
            'name'        => 'App Mobile Cliente',
            'description' => 'Applicazione mobile per i clienti finali.',
            'priority'    => 'medium',
        ]);
        $p2->members()->attach([
            $sofia->id  => ['role' => 'pm'],
            $luca->id   => ['role' => 'developer'],
            $marta->id  => ['role' => 'developer'],
        ]);
        $cols2 = collect(['Backlog', 'In corso', 'In revisione', 'Completato'])
            ->map(fn ($name, $i) => $p2->columns()->create([
                'column_type_id' => $types[$name]->id,
                'name'           => $name,
                'position'       => $i,
                'is_done'        => $types[$name]->is_done,
            ]));

        foreach ($systemLabels as $l) {
            $p2->labels()->create(array_merge($l, ['is_system' => true]));
        }

        $p2Tasks = ['Prototipo React Native', 'Autenticazione OAuth', 'Push notification'];
        foreach ($p2Tasks as $i => $title) {
            $p2->tasks()->create([
                'column_id' => $cols2[$i % 4]->id,
                'title'     => $title,
                'priority'  => 'medium',
            ])->assignees()->attach($luca->id);
        }

        // ── Project 3: Sito Vetrina (archived) ────────────────────────────────
        $p3 = Project::create([
            'name'        => 'Sito vetrina v1',
            'description' => 'Primo sito istituzionale, archiviato.',
            'priority'    => 'low',
            'archived_at' => now()->subMonths(2),
        ]);
        $p3->members()->attach($christian->id, ['role' => 'pm']);
        foreach ($systemLabels as $l) {
            $p3->labels()->create(array_merge($l, ['is_system' => true]));
        }

        // ── Activity log ──────────────────────────────────────────────────────
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => null,
            'user_id'    => $christian->id,
            'type'       => 'task.created',
            'meta'       => ['task_title' => 'Definire architettura DB'],
            'created_at' => now()->subHours(5),
        ]);
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => null,
            'user_id'    => $luca->id,
            'type'       => 'task.moved',
            'meta'       => ['task_title' => 'Progettare wireframe UI'],
            'created_at' => now()->subHours(3),
        ]);
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => null,
            'user_id'    => $sofia->id,
            'type'       => 'comment.added',
            'meta'       => ['task_title' => 'Documentare API', 'body' => 'Ho aggiunto la sezione autenticazione.'],
            'created_at' => now()->subHour(),
        ]);
    }
}
