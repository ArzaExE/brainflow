<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ColumnType;
use App\Models\Project;
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
            ['name' => 'In revisione',  'is_done' => false, 'position' => 4],
            ['name' => 'In attesa',     'is_done' => false, 'position' => 5],
            ['name' => 'Testing',       'is_done' => false, 'position' => 6],
            ['name' => 'Completato',    'is_done' => true,  'position' => 7],
        ];
        $types = collect($typeData)->mapWithKeys(function ($t) {
            $model = ColumnType::create($t);
            return [$t['name'] => $model];
        });

        // ── Users ─────────────────────────────────────────────────────────────
        $christian = User::create([
            'name'              => 'Christian Arzani',
            'email'             => 'christian@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'admin',
            'email_verified_at' => now(),
        ]);
        $sofia = User::create([
            'name'              => 'Sofia Russo',
            'email'             => 'sofia@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
        $luca = User::create([
            'name'              => 'Luca Bianchi',
            'email'             => 'luca@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
        $marta = User::create([
            'name'              => 'Marta Ferrari',
            'email'             => 'marta@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
        $davide = User::create([
            'name'              => 'Davide Conti',
            'email'             => 'davide@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);
        $elena = User::create([
            'name'              => 'Elena Marino',
            'email'             => 'elena@brainflow.it',
            'password'          => Hash::make('Password1'),
            'sys_role'          => 'user',
            'email_verified_at' => now(),
        ]);

        // Etichette di sistema (riutilizzate per ogni progetto)
        $systemLabels = [
            ['name' => 'DIAGRAMMA',     'color' => '#6366f1'],
            ['name' => 'ANALISI',       'color' => '#8b5cf6'],
            ['name' => 'BACKEND',       'color' => '#3b82f6'],
            ['name' => 'FRONTEND',      'color' => '#ec4899'],
            ['name' => 'DOCUMENTAZIONE','color' => '#f97316'],
            ['name' => 'TEST',          'color' => '#22c55e'],
        ];

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

        $p1Labels = collect($systemLabels)->map(fn ($l) =>
        $p1->labels()->create(array_merge($l, ['is_system' => true]))
        );

        $tasks1 = [
            ['title' => 'Definire architettura DB',  'column' => 0, 'priority' => 'high',   'assignees' => [$luca->id],              'labels' => [0, 2]],
            ['title' => 'Progettare wireframe UI',   'column' => 3, 'priority' => 'medium', 'assignees' => [$marta->id],             'labels' => [0, 3]],
            ['title' => 'Implementare auth JWT',     'column' => 1, 'priority' => 'high',   'assignees' => [$luca->id, $davide->id], 'labels' => [2]],
            ['title' => 'Board drag-and-drop',       'column' => 1, 'priority' => 'high',   'assignees' => [$marta->id],             'labels' => [3], 'due_date' => now()->addDays(3)->toDateString()],
            ['title' => 'Dashboard KPI',             'column' => 0, 'priority' => 'medium', 'assignees' => [$davide->id],            'labels' => [3, 5]],
            ['title' => 'Documentare API',           'column' => 2, 'priority' => 'low',    'assignees' => [$sofia->id],             'labels' => [4]],
            ['title' => 'Test di integrazione',      'column' => 0, 'priority' => 'medium', 'assignees' => [$davide->id],            'labels' => [5], 'due_date' => now()->subDays(2)->toDateString()],
            ['title' => 'Deploy su VPS',             'column' => 0, 'priority' => 'high',   'assignees' => [$luca->id],              'labels' => [2]],
        ];

        // posizione incrementale per colonna (l'ordinamento della board si basa su `position`)
        $positionByColumn = [];
        $taskModels = [];

        foreach ($tasks1 as $td) {
            $colId = $cols1[$td['column']]->id;
            $position = $positionByColumn[$colId] ?? 0;
            $positionByColumn[$colId] = $position + 1;

            $task = $p1->tasks()->create([
                'column_id' => $colId,
                'title'     => $td['title'],
                'priority'  => $td['priority'],
                'due_date'  => $td['due_date'] ?? null,
                'position'  => $position,
            ]);
            $task->assignees()->attach($td['assignees']);
            $task->labels()->attach(
                collect($td['labels'])->map(fn ($i) => $p1Labels[$i]->id)->toArray()
            );

            $taskModels[$td['title']] = $task;

            if ($td['title'] === 'Implementare auth JWT') {
                $task->subtasks()->createMany([
                    ['title' => 'Configurare Guard',  'done' => true],
                    ['title' => 'Endpoint /login',    'done' => true],
                    ['title' => 'Endpoint /register', 'done' => false],
                    ['title' => 'Refresh token',      'done' => false],
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
            $sofia->id => ['role' => 'pm'],
            $luca->id  => ['role' => 'developer'],
            $marta->id => ['role' => 'developer'],
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
        $p2PositionByColumn = [];
        foreach ($p2Tasks as $i => $title) {
            $colId = $cols2[$i % 4]->id;
            $position = $p2PositionByColumn[$colId] ?? 0;
            $p2PositionByColumn[$colId] = $position + 1;

            $p2->tasks()->create([
                'column_id' => $colId,
                'title'     => $title,
                'priority'  => 'medium',
                'position'  => $position,
            ])->assignees()->attach($luca->id);
        }

        // ── Project 3: Sito Vetrina (archiviato) ──────────────────────────────
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
        // Le voci referenziano i task reali e usano la stessa struttura `meta`
        // prodotta dai controller (task.created, task.moved, task.assigned).
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => $taskModels['Definire architettura DB']->id,
            'user_id'    => $christian->id,
            'type'       => 'task.created',
            'meta'       => [
                'task_title'  => 'Definire architettura DB',
                'assigned_to' => $luca->name,
            ],
            'created_at' => now()->subHours(5),
        ]);
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => $taskModels['Progettare wireframe UI']->id,
            'user_id'    => $marta->id,
            'type'       => 'task.moved',
            'meta'       => [
                'task_title'  => 'Progettare wireframe UI',
                'from_column' => 'Da fare',
                'to_column'   => 'Completato',
            ],
            'created_at' => now()->subHours(3),
        ]);
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => $taskModels['Documentare API']->id,
            'user_id'    => $sofia->id,
            'type'       => 'task.assigned',
            'meta'       => [
                'task_title'  => 'Documentare API',
                'assigned_to' => $sofia->name,
            ],
            'created_at' => now()->subHour(),
        ]);
    }
}
