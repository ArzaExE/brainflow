# Registro dei Commenti Personalizzati

Questo documento contiene esclusivamente i commenti scritti dall'utente nei file sorgenti, escludendo il codice boilerplate e i commenti predefiniti di Laravel.

## Indice dei File con Commenti Personali
---

## app/Console/Commands/SendDueSoonNotifications.php

Percorso file: [ app/Console/Commands/SendDueSoonNotifications.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php)

### [Riga 35 - SendDueSoonNotifications.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php#L35)
**Commento:**
```text
// estrae solo i task con la data di scadenza
```
**Codice di riferimento:**
```php
            ->whereDate('due_date', $tomorrow)  // controlla data
            // Escludo i task che si trovano nella colonna "completati" (is_done)
            ->whereHas('column', function ($query) {
                $query->where('is_done', false);
```

### [Riga 37 - SendDueSoonNotifications.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php#L37)
**Commento:**
```text
// Escludo i task che si trovano nella colonna "completati" (is_done)
```
**Codice di riferimento:**
```php
            ->whereHas('column', function ($query) {
                $query->where('is_done', false);
            })
            // Escludo i task dei progetti archiviati (archived_at NULL = attivo)
```

### [Riga 41 - SendDueSoonNotifications.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php#L41)
**Commento:**
```text
// Escludo i task dei progetti archiviati (archived_at NULL = attivo)
```
**Codice di riferimento:**
```php
            ->whereHas('project', function ($query) {
                $query->whereNull('archived_at');
            })
            ->with(['assignees', 'project', 'column'])
```


---

## app/Http/Controllers/AdminController.php

Percorso file: [ app/Http/Controllers/AdminController.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php)

### [Riga 39 - AdminController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php#L39)
**Commento:**
```text
/*
         *  Controllo sulla mail try/catch se la mail non va a buon fine.
         * Il controllo è necessario perchè le mail vengono mandate in modo sincrono per far funzionare lo scheduler,
         * con un approccio asincrono questo controllo non è necessario
         * */
```
**Codice di riferimento:**
```php
        $emailSent = true;
        try {
            $user->sendUserRegistrationNotification(auth()->user(), $pwd);
        } catch (\Throwable $th) {
```

### [Riga 49 - AdminController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php#L49)
**Commento:**
```text
// logga l'errore
```
**Codice di riferimento:**
```php
        }

        return response()->json([
                'user' => $user,
```

### [Riga 64 - AdminController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php#L64)
**Commento:**
```text
// ?? $user->sys_role serve per avere un valore valido se l'utente non inserisce un ruolo (poco probabile ma megio controllare)
```
**Codice di riferimento:**
```php
            && ($validated['sys_role'] ?? $user->sys_role) === 'user') {
                return response()->json(
                    ['errors' =>
                        ['sys_role' => ['Non puoi modificare il ruolo all\'ultimo admin']]
```

### [Riga 94 - AdminController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php#L94)
**Commento:**
```text
// helper per la generazione di password
```
**Codice di riferimento:**
```php
    private function generatePwd(): string
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
```


---

## app/Http/Controllers/MemberController.php

Percorso file: [ app/Http/Controllers/MemberController.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php)

### [Riga 18 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L18)
**Commento:**
```text
// Controlla se l'utente del progetto associato alla tabella pivot già esista
```
**Codice di riferimento:**
```php
        if ($project->members()->wherePivot('user_id', $user->id)->exists()) {
            return response()->json(
                ['errors' => ['user_id' => ['Questo utente è già membro del progetto.']]],
                422
```

### [Riga 26 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L26)
**Commento:**
```text
//withTrashed serve per non escludere automaticamente i membri eliminati con il soft delete
```
**Codice di riferimento:**
```php
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();
```

### [Riga 33 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L33)
**Commento:**
```text
// Riaggiunge l'utente che era già stato assegnato al progetto
```
**Codice di riferimento:**
```php
            $member->update([
                'role' => $validated['role'],
            ]);

```

### [Riga 39 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L39)
**Commento:**
```text
// Si usa attach e non create perchè la relazione è belongsToMany
```
**Codice di riferimento:**
```php
                'role' => $validated['role'],
            ]);
        }

```

### [Riga 53 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L53)
**Commento:**
```text
// logga l'errore
```
**Codice di riferimento:**
```php
        }

        return response()->json([
            'member'     => $member,
```

### [Riga 76 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L76)
**Commento:**
```text
// Aggiorna la tabella pivot con il nuovo ruolo
```
**Codice di riferimento:**
```php
        $project->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);
        return response()->json(['ok' => true]);
    }

```

### [Riga 91 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L91)
**Commento:**
```text
// Seleziona il titolo per ogni task assegnata all'utente
```
**Codice di riferimento:**
```php
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->select('title')->get();

        if ($assignedTitles->isNotEmpty()) {
```

### [Riga 118 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L118)
**Commento:**
```text
// rimuove l'utente dal progetto, si usa detach per la relazione belongsToMany
```
**Codice di riferimento:**
```php
        return response()->json(['ok' => true]);
    }

    // Collezione di utenti non assegnati a un progetto
```

### [Riga 123 - MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L123)
**Commento:**
```text
// Collezione di utenti non assegnati a un progetto
```
**Codice di riferimento:**
```php
    public function availableUsers(Project $project){
        $projectUsersIds = $project->members()->select('user_id');
        $available = User::whereNotIn('id', $projectUsersIds)->get(['id', 'name', 'email']);
        return response()->json($available);
```


---

## app/Http/Controllers/ProjectColumnsController.php

Percorso file: [ app/Http/Controllers/ProjectColumnsController.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php)

### [Riga 29 - ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php#L29)
**Commento:**
```text
// Prende la posizione dell'ultima colonna e la incrementa
```
**Codice di riferimento:**
```php
            'is_done' => $type->is_done,
        ]);
        return response()->json($column->load('columnType')); // Restituisce la colonna con la relazione del tipo di colonna caricata
    }
```

### [Riga 32 - ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php#L32)
**Commento:**
```text
// Restituisce la colonna con la relazione del tipo di colonna caricata
```
**Codice di riferimento:**
```php
    }

    // Mostra solo le colonne non ancora usate
    public function availableTypes(Project $project)
```

### [Riga 35 - ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php#L35)
**Commento:**
```text
// Mostra solo le colonne non ancora usate
```
**Codice di riferimento:**
```php
    public function availableTypes(Project $project)
    {
        $usedTypeIds = $project->columns()->select('column_type_id');
        $available = ColumnType::whereNotIn('id', $usedTypeIds)->orderBy('position')->get();
```

### [Riga 43 - ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php#L43)
**Commento:**
```text
// Gestione dello spostamento delle task
```
**Codice di riferimento:**
```php
    public function reorder(UpdateProjectColumnPositionRequest $request, Project $project)
    {
        $validated = $request->validated();

```

### [Riga 48 - ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php#L48)
**Commento:**
```text
// chiave = posizione, valore = ID colonna
```
**Codice di riferimento:**
```php
            $project->columns()->where('id', $columnId)->update(['position' => $position]);
        }

        return response()->json(['ok' => true]);
```


---

## app/Http/Controllers/ProjectController.php

Percorso file: [ app/Http/Controllers/ProjectController.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php)

### [Riga 20 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L20)
**Commento:**
```text
// Controllo sul ruolo utente per la modifica da frontend
```
**Codice di riferimento:**
```php
            'projects'         => $loadProjects['projects'],
            'archivedProjects' => $loadProjects['archivedProjects'],
        ]);
    }
```

### [Riga 29 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L29)
**Commento:**
```text
// assegna isArchived solo se è diverso da null
```
**Codice di riferimento:**
```php
        $loadProjects = $this->loadProjects($user);
        $canEdit = $this->resolvePermissions($loadProjects['isAdmin'], $project, $user);

        $canManageProject = $canEdit['isPm'];
```

### [Riga 45 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L45)
**Commento:**
```text
// Itera il risultato della collection
```
**Codice di riferimento:**
```php
            $arr = $m->toArray(); // Trasforma il singolo user in un array
            $arr['pivot_role'] = $m->pivot->role; // Aggiunge il campo pivot_role con il ruolo del membro
            return $arr;
        });
```

### [Riga 46 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L46)
**Commento:**
```text
// Trasforma il singolo user in un array
```
**Codice di riferimento:**
```php
            $arr['pivot_role'] = $m->pivot->role; // Aggiunge il campo pivot_role con il ruolo del membro
            return $arr;
        });

```

### [Riga 47 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L47)
**Commento:**
```text
// Aggiunge il campo pivot_role con il ruolo del membro
```
**Codice di riferimento:**
```php
            return $arr;
        });

        $activity = $project->activityLogs()->with('user')->get()->map(function ($log) { // Itera i log e gli user per ogni log (relazione)
```

### [Riga 51 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L51)
**Commento:**
```text
// Itera i log e gli user per ogni log (relazione)
```
**Codice di riferimento:**
```php
            $arr = $log->toArray();
            $arr['user_name'] = $log->user?->name;
            return $arr;
        });
```

### [Riga 61 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L61)
**Commento:**
```text
// Controllo sul ruolo utente PM
```
**Codice di riferimento:**
```php
            'canEdit'          => $canEdit['canEdit'], // controllo ulteriore se il ruolo di sistema è admin
            'isArchived'       => $isArchived,
            'members'          => $members,
            'labels'           => $labels,
```

### [Riga 62 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L62)
**Commento:**
```text
// controllo ulteriore se il ruolo di sistema è admin
```
**Codice di riferimento:**
```php
            'isArchived'       => $isArchived,
            'members'          => $members,
            'labels'           => $labels,
            'activity'         => $activity,
```

### [Riga 68 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L68)
**Commento:**
```text
// Variabili per la sidebar
```
**Codice di riferimento:**
```php
            'archivedProjects' => $loadProjects['archivedProjects'],
            'projects'  => $loadProjects['projects'],
            'currentProject'   => $project,
        ]);
```

### [Riga 78 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L78)
**Commento:**
```text
// Imposta l'utente che crea il progetto come PM
```
**Codice di riferimento:**
```php
        $project->members()->attach($request->user()->id, ['role' => 'pm']);
        // genera le etichette di sistema, createMany itera ogni elemento dell'array
        $project->labels()->createMany($this->systemLabels());

```

### [Riga 80 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L80)
**Commento:**
```text
// genera le etichette di sistema, createMany itera ogni elemento dell'array
```
**Codice di riferimento:**
```php
        $project->labels()->createMany($this->systemLabels());

        return redirect()->route('projects.index')->with('success', 'Progetto creato con successo!');
    }
```

### [Riga 129 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L129)
**Commento:**
```text
// Mostra i progetti per gli utenti non eliminati
```
**Codice di riferimento:**
```php
                ->orderBy('updated_at')
                ->get();
            $archivedProjects = $user->projects()
                ->archived()
```

### [Riga 158 - ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php#L158)
**Commento:**
```text
// Controlla dalla tabella pivot con role se esiste effettivamente il ruolo
```
**Codice di riferimento:**
```php
                if ($role === null) {
                    abort(403, 'Non sei membro di questo progetto.');
                }

```


---

## app/Http/Controllers/TaskController.php

Percorso file: [ app/Http/Controllers/TaskController.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php)

### [Riga 30 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L30)
**Commento:**
```text
// sync allinea la relazione all'elenco esatto di id (aggiunge/rimuove/mantiene)
```
**Codice di riferimento:**
```php
        $task->assignees()->sync($validated['assignee_ids']);
        $task->labels()->sync($validated['label_ids'] ?? []); // se l'utente non seleziona etichette passa array vuoto

        if (!empty($validated['subtasks'])) {
```

### [Riga 32 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L32)
**Commento:**
```text
// se l'utente non seleziona etichette passa array vuoto
```
**Codice di riferimento:**
```php
        if (!empty($validated['subtasks'])) {
            foreach ($validated['subtasks'] as $sub) {
                $task->subtasks()->create([
                    'title' => $sub['title'],
```

### [Riga 43 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L43)
**Commento:**
```text
// Utenti assegnati alla task
```
**Codice di riferimento:**
```php
        $assigneeUsers = User::whereIn('id', $validated['assignee_ids'])->get();
        $assigneeNames = $assigneeUsers->pluck('name')->implode(', '); // Vengono estratti i nomi in formato stringa per fornirli al log

        $activity = $project->activityLogs()->create([
```

### [Riga 45 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L45)
**Commento:**
```text
// Vengono estratti i nomi in formato stringa per fornirli al log
```
**Codice di riferimento:**
```php
        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'type'    => 'task.created',
```

### [Riga 79 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L79)
**Commento:**
```text
// Se cambia il nome salva quello vecchio per il log
```
**Codice di riferimento:**
```php
        if ($validated['title'] !== $task->title) {
            $oldTitle = $task->title;
        }

```

### [Riga 95 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L95)
**Commento:**
```text
// Estrae tutti gli ID degli utenti assegnati alla task
```
**Codice di riferimento:**
```php
            $previousIds = $task->assignees()->pluck('users.id')->all();
            // Imposta i nuovi assegnatari alla task
            $task->assignees()->sync($validated['assignee_ids']);
            // Salva i cambiamenti che ci sono stati
```

### [Riga 97 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L97)
**Commento:**
```text
// Imposta i nuovi assegnatari alla task
```
**Codice di riferimento:**
```php
            $task->assignees()->sync($validated['assignee_ids']);
            // Salva i cambiamenti che ci sono stati
            $addedIds = array_diff($validated['assignee_ids'], $previousIds);
            // Se ci sono stati cambiamenti manda per ogni nuovo utente assegnato alla task una mail
```

### [Riga 99 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L99)
**Commento:**
```text
// Salva i cambiamenti che ci sono stati
```
**Codice di riferimento:**
```php
            $addedIds = array_diff($validated['assignee_ids'], $previousIds);
            // Se ci sono stati cambiamenti manda per ogni nuovo utente assegnato alla task una mail
            if (!empty($addedIds)) {
                $names = User::whereIn('id', $addedIds)->pluck('name')->implode(', ');
```

### [Riga 101 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L101)
**Commento:**
```text
// Se ci sono stati cambiamenti manda per ogni nuovo utente assegnato alla task una mail
```
**Codice di riferimento:**
```php
            if (!empty($addedIds)) {
                $names = User::whereIn('id', $addedIds)->pluck('name')->implode(', ');
                $newAssigned = User::whereIn('id', $addedIds)->get();
                foreach ($newAssigned as $new) {
```

### [Riga 116 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L116)
**Commento:**
```text
// Sincronizza le etichette se ci sono stati cambiamenti
```
**Codice di riferimento:**
```php
        if (!empty($validated['label_ids'])) {
            $task->labels()->sync($validated['label_ids']);
        }

```

### [Riga 121 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L121)
**Commento:**
```text
// Sincronizza le subtasks se ci sono stati cambiamenti
```
**Codice di riferimento:**
```php
        if (!empty($validated['subtasks'])) {
            $this->syncSubtasks($task, $validated['subtasks']);
        }

```

### [Riga 126 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L126)
**Commento:**
```text
// Genera messaggio per il log
```
**Codice di riferimento:**
```php
        $meta = ['task_title' => $task->title];
        $type = 'task.updated';

        if ($names) {
```

### [Riga 156 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L156)
**Commento:**
```text
// Controllo sullo spostamento della task solamente all'interno del priopio progetto
```
**Codice di riferimento:**
```php
                Rule::exists('project_columns', 'id')->where('project_id', $project->id),
            ],
        ]);

```

### [Riga 161 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L161)
**Commento:**
```text
// Salva vecchia colonna per log
```
**Codice di riferimento:**
```php
        $task->update(['column_id' => $validated['column_id']]);

        $task->load('column'); // Carica la relazione column nella task
        $toColumn = $task->column->name; // Nuova colonna
```

### [Riga 165 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L165)
**Commento:**
```text
// Carica la relazione column nella task
```
**Codice di riferimento:**
```php
        $toColumn = $task->column->name; // Nuova colonna

        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
```

### [Riga 166 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L166)
**Commento:**
```text
// Nuova colonna
```
**Codice di riferimento:**
```php
        $activity = $project->activityLogs()->create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'type'    => 'task.moved',
```

### [Riga 179 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L179)
**Commento:**
```text
// Estrae gli assegnari della task
```
**Codice di riferimento:**
```php
        $emailSent = true;

        foreach ($assignees as $user) {
            try {
```

### [Riga 200 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L200)
**Commento:**
```text
// Salva titolo della task per log
```
**Codice di riferimento:**
```php
        $task->delete();

        $activity = $project->activityLogs()->create([
            'task_id' => null,  // la task non esiste più
```

### [Riga 205 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L205)
**Commento:**
```text
// la task non esiste più
```
**Codice di riferimento:**
```php
            'user_id' => auth()->id(),
            'type'    => 'task.deleted',
            'meta'    => ['task_title' => $title],
        ]);
```

### [Riga 216 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L216)
**Commento:**
```text
// Metodo che sincronizza le subtask (non si può utilizzare il metodo sync per via della relazione delle subtask che è one-to-may)
```
**Codice di riferimento:**
```php
    private function syncSubtasks(Task $task, array $subtasks): void
    {
        $task->subtasks()->delete(); // Cancella tutte le subtask associate alla task

```

### [Riga 219 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L219)
**Commento:**
```text
// Cancella tutte le subtask associate alla task
```
**Codice di riferimento:**
```php
        // Ricrea da zero tutte le subtask aggiornate
        foreach ($subtasks as $sub) {
            $task->subtasks()->create([
                'title' => $sub['title'],
```

### [Riga 221 - TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L221)
**Commento:**
```text
// Ricrea da zero tutte le subtask aggiornate
```
**Codice di riferimento:**
```php
        foreach ($subtasks as $sub) {
            $task->subtasks()->create([
                'title' => $sub['title'],
                'done'  => $sub['done'] ?? false,
```


---

## app/Http/Middleware/EnsureProjectRole.php

Percorso file: [ app/Http/Middleware/EnsureProjectRole.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php)

### [Riga 25 - EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php#L25)
**Commento:**
```text
// Admin di sistema vede tutto, senza essere nella pivot
```
**Codice di riferimento:**
```php
        if ($user->sys_role === 'admin') {
            $request->attributes->set('project_role', 'admin');
            return $next($request);
        }
```

### [Riga 33 - EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php#L33)
**Commento:**
```text
// Se il parametro è già un'istanza del modello Project
```
**Codice di riferimento:**
```php
        if ($projectParameter instanceof Project) {
            $project = $projectParameter;
        } else {
            // Se è solo un ID numerico o una stringa, cercalo nel database
```

### [Riga 37 - EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php#L37)
**Commento:**
```text
// Se è solo un ID numerico o una stringa, cercalo nel database
```
**Codice di riferimento:**
```php
            $project = Project::find($projectParameter);
        }

        if (!$project) {
```

### [Riga 48 - EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php#L48)
**Commento:**
```text
// parametro strict è l'equivalente del controllo ===. Viene controllato anche il tipo.
```
**Codice di riferimento:**
```php
        if (!$membership || !in_array($membership->pivot->role, $roles, true)) {
            abort(403);
        }

```


---

## app/Models/ActivityLog.php

Percorso file: [ app/Models/ActivityLog.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php)

### [Riga 13 - ActivityLog.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php#L13)
**Commento:**
```text
// 'task.created' | 'task.moved' | 'task.assigned' | 'comment.added'
```
**Codice di riferimento:**
```php
        'meta',
    ];

    protected function casts(): array
```

### [Riga 26 - ActivityLog.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php#L26)
**Commento:**
```text
// belongsTo: relazione inversa
```
**Codice di riferimento:**
```php
        return $this->belongsTo(Project::class);
    }

    public function task()
```


---

## app/Models/Project.php

Percorso file: [ app/Models/Project.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Project.php)

### [Riga 36 - Project.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Project.php#L36)
**Commento:**
```text
// Alias per risolvere scopeBinding delle rotte che cerca in automatico users() al posto di members
```
**Codice di riferimento:**
```php
    public function users()
    {
        return $this->members();
    }
```


---

## app/Models/User.php

Percorso file: [ app/Models/User.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php)

### [Riga 34 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L34)
**Commento:**
```text
// casts() converte automaticamente gli attributi del database in tipi specifici quando si leggono/scrivono
```
**Codice di riferimento:**
```php
    // viene fatto solamente se c'è una conversione da fare
    protected function casts(): array
    {
        return [
```

### [Riga 35 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L35)
**Commento:**
```text
// viene fatto solamente se c'è una conversione da fare
```
**Codice di riferimento:**
```php
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
```

### [Riga 48 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L48)
**Commento:**
```text
// belongsToMany è una relazione molti-a-molti
```
**Codice di riferimento:**
```php
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }
```

### [Riga 61 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L61)
**Commento:**
```text
// hasMany è una relazione uno-a-molti
```
**Codice di riferimento:**
```php
        return $this->hasMany(ActivityLog::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
```

### [Riga 72 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L72)
**Commento:**
```text
// Estrae le prime lettere di nome e cognome, p.es Matvej Rossi --> MR
```
**Codice di riferimento:**
```php
    public function initials(): string
    {
        return collect(explode(' ', $this->name)) // Spezza la stringa restituendo un array
            // map: applica una funzione a ogni elemento della collection e restituisce una nuova collection
```

### [Riga 75 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L75)
**Commento:**
```text
// Spezza la stringa restituendo un array
```
**Codice di riferimento:**
```php
            // map: applica una funzione a ogni elemento della collection e restituisce una nuova collection
            // mb_substr prende solo il primo carattere, viene utilizzato mb_substr (invece di substr) per gestire i caratteri Unicode
            ->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
```

### [Riga 76 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L76)
**Commento:**
```text
// map: applica una funzione a ogni elemento della collection e restituisce una nuova collection
```
**Codice di riferimento:**
```php
            // mb_substr prende solo il primo carattere, viene utilizzato mb_substr (invece di substr) per gestire i caratteri Unicode
            ->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode(''); // Unisce i due elementi dell'array (p.es ["M", "R"]) in una stringa MR
```

### [Riga 77 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L77)
**Commento:**
```text
// mb_substr prende solo il primo carattere, viene utilizzato mb_substr (invece di substr) per gestire i caratteri Unicode
```
**Codice di riferimento:**
```php
            ->map(fn ($p) => strtoupper(mb_substr($p, 0, 1)))
            ->take(2)
            ->implode(''); // Unisce i due elementi dell'array (p.es ["M", "R"]) in una stringa MR
    }
```

### [Riga 80 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L80)
**Commento:**
```text
// Unisce i due elementi dell'array (p.es ["M", "R"]) in una stringa MR
```
**Codice di riferimento:**
```php
    }

    // ── Notifiche custom ───────────────────────────────────────────────────────

```

### [Riga 86 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L86)
**Commento:**
```text
// Sovrascrive il metodo predefinito di Laravel per la notifica della mail.
```
**Codice di riferimento:**
```php
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail());
    }
```

### [Riga 92 - User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php#L92)
**Commento:**
```text
// Sovrascrive il metodo predefinito di Laravel per il reset della password.
```
**Codice di riferimento:**
```php
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }
```


---

## app/Notifications/CustomResetPassword.php

Percorso file: [ app/Notifications/CustomResetPassword.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomResetPassword.php)

### [Riga 11 - CustomResetPassword.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomResetPassword.php#L11)
**Commento:**
```text
// Messaggio di reset della password, sovrascrive quello di default di Laravel che è in inglese
```
**Codice di riferimento:**
```php
    public function toMail($notifiable): MailMessage
    {
        // Costruzione del url per il reset
        $url = url(route('password.reset', [
```

### [Riga 14 - CustomResetPassword.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomResetPassword.php#L14)
**Commento:**
```text
// Costruzione del url per il reset
```
**Codice di riferimento:**
```php
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
```


---

## app/Notifications/CustomVerifyEmail.php

Percorso file: [ app/Notifications/CustomVerifyEmail.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomVerifyEmail.php)

### [Riga 10 - CustomVerifyEmail.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomVerifyEmail.php#L10)
**Commento:**
```text
// Mesaggio di verifica email, sovrascrive quello di default di Laravel che è in inglese
```
**Codice di riferimento:**
```php
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifica la tua email · BrainFlow')
```


---

## app/Notifications/InviteMember.php

Percorso file: [ app/Notifications/InviteMember.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/InviteMember.php)

### [Riga 43 - InviteMember.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/InviteMember.php#L43)
**Commento:**
```text
// Genera l'URL per andare direttamente alla pagina del progetto
```
**Codice di riferimento:**
```php
        $projectUrl = route('projects.show', $this->project->id);

        return (new MailMessage)
            ->subject('Sei stato aggiunto a un nuovo progetto · BrainFlow')
```


---

## app/Notifications/TaskAssignment.php

Percorso file: [ app/Notifications/TaskAssignment.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php)

### [Riga 44 - TaskAssignment.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php#L44)
**Commento:**
```text
// Genera l'URL per andare direttamente alla pagina del task
```
**Codice di riferimento:**
```php
        $taskUrl = route('projects.show', $this->task->project->id);

        $mail = (new MailMessage)
            ->subject('Ti è stato assegnato un nuovo task · BrainFlow')
```

### [Riga 52 - TaskAssignment.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php#L52)
**Commento:**
```text
// La descrizione non è obbligatoria: la mostro solo se presente
```
**Codice di riferimento:**
```php
        if (!empty($this->task->description)) {
            $mail->line('**Descrizione:** ' . $this->task->description);
        }

```

### [Riga 57 - TaskAssignment.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php#L57)
**Commento:**
```text
// La data di scadenza non è obbligatoria: la mostro solo se presente
```
**Codice di riferimento:**
```php
        if (!empty($this->task->due_date)) {
            $mail->line('**Scadenza:** ' . $this->task->due_date->format('d.m.Y'));
        }

```


---

## app/Notifications/TaskMove.php

Percorso file: [ app/Notifications/TaskMove.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskMove.php)

### [Riga 47 - TaskMove.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskMove.php#L47)
**Commento:**
```text
// Genera l'URL per andare direttamente alla pagina del task
```
**Codice di riferimento:**
```php
        $taskUrl = route('projects.show', $this->task->project->id);

        return (new MailMessage)
            ->subject('Un task è stato spostato · BrainFlow')
```


---

## app/Notifications/UserRegistrationFromAdmin.php

Percorso file: [ app/Notifications/UserRegistrationFromAdmin.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/UserRegistrationFromAdmin.php)

### [Riga 42 - UserRegistrationFromAdmin.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/UserRegistrationFromAdmin.php#L42)
**Commento:**
```text
// URL della pagina di login per il primo accesso
```
**Codice di riferimento:**
```php
        $loginUrl = route('login');

        return (new MailMessage)
            ->subject('Il tuo account è stato creato · BrainFlow')
```


---

## app/Providers/AppServiceProvider.php

Percorso file: [ app/Providers/AppServiceProvider.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Providers/AppServiceProvider.php)

### [Riga 25 - AppServiceProvider.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Providers/AppServiceProvider.php#L25)
**Commento:**
```text
// richiede maiuscole e minuscole
```
**Codice di riferimento:**
```php
                ->numbers();     // richiede almeno un numero
        });
    }
}
```

### [Riga 26 - AppServiceProvider.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Providers/AppServiceProvider.php#L26)
**Commento:**
```text
// richiede almeno un numero
```
**Codice di riferimento:**
```php
        });
    }
}
```


---

## database/factories/UserFactory.php

Percorso file: [ database/factories/UserFactory.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/factories/UserFactory.php)

### [Riga 15 - UserFactory.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/factories/UserFactory.php#L15)
**Commento:**
```text
/**
     * The current password being used by the factory.
     */
```
**Codice di riferimento:**
```php
    protected static ?string $password;

    /**
     * Define the model's default state.
```


---

## database/migrations/2026_05_18_060719_create_tasks_table.php

Percorso file: [ database/migrations/2026_05_18_060719_create_tasks_table.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060719_create_tasks_table.php)

### [Riga 25 - 2026_05_18_060719_create_tasks_table.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060719_create_tasks_table.php#L25)
**Commento:**
```text
// task ↔ user (assignees)
```
**Codice di riferimento:**
```php
        Schema::create('task_user', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['task_id', 'user_id']);
```


---

## database/migrations/2026_05_18_060733_create_labels_table.php

Percorso file: [ database/migrations/2026_05_18_060733_create_labels_table.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060733_create_labels_table.php)

### [Riga 20 - 2026_05_18_060733_create_labels_table.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060733_create_labels_table.php#L20)
**Commento:**
```text
// label ↔ task
```
**Codice di riferimento:**
```php
        Schema::create('label_task', function (Blueprint $table) {
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->primary(['label_id', 'task_id']);
```


---

## database/migrations/2026_05_18_060744_create_activity_logs_table.php

Percorso file: [ database/migrations/2026_05_18_060744_create_activity_logs_table.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060744_create_activity_logs_table.php)

### [Riga 16 - 2026_05_18_060744_create_activity_logs_table.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations/2026_05_18_060744_create_activity_logs_table.php#L16)
**Commento:**
```text
// task.created, task.moved, task.assigned, comment.added
```
**Codice di riferimento:**
```php
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
```


---

## database/seeders/DatabaseSeeder.php

Percorso file: [ database/seeders/DatabaseSeeder.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php)

### [Riga 75 - DatabaseSeeder.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php#L75)
**Commento:**
```text
// Etichette di sistema (riutilizzate per ogni progetto)
```
**Codice di riferimento:**
```php
        $systemLabels = [
            ['name' => 'DIAGRAMMA',     'color' => '#6366f1'],
            ['name' => 'ANALISI',       'color' => '#8b5cf6'],
            ['name' => 'BACKEND',       'color' => '#3b82f6'],
```

### [Riga 124 - DatabaseSeeder.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php#L124)
**Commento:**
```text
// posizione incrementale per colonna (l'ordinamento della board si basa su `position`)
```
**Codice di riferimento:**
```php
        $positionByColumn = [];
        $taskModels = [];

        foreach ($tasks1 as $td) {
```

### [Riga 207 - DatabaseSeeder.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php#L207)
**Commento:**
```text
// ── Activity log ──────────────────────────────────────────────────────
```
**Codice di riferimento:**
```php
        // Le voci referenziano i task reali e usano la stessa struttura `meta`
        // prodotta dai controller (task.created, task.moved, task.assigned).
        ActivityLog::create([
            'project_id' => $p1->id,
```

### [Riga 208 - DatabaseSeeder.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php#L208)
**Commento:**
```text
// Le voci referenziano i task reali e usano la stessa struttura `meta`
```
**Codice di riferimento:**
```php
        // prodotta dai controller (task.created, task.moved, task.assigned).
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => $taskModels['Definire architettura DB']->id,
```

### [Riga 209 - DatabaseSeeder.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/seeders/DatabaseSeeder.php#L209)
**Commento:**
```text
// prodotta dai controller (task.created, task.moved, task.assigned).
```
**Codice di riferimento:**
```php
        ActivityLog::create([
            'project_id' => $p1->id,
            'task_id'    => $taskModels['Definire architettura DB']->id,
            'user_id'    => $christian->id,
```


---

## lang/it/passwords.php

Percorso file: [ lang/it/passwords.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/lang/it/passwords.php)

### [Riga 5 - passwords.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/lang/it/passwords.php#L5)
**Commento:**
```text
/*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | outcome such as failure due to an invalid password / reset token.
    |
    */
```
**Codice di riferimento:**
```php
    'reset' => 'La tua password è stata reimpostata.',
    'sent' => 'Ti abbiamo inviato via email il link per reimpostare la password.',
    'throttled' => 'Attendi prima di riprovare.',
    'token' => 'Questo token di reimpostazione della password non è valido.',
```


---

## resources/views/admin/users.blade.php

Percorso file: [ resources/views/admin/users.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php)

### [Riga 34 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L34)
**Commento:**
```text
{{-- Header sezione con azione, come nella tab Membri --}}
```
**Codice di riferimento:**
```php
            <div class="row" style="margin-bottom:16px">
                <div class="section-title" style="margin-bottom:0">
                    Tutti gli utenti
                    <span class="mono" x-text="users.length"></span>
```

### [Riga 46 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L46)
**Commento:**
```text
{{-- Lista utenti --}}
```
**Codice di riferimento:**
```php
            <div class="col" style="gap:8px">
                <template x-for="u in users" :key="u.id">
                    <div class="card">
                        <div class="card__bd row">
```

### [Riga 68 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L68)
**Commento:**
```text
{{-- Ruolo di sistema (cambio rapido) --}}
```
**Codice di riferimento:**
```php
                            <select class="select" style="width:auto;font-size:12px"
                                    :disabled="savingId === u.id"
                                    x-model="u.sys_role"
                                    @change="changeSysRole(u)">
```

### [Riga 105 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L105)
**Commento:**
```text
{{-- ── Modale crea / modifica ───────────────────────── --}}
```
**Codice di riferimento:**
```php
        <template x-if="showForm">
            <div class="modal" @click.self="closeForm()">
                <div class="modal__box" @click.stop>
                    <div class="modal__hd">
```

### [Riga 117 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L117)
**Commento:**
```text
{{-- Nome --}}
```
**Codice di riferimento:**
```php
                        <div class="field">
                            <label class="field__label">Nome <span class="req">*</span></label>
                            <input type="text" class="input"
                                   :class="formErrors.name ? 'is-error' : ''"
```

### [Riga 143 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L143)
**Commento:**
```text
{{-- Ruolo di sistema --}}
```
**Codice di riferimento:**
```php
                        <div class="field">
                            <label class="field__label">Ruolo di sistema</label>
                            <select class="select"
                                    :class="formErrors.sys_role ? 'is-error' : ''"
```

### [Riga 174 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L174)
**Commento:**
```text
{{-- ── Modale conferma eliminazione ─────────────────── --}}
```
**Codice di riferimento:**
```php
        <template x-if="showDeleteConfirm">
            <div class="modal" @click.self="showDeleteConfirm=false">
                <div class="modal__box modal__box--sm" @click.stop>
                    <div class="modal__hd">
```

### [Riga 188 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L188)
**Commento:**
```text
{{-- Errore mostrato come messaggio di validazione (scritta rossa) --}}
```
**Codice di riferimento:**
```php
                        <template x-if="deleteError">
                            <span class="field__error" x-text="deleteError"></span>
                        </template>
                    </div>
```

### [Riga 286 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L286)
**Commento:**
```text
// ── crea / modifica ────────────────────────────────
```
**Codice di riferimento:**
```php
                    openCreate() {
                        this.formMode   = 'create';
                        this.formId     = null;
                        this.form       = { name: '', email: '', sys_role: 'user' };
```

### [Riga 321 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L321)
**Commento:**
```text
// L'utente è stato creato. Se l'email di registrazione
```
**Codice di riferimento:**
```php
                                // non è partita, il backend lo segnala con email_sent=false:
                                // l'admin va avvisato che deve comunicare le credenziali in altro modo.
                                if (data.email_sent === false) {
                                    this.toast('warn', 'Utente creato, ma l\'email di registrazione non è stata inviata.');
```

### [Riga 322 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L322)
**Commento:**
```text
// non è partita, il backend lo segnala con email_sent=false:
```
**Codice di riferimento:**
```php
                                // l'admin va avvisato che deve comunicare le credenziali in altro modo.
                                if (data.email_sent === false) {
                                    this.toast('warn', 'Utente creato, ma l\'email di registrazione non è stata inviata.');
                                } else {
```

### [Riga 323 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L323)
**Commento:**
```text
// l'admin va avvisato che deve comunicare le credenziali in altro modo.
```
**Codice di riferimento:**
```php
                                if (data.email_sent === false) {
                                    this.toast('warn', 'Utente creato, ma l\'email di registrazione non è stata inviata.');
                                } else {
                                    this.toast('success', 'Utente creato.');
```

### [Riga 343 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L343)
**Commento:**
```text
// Update / create => errori mostrati come validazione inline
```
**Codice di riferimento:**
```php
                            if (err.validation) {
                                this.formErrors = err.validation;
                            } else {
                                this.toast('danger', err.message || 'Errore durante il salvataggio.');
```

### [Riga 354 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L354)
**Commento:**
```text
// ── cambio rapido ruolo (toast) ─────────────────────
```
**Codice di riferimento:**
```php
                    async changeSysRole(u) {
                        this.savingId = u.id;
                        const idx = this.users.findIndex(x => x.id === u.id);
                        const previous = idx !== -1 ? this.users[idx].sys_role : null;
```

### [Riga 371 - users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php#L371)
**Commento:**
```text
// ── eliminazione (messaggio di validazione) ─────────
```
**Codice di riferimento:**
```php
                    askDelete(u) {
                        this.deleteTarget      = u;
                        this.deleteError       = '';
                        this.showDeleteConfirm = true;
```


---

## resources/views/auth/confirm-password.blade.php

Percorso file: [ resources/views/auth/confirm-password.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth/confirm-password.blade.php)

### [Riga 9 - confirm-password.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth/confirm-password.blade.php#L9)
**Commento:**
```text
<!-- Password -->
```
**Codice di riferimento:**
```php
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
```


---

## resources/views/auth/login.blade.php

Percorso file: [ resources/views/auth/login.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth/login.blade.php)

### [Riga 40 - login.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth/login.blade.php#L40)
**Commento:**
```text
{{-- Messaggio di sessione (es. "Password reimpostata con successo") --}}
```
**Codice di riferimento:**
```php
                    @if (session('status'))
                        <div class="info-banner">
                            {{ session('status') }}
                        </div>
```

### [Riga 47 - login.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth/login.blade.php#L47)
**Commento:**
```text
{{-- Errore generico di autenticazione --}}
```
**Codice di riferimento:**
```php
                    @if ($errors->any())
                        <div class="warn-banner">
                            {{ $errors->first() }}
                        </div>
```


---

## resources/views/components/modal.blade.php

Percorso file: [ resources/views/components/modal.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/modal.blade.php)

### [Riga 24 - modal.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/modal.blade.php#L24)
**Commento:**
```text
// All non-disabled elements...
```
**Codice di riferimento:**
```php
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
```


---

## resources/views/projects/index.blade.php

Percorso file: [ resources/views/projects/index.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/index.blade.php)

### [Riga 138 - index.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/index.blade.php#L138)
**Commento:**
```text
{{-- Campo Nome --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label" for="proj_name">
                            Nome progetto <span class="req">*</span>
                        </label>
```


---

## resources/views/projects/show.blade.php

Percorso file: [ resources/views/projects/show.blade.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php)

### [Riga 218 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L218)
**Commento:**
```text
{{-- Add task --}}
```
**Codice di riferimento:**
```php
                <div class="column__add" x-show="canEdit">
                    <button class="btn btn--ghost btn--sm btn--block"
                            @click="openNewTask(col.id)">
                        <x-icon name="plus" size="sm" /> Aggiungi task
```

### [Riga 440 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L440)
**Commento:**
```text
{{-- ── Task detail drawer ───────────────────────────── --}}
```
**Codice di riferimento:**
```php
    <template x-if="openTaskId !== null && taskDraft !== null">
        <div>
            <div class="scrim" @click="closeDrawer()"></div>
            <div class="drawer">
```

### [Riga 479 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L479)
**Commento:**
```text
{{-- Assegnatari --}}
```
**Codice di riferimento:**
```php
                        <span class="detail-grid__label">Assegnatari <span class="req">*</span></span>
                        <div class="col" style="gap:6px">
                            <div class="row" style="flex-wrap:wrap;gap:6px"
                                 x-show="taskDraft.assignee_ids.length > 0">
```

### [Riga 512 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L512)
**Commento:**
```text
{{-- Scadenza --}}
```
**Codice di riferimento:**
```php
                        <span class="detail-grid__label">Scadenza</span>
                        <input type="date" class="input"
                               :class="taskDraftErrors.due_date ? 'is-error' : ''"
                               x-model="taskDraft.due_date"
```

### [Riga 519 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L519)
**Commento:**
```text
{{-- Etichette --}}
```
**Codice di riferimento:**
```php
                        <span class="detail-grid__label">Etichette</span>
                        <div class="col" style="gap:6px">
                            <div class="row" style="flex-wrap:wrap;gap:4px"
                                 x-show="taskDraft.label_ids.length > 0">
```

### [Riga 558 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L558)
**Commento:**
```text
{{-- Sottoattività --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <div class="section-title">
                            Sottoattività
                            <span class="mono" x-text="draftSubtaskStats()"></span>
```

### [Riga 637 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L637)
**Commento:**
```text
{{-- Campo Nome --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label" for="edit_name">
                            Nome progetto <span class="req">*</span>
                        </label>
```

### [Riga 697 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L697)
**Commento:**
```text
{{-- ── New task modal ──────────────────────────────── --}}
```
**Codice di riferimento:**
```php
    <template x-if="showNewTask">
        <div class="modal" @click.self="showNewTask=false">
            <div class="modal__box" @click.stop style="max-width:560px">
                <div class="modal__hd">
```

### [Riga 740 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L740)
**Commento:**
```text
{{-- Scadenza --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label">Scadenza</label>
                        <input type="date" class="input"
                               :class="newTaskErrors.due_date ? 'is-error' : ''"
```

### [Riga 751 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L751)
**Commento:**
```text
{{-- Assegnatari --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label">
                            Assegnatari <span class="req">*</span>
                        </label>
```

### [Riga 757 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L757)
**Commento:**
```text
{{-- Chip degli assegnatari già aggiunti --}}
```
**Codice di riferimento:**
```php
                        <div class="row" style="flex-wrap:wrap;gap:6px;margin-bottom:8px"
                             x-show="newTaskAssignees.length > 0">
                            <template x-for="userId in newTaskAssignees" :key="userId">
                            <span class="chip">
```

### [Riga 775 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L775)
**Commento:**
```text
{{-- Select per aggiungere un assegnatario --}}
```
**Codice di riferimento:**
```php
                        <select class="select"
                                :class="newTaskErrors.assignees ? 'is-error' : ''"
                                @change="addNewTaskAssignee($event.target.value); $event.target.value=''">
                            <option value="">+ Aggiungi assegnatario</option>
```

### [Riga 790 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L790)
**Commento:**
```text
{{-- Etichette --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label">Etichette</label>

                        <div class="row" style="flex-wrap:wrap;gap:4px;margin-bottom:8px"
```

### [Riga 836 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L836)
**Commento:**
```text
{{-- Sottoattività --}}
```
**Codice di riferimento:**
```php
                    <div class="field">
                        <label class="field__label">Sottoattività</label>

                        <div class="subtasks" x-show="newTaskSubtasks.length > 0">
```

### [Riga 1083 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1083)
**Commento:**
```text
{{-- Banner errore (stile coerente con quello del modal di creazione) --}}
```
**Codice di riferimento:**
```php
                    <template x-if="deleteColError">
                        <div class="warn-banner" x-text="deleteColError"></div>
                    </template>
                </div>
```

### [Riga 1103 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1103)
**Commento:**
```text
{{-- ── Delete task confirm modal ─────────────────────── --}}
```
**Codice di riferimento:**
```php
    <template x-if="showDeleteTaskConfirm">
        <div class="modal" @click.self="showDeleteTaskConfirm=false">
            <div class="modal__box modal__box--sm" @click.stop>
                <div class="modal__hd">
```

### [Riga 1230 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1230)
**Commento:**
```text
// ── new task state ──────────────────────────────────
```
**Codice di riferimento:**
```php
        newTaskTitle:       '',
        newTaskPriority:    'medium',
        newTaskColId:       null,
        newTaskDueDate:     '',
```

### [Riga 1236 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1236)
**Commento:**
```text
// array di user id
```
**Codice di riferimento:**
```php
        newTaskLabels:      [],   // array di label id
        newTaskSubtasks:    [],   // array di { title, done }
        newSubtaskDraft:    '',
        newTaskErrors:      {},
```

### [Riga 1237 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1237)
**Commento:**
```text
// array di label id
```
**Codice di riferimento:**
```php
        newTaskSubtasks:    [],   // array di { title, done }
        newSubtaskDraft:    '',
        newTaskErrors:      {},

```

### [Riga 1238 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1238)
**Commento:**
```text
// array di { title, done }
```
**Codice di riferimento:**
```php
        newSubtaskDraft:    '',
        newTaskErrors:      {},

        taskDraft:       null,   // bozza editabile, indipendente da this.tasks
```

### [Riga 1242 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1242)
**Commento:**
```text
// bozza editabile, indipendente da this.tasks
```
**Codice di riferimento:**
```php
        taskDraftErrors: {},
        savingTask:      false,

        showNewTask:        false,
```

### [Riga 1314 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1314)
**Commento:**
```text
// ← se doneId è null, nessuna è esclusa
```
**Codice di riferimento:**
```php
            );
        },

        columnName(colId) {
```

### [Riga 1362 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1362)
**Commento:**
```text
// Funziona sia per "2026-06-30", "2026-06-30T...", "2026-06-30 00:00:00"
```
**Codice di riferimento:**
```php
            return String(d).slice(0, 10);
        },

        memberName(userId) {
```

### [Riga 1389 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1389)
**Commento:**
```text
// Pulisce l'errore se l'utente ha appena risolto la condizione
```
**Codice di riferimento:**
```php
            if (this.newTaskAssignees.length > 0) {
                delete this.newTaskErrors.assignees;
            }
        },
```

### [Riga 1411 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1411)
**Commento:**
```text
// Sottoattività della bozza
```
**Codice di riferimento:**
```php
        draftAddSubtask() {
            const title = this.newDraftSubtaskTitle.trim();
            if (!title || !this.taskDraft) return;
            this.taskDraft.subtasks.push({
```

### [Riga 1500 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1500)
**Commento:**
```text
// Se il verbo è già "ha assegnato" (task.assigned) non ripetere "assegnata":
```
**Codice di riferimento:**
```php
                // basta " a Marta". Per gli altri tipi usa " e assegnata a ".
                const connector = entry.type === 'task.assigned'
                    ? ' a '
                    : ' e l\'ha assegnato a ';
```

### [Riga 1501 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1501)
**Commento:**
```text
// basta " a Marta". Per gli altri tipi usa " e assegnata a ".
```
**Codice di riferimento:**
```php
                const connector = entry.type === 'task.assigned'
                    ? ' a '
                    : ' e l\'ha assegnato a ';
                text += `${connector}${entry.meta.assigned_to}`;
```

### [Riga 1515 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1515)
**Commento:**
```text
// Restituisce gli oggetti completi degli assegnatari della bozza
```
**Codice di riferimento:**
```php
        draftAssignees() {
            if (!this.taskDraft) return [];
            return this.taskDraft.assignee_ids
                .map(id => this.members.find(m => m.id == id))
```

### [Riga 1523 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1523)
**Commento:**
```text
// Restituisce gli oggetti completi delle etichette della bozza
```
**Codice di riferimento:**
```php
        draftLabels() {
            if (!this.taskDraft) return [];
            return this.taskDraft.label_ids
                .map(id => this.labels.find(l => l.id == id))
```

### [Riga 1567 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1567)
**Commento:**
```text
// Nome+cognome (full name) — distinto da memberName che era solo "name"
```
**Codice di riferimento:**
```php
        memberFullName(userId) {
            const m = this.members.find(m => m.id == userId);
            if (!m) return '?';
            // Se hai already first_name/last_name separati usali, altrimenti m.name è sufficiente
```

### [Riga 1571 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1571)
**Commento:**
```text
// Se hai already first_name/last_name separati usali, altrimenti m.name è sufficiente
```
**Codice di riferimento:**
```php
            return m.full_name || `${m.first_name || ''} ${m.last_name || ''}`.trim() || m.name || '?';
        },

        // ── drawer ─────────────────────────────────────────
```

### [Riga 1605 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1605)
**Commento:**
```text
// ── task CRUD ──────────────────────────────────────
```
**Codice di riferimento:**
```php
        openNewTask(colId) {
            if (!this.canEdit) return;
            this.newTaskColId       = colId;
            this.newTaskTitle       = '';
```

### [Riga 1640 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1640)
**Commento:**
```text
// Costruisci il payload
```
**Codice di riferimento:**
```php
            const payload = {
                title:        this.newTaskTitle.trim(),
                description:  this.newTaskDescription.trim() || null,
                priority:     this.newTaskPriority,
```

### [Riga 1669 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1669)
**Commento:**
```text
// Mappa i nomi backend → frontend in modo esplicito
```
**Codice di riferimento:**
```php
                    const fieldMap = {
                        'title':        'title',
                        'description':  'description',
                        'priority':     'priority',
```

### [Riga 1685 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1685)
**Commento:**
```text
// Sottoattività: chiavi come 'subtasks.0.title' → raggruppiamo in 'subtasks'
```
**Codice di riferimento:**
```php
                        if (backendKey.startsWith('subtasks')) {
                            mapped.subtasks = mapped.subtasks || msg;
                            continue;
                        }
```

### [Riga 1691 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1691)
**Commento:**
```text
// Campo conosciuto → usa il mapping
```
**Codice di riferimento:**
```php
                        const frontendKey = fieldMap[backendKey] || backendKey;
                        mapped[frontendKey] = msg;
                    }

```

### [Riga 1705 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1705)
**Commento:**
```text
// Successo: il backend ritorna la task creata con assignees/labels/subtasks già popolati
```
**Codice di riferimento:**
```php
            const result = await response.json();
            this.tasks.push(result.task);

            const newActs = result.activities || (result.activity ? [result.activity] : []);
```

### [Riga 1764 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1764)
**Commento:**
```text
// ── 4. Gestione errori dal backend ──
```
**Codice di riferimento:**
```php
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));

                if (errorData.errors) {
```

### [Riga 1799 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1799)
**Commento:**
```text
// ── 5. Successo: aggiorna lo stato ──
```
**Codice di riferimento:**
```php
            const data = await response.json();
            const task = data.task || data;

            if (!task || !task.id) {
```

### [Riga 1830 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1830)
**Commento:**
```text
// ── Optimistic update: aggiorna subito la UI ──
```
**Codice di riferimento:**
```php
            const previousColId = t.column_id;
            t.column_id = newColId;

            // ── Chiamata al backend ──
```

### [Riga 1846 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1846)
**Commento:**
```text
// Rollback: ripristina la colonna precedente
```
**Codice di riferimento:**
```php
                t.column_id = previousColId;
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Impossibile spostare la task.' }
                }));
```

### [Riga 1870 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1870)
**Commento:**
```text
// Esegue l'eliminazione effettiva
```
**Codice di riferimento:**
```php
        async confirmDeleteTask() {
            if (!this.deleteTaskTarget) return;

            const taskId = this.deleteTaskTarget.id;
```

### [Riga 1892 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1892)
**Commento:**
```text
// Aggiorna stato locale: rimuovi la task
```
**Codice di riferimento:**
```php
            this.tasks = this.tasks.filter(t => t.id !== taskId);

            // Se il backend restituisce activity, aggiungila al feed
            const data = await response.json().catch(() => ({}));
```

### [Riga 1895 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1895)
**Commento:**
```text
// Se il backend restituisce activity, aggiungila al feed
```
**Codice di riferimento:**
```php
            const data = await response.json().catch(() => ({}));
            const newActs = data.activities || (data.activity ? [data.activity] : []);
            newActs.forEach(a => this.activity.unshift(a));

```

### [Riga 1904 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1904)
**Commento:**
```text
// Chiudi modal e drawer
```
**Codice di riferimento:**
```php
            this.showDeleteTaskConfirm = false;
            this.deleteTaskTarget      = null;
            this.deletingTask          = false;
            this.closeDrawer();
```

### [Riga 1961 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1961)
**Commento:**
```text
// Apre il modal di conferma
```
**Codice di riferimento:**
```php
        askDeleteColumn(col) {
            if (!this.isPm) return;
            this.deleteColTarget   = col;
            this.deleteColError    = '';
```

### [Riga 1969 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1969)
**Commento:**
```text
// Esegue l'eliminazione effettiva
```
**Codice di riferimento:**
```php
        async confirmDeleteColumn() {
            if (!this.deleteColTarget) return;

            const colId   = this.deleteColTarget.id;
```

### [Riga 2001 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L2001)
**Commento:**
```text
// Chiudi il modal
```
**Codice di riferimento:**
```php
            this.showDeleteColConfirm = false;
            this.deleteColTarget      = null;
            this.deletingCol          = false;

```

### [Riga 2057 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L2057)
**Commento:**
```text
// Rollback: ripristina il ruolo precedente nello stato Alpine
```
**Codice di riferimento:**
```php
                if (m && previousRole) m.pivot_role = previousRole;

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'danger', message: data.message || 'Impossibile aggiornare il ruolo.' }
```

### [Riga 2330 - show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L2330)
**Commento:**
```text
// ⬇ ora chiama moveTask che gestisce backend + attività
```
**Codice di riferimento:**
```php
                            this.moveTask(taskId, newColId);
                        }
                    },
                });
```


---

## routes/console.php

Percorso file: [ routes/console.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/console.php)

### [Riga 13 - console.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/console.php#L13)
**Commento:**
```text
//Lancia il comando come processo separato non aspetta che finisca
```
**Codice di riferimento:**
```php
    ->runInBackground();
```


---

## routes/web.php

Percorso file: [ routes/web.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/web.php)

### [Riga 44 - web.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/web.php#L44)
**Commento:**
```text
// Colonne
```
**Codice di riferimento:**
```php
            Route::post('columns', [ProjectColumnsController::class, 'store'])
                ->name('projects.columns.store')
                ->middleware('project.access:pm');
            Route::get('columns/available-types', [ProjectColumnsController::class, 'availableTypes'])
```

### [Riga 86 - web.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/web.php#L86)
**Commento:**
```text
// Labels di progetto
```
**Codice di riferimento:**
```php
            Route::post('labels', [LabelController::class, 'store'])
                ->name('projects.labels.store')
                ->middleware('project.access:pm,developer');
            Route::patch('labels/{label}', [LabelController::class, 'update'])
```


---

## tests/Feature/ActivityLogTest.php

Percorso file: [ tests/Feature/ActivityLogTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php)

### [Riga 13 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L13)
**Commento:**
```text
/**
 * TC-029 – TC-031  Commenti/menzioni e activity log (REQ-011 / REQ-012)
 */
```
**Codice di riferimento:**
```php
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 29 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L29)
**Commento:**
```text
// TC-029  Descrizione task con menzione @username salvata correttamente
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskDescriptionMentionIsPersisted(): void
    {
        $mario = $this->makeUser();
```

### [Riga 71 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L71)
**Commento:**
```text
// La menzione deve essere riconosciuta e registrata.
```
**Codice di riferimento:**
```php
        // Fallisce finché il parsing e la persistenza delle menzioni non esistono.
        $this->assertDatabaseHas('task_mentions', [
            'task_id' => $task->id,
            'user_id' => $luca->id,
```

### [Riga 72 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L72)
**Commento:**
```text
// Fallisce finché il parsing e la persistenza delle menzioni non esistono.
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('task_mentions', [
            'task_id' => $task->id,
            'user_id' => $luca->id,
        ]);
```

### [Riga 80 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L80)
**Commento:**
```text
// TC-030  Registrazione degli eventi rilevanti nell'activity log
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testActivityLogRecordsTaskEvents(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 103 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L103)
**Commento:**
```text
// 1. Creazione task → log 'task.created'
```
**Codice di riferimento:**
```php
        $createResponse = $this->actingAs($pm)
            ->postJson(route('projects.tasks.store', $project), [
                'title'        => 'Task log test',
                'priority'     => 'medium',
```

### [Riga 122 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L122)
**Commento:**
```text
// 2. Spostamento task → log 'task.moved'
```
**Codice di riferimento:**
```php
        $this->actingAs($pm)
            ->patchJson(route('tasks.move', [$project, $task]), [
                'column_id' => $col2->id,
            ]);
```

### [Riga 134 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L134)
**Commento:**
```text
// 3. Aggiornamento con nuovo assegnatario → log 'task.assigned'
```
**Codice di riferimento:**
```php
        //    Il PM viene aggiunto come nuovo assegnatario: addedIds = [$pm->id]
        $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
```

### [Riga 135 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L135)
**Commento:**
```text
//    Il PM viene aggiunto come nuovo assegnatario: addedIds = [$pm->id]
```
**Codice di riferimento:**
```php
        $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
```

### [Riga 151 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L151)
**Commento:**
```text
// TC-031  Activity log accessibile a tutti i membri, incluso il Viewer
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testActivityLogIsAccessibleToViewer(): void
    {
        $pm     = $this->makeUser();
```

### [Riga 162 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L162)
**Commento:**
```text
// Voce di log pre-esistente
```
**Codice di riferimento:**
```php
        ActivityLog::create([
            'project_id' => $project->id,
            'task_id'    => null,
            'user_id'    => $pm->id,
```

### [Riga 171 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L171)
**Commento:**
```text
// Il Viewer può aprire la pagina del progetto (che include il log)
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($viewer)
            ->get(route('projects.show', $project));

        $response->assertOk();
```

### [Riga 178 - ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php#L178)
**Commento:**
```text
// Le voci nel log appartengono al progetto corrente
```
**Codice di riferimento:**
```php
        $activityInView = $response->viewData('activity');
        $this->assertNotEmpty($activityInView);

        $types = collect($activityInView)->pluck('type')->toArray();
```


---

## tests/Feature/AdminUsersTest.php

Percorso file: [ tests/Feature/AdminUsersTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php)

### [Riga 10 - AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php#L10)
**Commento:**
```text
/**
 * TC-007 – TC-009  Gestione utenti Admin (REQ-002 / REQ-NF-005)
 */
```
**Codice di riferimento:**
```php
class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 18 - AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php#L18)
**Commento:**
```text
// TC-007  L'Admin accede alla pagina di amministrazione utenti
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testAdminCanAccessUsersPage(): void
    {
        $admin = User::factory()->create([
```

### [Riga 34 - AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php#L34)
**Commento:**
```text
// TC-008  L'utente standard non può accedere all'amministrazione utenti
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testStandardUserCannotAccessAdminPage(): void
    {
        $user = User::factory()->create([
```

### [Riga 49 - AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php#L49)
**Commento:**
```text
// TC-009  L'Admin modifica il ruolo di sistema di un utente
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testAdminCanUpdateUserRole(): void
    {
        $admin = User::factory()->create([
```

### [Riga 72 - AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php#L72)
**Commento:**
```text
// Il ruolo nel DB è aggiornato ad admin
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('users', [
            'email'    => 'luca.verdi@example.com',
            'sys_role' => 'admin',
        ]);
```


---

## tests/Feature/AuthenticationTest.php

Percorso file: [ tests/Feature/AuthenticationTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php)

### [Riga 18 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L18)
**Commento:**
```text
// TC-001  Registrazione con dati validi
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testValidDataRegistration(): void
    {
        $response = $this->post('/register', [
```

### [Riga 29 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L29)
**Commento:**
```text
// Reindirizzamento alla dashboard dopo la registrazione
```
**Codice di riferimento:**
```php
        $response->assertRedirect(route('projects.index'));

        // L'utente esiste nel DB
        $this->assertDatabaseHas('users', [
```

### [Riga 32 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L32)
**Commento:**
```text
// L'utente esiste nel DB
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('users', [
            'email'    => 'mario.rossi@example.com',
            'sys_role' => 'user',
        ]);
```

### [Riga 38 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L38)
**Commento:**
```text
// La password è salvata come hash bcrypt (prefisso $2y$)
```
**Codice di riferimento:**
```php
        $user = User::where('email', 'mario.rossi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertStringStartsWith('$2y$', $user->password);
        $this->assertTrue(Hash::check('Password1', $user->password));
```

### [Riga 46 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L46)
**Commento:**
```text
// TC-002  Registrazione con email già esistente
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testRegistrationWithDuplicateEmail(): void
    {
        // Utente preesistente
```

### [Riga 50 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L50)
**Commento:**
```text
// Utente preesistente
```
**Codice di riferimento:**
```php
        User::factory()->create(['email' => 'mario.rossi@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'Mario Bianchi',
```

### [Riga 60 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L60)
**Commento:**
```text
// Errore di validazione sul campo email
```
**Codice di riferimento:**
```php
        $response->assertSessionHasErrors('email');

        // Nessun secondo utente creato con la stessa email
        $this->assertSame(1, User::where('email', 'mario.rossi@example.com')->count());
```

### [Riga 63 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L63)
**Commento:**
```text
// Nessun secondo utente creato con la stessa email
```
**Codice di riferimento:**
```php
        $this->assertSame(1, User::where('email', 'mario.rossi@example.com')->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 68 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L68)
**Commento:**
```text
// TC-003  Registrazione con password non conforme
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────

    /** Caso A – password troppo corta */
    public function testShortPassword(): void
```

### [Riga 71 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L71)
**Commento:**
```text
/** Caso A – password troppo corta */
```
**Codice di riferimento:**
```php
    public function testShortPassword(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
```

### [Riga 99 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L99)
**Commento:**
```text
/** Caso C – conferma password non corrisponde */
```
**Codice di riferimento:**
```php
    public function testPasswordConfirmationMismatch(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Mario Rossi',
```

### [Riga 114 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L114)
**Commento:**
```text
// TC-004  Login con credenziali corrette e logout
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testLoginAndLogout(): void
    {
        $user = User::factory()->create([
```

### [Riga 132 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L132)
**Commento:**
```text
// Accesso a rotta protetta
```
**Codice di riferimento:**
```php
        $this->actingAs($user)->get(route('projects.index'))->assertOk();

        // Logout
        $this->actingAs($user)->post('/logout');
```

### [Riga 138 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L138)
**Commento:**
```text
// Dopo logout la rotta protetta reindirizza al login
```
**Codice di riferimento:**
```php
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 143 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L143)
**Commento:**
```text
// TC-005  Login con credenziali errate
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testLoginWithWrongCredentials(): void
    {
        User::factory()->create([
```

### [Riga 157 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L157)
**Commento:**
```text
// Messaggio di errore generico (non distingue email / password)
```
**Codice di riferimento:**
```php
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

```

### [Riga 163 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L163)
**Commento:**
```text
// TC-006  Accesso a rotta protetta senza autenticazione (middleware auth)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testSafeRouteWithoutAuthentication(): void
    {
        // Visitatore non autenticato → reindirizzamento al login
```

### [Riga 167 - AuthenticationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AuthenticationTest.php#L167)
**Commento:**
```text
// Visitatore non autenticato → reindirizzamento al login
```
**Codice di riferimento:**
```php
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }
}
```


---

## tests/Feature/DashboardTest.php

Percorso file: [ tests/Feature/DashboardTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php)

### [Riga 13 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L13)
**Commento:**
```text
/**
 * TC-035 – TC-036  Dashboard riepilogativa del progetto (REQ-014 / REQ-NF-004)
 */
```
**Codice di riferimento:**
```php
class DashboardTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 29 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L29)
**Commento:**
```text
// TC-035  Dashboard con statistiche corrette
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDashboardShowsCorrectStatistics(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 52 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L52)
**Commento:**
```text
// 2 task in colonna "Completato" → assegnati al PM
```
**Codice di riferimento:**
```php
        $t1 = $project->tasks()->create(['title' => 'Task 1', 'priority' => 'high',   'column_id' => $doneCol->id,   'position' => 0]);
        $t2 = $project->tasks()->create(['title' => 'Task 2', 'priority' => 'medium', 'column_id' => $doneCol->id,   'position' => 1]);
        $t1->assignees()->attach($pm->id);
        $t2->assignees()->attach($pm->id);
```

### [Riga 58 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L58)
**Commento:**
```text
// 1 task attivo → assegnato al dev
```
**Codice di riferimento:**
```php
        $t3 = $project->tasks()->create(['title' => 'Task 3', 'priority' => 'low', 'column_id' => $activeCol->id, 'position' => 0]);
        $t3->assignees()->attach($dev->id);

        // 1 task scaduto (due_date ieri, colonna non-done) → assegnato al dev
```

### [Riga 62 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L62)
**Commento:**
```text
// 1 task scaduto (due_date ieri, colonna non-done) → assegnato al dev
```
**Codice di riferimento:**
```php
        $t4 = $project->tasks()->create([
            'title'     => 'Task scaduto',
            'priority'  => 'high',
            'column_id' => $activeCol->id,
```

### [Riga 72 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L72)
**Commento:**
```text
// Percentuale completamento: 2 completati su 4 totali = 50 %
```
**Codice di riferimento:**
```php
        $this->assertSame(50, $project->completionPercent());

        // La pagina del progetto (dashboard) è raggiungibile dal PM
        $response = $this->actingAs($pm)
```

### [Riga 75 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L75)
**Commento:**
```text
// La pagina del progetto (dashboard) è raggiungibile dal PM
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($pm)
            ->get(route('projects.show', $project));

        $response->assertOk();
```

### [Riga 82 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L82)
**Commento:**
```text
// Task scaduti: 1 (in colonna non-done con due_date passata)
```
**Codice di riferimento:**
```php
        $overdueTasks = $project->tasks()
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereHas('column', fn ($q) => $q->where('is_done', false))
            ->get();
```

### [Riga 91 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L91)
**Commento:**
```text
// Task per membro
```
**Codice di riferimento:**
```php
        $pmTaskCount  = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $pm->id))->count();
        $devTaskCount = $project->tasks()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $dev->id))->count();
```

### [Riga 102 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L102)
**Commento:**
```text
// TC-036  Dashboard con progetto senza task (caso limite – nessuna divisione per 0)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDashboardWithEmptyProjectShowsZeroWithoutError(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 110 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L110)
**Commento:**
```text
// Nessuna colonna, nessun task: completionPercent() deve restituire 0
```
**Codice di riferimento:**
```php
        $this->assertSame(0, $project->completionPercent());

        // La dashboard si carica correttamente senza errori 500
        $response = $this->actingAs($pm)
```

### [Riga 113 - DashboardTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/DashboardTest.php#L113)
**Commento:**
```text
// La dashboard si carica correttamente senza errori 500
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($pm)
            ->get(route('projects.show', $project));

        $response->assertOk();
```


---

## tests/Feature/EmailNotificationTest.php

Percorso file: [ tests/Feature/EmailNotificationTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php)

### [Riga 34 - EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php#L34)
**Commento:**
```text
// TC-032  Notifica email all'assegnazione di un task
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskAssignmentSendsEmailNotification(): void
    {
        Notification::fake();
```

### [Riga 69 - EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php#L69)
**Commento:**
```text
// TC-033  Notifica email all'avvicinarsi della scadenza (scheduler)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDueSoonNotificationSentByScheduler(): void
    {
        Notification::fake();
```

### [Riga 86 - EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php#L86)
**Commento:**
```text
// Task con scadenza esattamente domani
```
**Codice di riferimento:**
```php
        $task = $project->tasks()->create([
            'title'     => 'Task in scadenza',
            'priority'  => 'high',
            'column_id' => $column->id,
```

### [Riga 102 - EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php#L102)
**Commento:**
```text
// TC-034  Notifica email al cambio di colonna/stato di un task assegnato
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskMoveSendsEmailNotificationToAssignee(): void
    {
        Notification::fake();
```

### [Riga 139 - EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php#L139)
**Commento:**
```text
// Il PM sposta il task (diverso dall'assegnatario)
```
**Codice di riferimento:**
```php
        $this->actingAs($pm)
            ->patchJson(route('tasks.move', [$project, $task]), [
                'column_id' => $col2->id,
            ]);
```


---

## tests/Feature/KanbanColumnsTest.php

Percorso file: [ tests/Feature/KanbanColumnsTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php)

### [Riga 13 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L13)
**Commento:**
```text
/**
 * TC-019 – TC-021  Gestione colonne kanban (REQ-006)
 */
```
**Codice di riferimento:**
```php
class KanbanColumnsTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 24 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L24)
**Commento:**
```text
// ColumnType necessari sia per le colonne di default (TC-019) sia per
```
**Codice di riferimento:**
```php
        // aggiungere/eliminare colonne (TC-020 / TC-021)
        $columnTypes = [
            ['name' => 'Backlog',      'is_done' => false, 'position' => 1],
            ['name' => 'Da fare',      'is_done' => false, 'position' => 2],
```

### [Riga 25 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L25)
**Commento:**
```text
// aggiungere/eliminare colonne (TC-020 / TC-021)
```
**Codice di riferimento:**
```php
        $columnTypes = [
            ['name' => 'Backlog',      'is_done' => false, 'position' => 1],
            ['name' => 'Da fare',      'is_done' => false, 'position' => 2],
            ['name' => 'In corso',     'is_done' => false, 'position' => 3],
```

### [Riga 58 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L58)
**Commento:**
```text
// TC-019  Selezione tipologie colonne di default
```
**Codice di riferimento:**
```php
    //
    // Verifica che all'apertura del modale di creazione colonna siano presenti
    // le 6 tipologie di default nell'ordine corretto (per campo position).
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 60 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L60)
**Commento:**
```text
// Verifica che all'apertura del modale di creazione colonna siano presenti
```
**Codice di riferimento:**
```php
    // le 6 tipologie di default nell'ordine corretto (per campo position).
    // ─────────────────────────────────────────────────────────────────────────
    public function testDefaultColumnTypesAvailableForSelection(): void
    {
```

### [Riga 61 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L61)
**Commento:**
```text
// le 6 tipologie di default nell'ordine corretto (per campo position).
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDefaultColumnTypesAvailableForSelection(): void
    {
        // Prerequisito: admin crea il progetto (ProjectController::store lo
```

### [Riga 65 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L65)
**Commento:**
```text
// Prerequisito: admin crea il progetto (ProjectController::store lo
```
**Codice di riferimento:**
```php
        // attacca automaticamente come PM del progetto)
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('projects.store'), [
```

### [Riga 66 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L66)
**Commento:**
```text
// attacca automaticamente come PM del progetto)
```
**Codice di riferimento:**
```php
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Beta',
```

### [Riga 77 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L77)
**Commento:**
```text
// Apertura del modale "crea colonna": l'endpoint restituisce i tipi disponibili
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($admin)
            ->getJson(route('projects.columns.available-types', $project));

        $response->assertOk();
```

### [Riga 87 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L87)
**Commento:**
```text
// Tutti e 6 i tipi di default devono essere presenti
```
**Codice di riferimento:**
```php
        foreach ($defaultNames as $name) {
            $this->assertContains($name, $returnedNames,
                "La tipologia di default '{$name}' non è presente nell'elenco.");
        }
```

### [Riga 93 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L93)
**Commento:**
```text
// I 6 tipi di default devono comparire nell'ordine corretto (per position)
```
**Codice di riferimento:**
```php
        $orderedDefaults = collect($response->json())
            ->filter(fn ($t) => in_array($t['name'], $defaultNames))
            ->pluck('name')
            ->values()
```

### [Riga 105 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L105)
**Commento:**
```text
// TC-020  Il PM aggiunge, riordina ed elimina una colonna
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmAddsReordersAndDeletesColumn(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 123 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L123)
**Commento:**
```text
// 1. Aggiunta colonna "In attesa"
```
**Codice di riferimento:**
```php
        $addResponse = $this->actingAs($pm)
            ->postJson(route('projects.columns.store', $project), [
                'column_type_id' => (string) $inAttesaType->id,
            ]);
```

### [Riga 133 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L133)
**Commento:**
```text
// 2. Riordino: "Da fare" → posizione 0, "In attesa" → posizione 1
```
**Codice di riferimento:**
```php
        $reorderResponse = $this->actingAs($pm)
            ->patchJson(route('projects.columns.reorder', $project), [
                'columns' => [$col1->id, $newColumn->id],
            ]);
```

### [Riga 143 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L143)
**Commento:**
```text
// 3. Eliminazione colonna "In attesa" (vuota)
```
**Codice di riferimento:**
```php
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('projects.columns.destroy', [$project, $newColumn]));

        $deleteResponse->assertOk();
```

### [Riga 152 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L152)
**Commento:**
```text
// TC-021  Eliminazione colonna con task: conferma ed eliminazione in cascata
```
**Codice di riferimento:**
```php
    //
    // Verifica che l'eliminazione di una colonna contenente task riesca
    // (il PM ha confermato l'operazione) e che i task presenti nella colonna
    // vengano eliminati in cascata insieme ad essa.
```

### [Riga 154 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L154)
**Commento:**
```text
// Verifica che l'eliminazione di una colonna contenente task riesca
```
**Codice di riferimento:**
```php
    // (il PM ha confermato l'operazione) e che i task presenti nella colonna
    // vengano eliminati in cascata insieme ad essa.
    //
    // NOTA: La richiesta di conferma tramite modale/dialog è una funzionalità
```

### [Riga 155 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L155)
**Commento:**
```text
// (il PM ha confermato l'operazione) e che i task presenti nella colonna
```
**Codice di riferimento:**
```php
    // vengano eliminati in cascata insieme ad essa.
    //
    // NOTA: La richiesta di conferma tramite modale/dialog è una funzionalità
    //       dell'interfaccia utente e richiede verifica visiva manuale nel browser.
```

### [Riga 156 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L156)
**Commento:**
```text
// vengano eliminati in cascata insieme ad essa.
```
**Codice di riferimento:**
```php
    //
    // NOTA: La richiesta di conferma tramite modale/dialog è una funzionalità
    //       dell'interfaccia utente e richiede verifica visiva manuale nel browser.
    //       Il test verifica il comportamento lato server: DELETE va a buon fine
```

### [Riga 158 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L158)
**Commento:**
```text
// NOTA: La richiesta di conferma tramite modale/dialog è una funzionalità
```
**Codice di riferimento:**
```php
    //       dell'interfaccia utente e richiede verifica visiva manuale nel browser.
    //       Il test verifica il comportamento lato server: DELETE va a buon fine
    //       e la colonna con i suoi task è rimossa dal database.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 159 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L159)
**Commento:**
```text
//       dell'interfaccia utente e richiede verifica visiva manuale nel browser.
```
**Codice di riferimento:**
```php
    //       Il test verifica il comportamento lato server: DELETE va a buon fine
    //       e la colonna con i suoi task è rimossa dal database.
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeleteColumnWithTasksAlsoDeletesTasks(): void
```

### [Riga 160 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L160)
**Commento:**
```text
//       Il test verifica il comportamento lato server: DELETE va a buon fine
```
**Codice di riferimento:**
```php
    //       e la colonna con i suoi task è rimossa dal database.
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeleteColumnWithTasksAlsoDeletesTasks(): void
    {
```

### [Riga 161 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L161)
**Commento:**
```text
//       e la colonna con i suoi task è rimossa dal database.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeleteColumnWithTasksAlsoDeletesTasks(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 178 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L178)
**Commento:**
```text
// Due task nella colonna da eliminare
```
**Codice di riferimento:**
```php
        $task1 = $project->tasks()->create([
            'title'     => 'Task A',
            'priority'  => 'medium',
            'column_id' => $column->id,
```

### [Riga 192 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L192)
**Commento:**
```text
// Il PM elimina la colonna (dopo aver confermato il dialog nel browser)
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($pm)
            ->deleteJson(route('projects.columns.destroy', [$project, $column]));

        $response->assertOk();
```

### [Riga 198 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L198)
**Commento:**
```text
// La colonna deve essere rimossa dal database
```
**Codice di riferimento:**
```php
        $this->assertDatabaseMissing('project_columns', ['id' => $column->id]);

        // I task appartenenti alla colonna devono essere eliminati in cascata
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
```

### [Riga 201 - KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php#L201)
**Commento:**
```text
// I task appartenenti alla colonna devono essere eliminati in cascata
```
**Codice di riferimento:**
```php
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    }
}
```


---

## tests/Feature/LabelsTest.php

Percorso file: [ tests/Feature/LabelsTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php)

### [Riga 11 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L11)
**Commento:**
```text
/**
 * TC-026 – TC-027  Gestione etichette (REQ-009 / REQ-NF-001)
 */
```
**Codice di riferimento:**
```php
class LabelsTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 26 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L26)
**Commento:**
```text
/** Helper: progetto con etichette di sistema già create */
```
**Codice di riferimento:**
```php
    private function makeProjectWithSystemLabels(User $pm): Project
    {
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
```

### [Riga 32 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L32)
**Commento:**
```text
// Stesso set di etichette usato da ProjectController::systemLabels()
```
**Codice di riferimento:**
```php
        $project->labels()->createMany([
            ['name' => 'DIAGRAMMA',      'color' => '#6366f1', 'is_system' => true],
            ['name' => 'ANALISI',        'color' => '#8b5cf6', 'is_system' => true],
            ['name' => 'BACKEND',        'color' => '#3b82f6', 'is_system' => true],
```

### [Riga 46 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L46)
**Commento:**
```text
// TC-026  Etichette di sistema presenti e non modificabili/eliminabili
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testSystemLabelsArePresentAndProtected(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 53 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L53)
**Commento:**
```text
// Tutte le etichette di default devono essere presenti
```
**Codice di riferimento:**
```php
        $expected = ['DIAGRAMMA', 'ANALISI', 'BACKEND', 'FRONTEND', 'DOCUMENTAZIONE', 'TEST'];
        $names    = $project->labels()->where('is_system', true)->pluck('name')->toArray();

        foreach ($expected as $name) {
```

### [Riga 63 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L63)
**Commento:**
```text
// Tentativo di eliminazione di un'etichetta di sistema → 422
```
**Codice di riferimento:**
```php
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('projects.labels.destroy', [$project, $backend]));

        $deleteResponse->assertStatus(422);
```

### [Riga 70 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L70)
**Commento:**
```text
// Tentativo di modifica di un'etichetta di sistema → 422
```
**Codice di riferimento:**
```php
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('projects.labels.update', [$project, $backend]), [
                'color' => '#000000',
            ]);
```

### [Riga 78 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L78)
**Commento:**
```text
// Il colore originale deve essere rimasto invariato
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('labels', [
            'id'    => $backend->id,
            'color' => '#3b82f6',
        ]);
```

### [Riga 86 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L86)
**Commento:**
```text
// TC-027  Creazione etichetta personalizzata con validazione colore hex
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testCustomLabelCreationWithColorValidation(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 93 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L93)
**Commento:**
```text
// Caso A – colore esadecimale valido
```
**Codice di riferimento:**
```php
        $validResponse = $this->actingAs($pm)
            ->postJson(route('projects.labels.store', $project), [
                'name'  => 'Urgente',
                'color' => '#FF0000',
```

### [Riga 103 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L103)
**Commento:**
```text
// il controller salva in uppercase
```
**Codice di riferimento:**
```php
            'color'      => '#FF0000',
            'is_system'  => false,
        ]);

```

### [Riga 108 - LabelsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/LabelsTest.php#L108)
**Commento:**
```text
// Caso B – colore non esadecimale → 422
```
**Codice di riferimento:**
```php
        $invalidResponse = $this->actingAs($pm)
            ->postJson(route('projects.labels.store', $project), [
                'name'  => 'Colore invalido',
                'color' => 'rosso',
```


---

## tests/Feature/MemberManagementAndRolePermissionsTest.php

Percorso file: [ tests/Feature/MemberManagementAndRolePermissionsTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php)

### [Riga 11 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L11)
**Commento:**
```text
/**
 * TC-015 – TC-018  Gestione membri e permessi di ruolo (REQ-004 / REQ-005)
 */
```
**Codice di riferimento:**
```php
class MemberManagementAndRolePermissionsTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 18 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L18)
**Commento:**
```text
/** Helper: utente verificato con ruolo di sistema 'user' */
```
**Codice di riferimento:**
```php
    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role' => 'user',
```

### [Riga 28 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L28)
**Commento:**
```text
// TC-015  Il Project Manager invita un nuovo membro con ruolo Developer
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmInvitesNewMember(): void
    {
        $pm = $this->makeUser();
```

### [Riga 46 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L46)
**Commento:**
```text
// Il nuovo membro esiste nella pivot con ruolo developer
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $newUser->id,
            'role' => 'developer',
```

### [Riga 55 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L55)
**Commento:**
```text
// TC-016  Il PM modifica il ruolo di un membro e poi lo rimuove
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testPmUpdatesRoleAndRemovesMember(): void
    {
        $this->withoutExceptionHandling();
```

### [Riga 68 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L68)
**Commento:**
```text
// Modifica ruolo da developer a viewer
```
**Codice di riferimento:**
```php
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('projects.members.update', [$project, $dev]), [
                'role' => 'viewer',
            ]);
```

### [Riga 86 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L86)
**Commento:**
```text
// Il membro deve risultare soft-deleted (deleted_at non null) oppure
```
**Codice di riferimento:**
```php
        // non presente nei membri attivi
        $this->assertSame(
            0,
            $project->members()->where('users.id', $dev->id)->count()
```

### [Riga 87 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L87)
**Commento:**
```text
// non presente nei membri attivi
```
**Codice di riferimento:**
```php
        $this->assertSame(
            0,
            $project->members()->where('users.id', $dev->id)->count()
        );
```

### [Riga 95 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L95)
**Commento:**
```text
// TC-017  Il Developer può operare sui task ma non gestire colonne/membri
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testDeveloperCannotManageColumns(): void
    {
        $pm = $this->makeUser();
```

### [Riga 106 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L106)
**Commento:**
```text
// Il developer NON può aggiungere una colonna (solo pm)
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($dev)
            ->postJson(route('projects.columns.store', $project), [
                'column_type_id' => 1,
            ]);
```

### [Riga 125 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L125)
**Commento:**
```text
// Il developer NON può invitare un membro
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($dev)
            ->postJson(route('projects.members.store', $project), [
                'user_id' => $newUser->id,
                'role' => 'viewer',
```

### [Riga 136 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L136)
**Commento:**
```text
// TC-018  Il Viewer ha accesso in sola lettura (scrittura → 403)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testViewerTaskWriteReturns403(): void
    {
        $pm = $this->makeUser();
```

### [Riga 154 - MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php#L154)
**Commento:**
```text
// Tentativo di creazione task come Viewer
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($viewer)
            ->postJson(route('projects.tasks.store', $project), [
                'title' => 'Task illecita',
                'priority' => 'medium',
```


---

## tests/Feature/ProjectCRUDAndPermissionsTest.php

Percorso file: [ tests/Feature/ProjectCRUDAndPermissionsTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php)

### [Riga 11 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L11)
**Commento:**
```text
/**
 * TC-010 – TC-014  Gestione progetti (REQ-003 / REQ-NF-001 / REQ-NF-005)
 */
```
**Codice di riferimento:**
```php
class ProjectCRUDAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 18 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L18)
**Commento:**
```text
/** Helper: crea un utente admin verificato */
```
**Codice di riferimento:**
```php
    private function makeAdmin(): User
    {
        return User::factory()->create([
            'sys_role'          => 'admin',
```

### [Riga 27 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L27)
**Commento:**
```text
/** Helper: crea un utente standard verificato */
```
**Codice di riferimento:**
```php
    private function makeUser(): User
    {
        return User::factory()->create([
            'sys_role'          => 'user',
```

### [Riga 37 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L37)
**Commento:**
```text
// TC-010  Creazione progetto con dati validi
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectCreationWithValidData(): void
    {
        $admin = $this->makeAdmin();
```

### [Riga 51 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L51)
**Commento:**
```text
// Progetto esiste nel DB
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('projects', [
            'name'        => 'Progetto Alfa',
            'archived_at' => null,
        ]);
```

### [Riga 57 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L57)
**Commento:**
```text
// Il creatore (admin) risulta membro con ruolo pm
```
**Codice di riferimento:**
```php
        $project = Project::where('name', 'Progetto Alfa')->first();
        $this->assertNotNull($project);

        $this->assertDatabaseHas('project_user', [
```

### [Riga 67 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L67)
**Commento:**
```text
// Etichette di sistema create (BACKEND, FRONTEND, ecc.)
```
**Codice di riferimento:**
```php
        $this->assertSame(6, $project->labels()->where('is_system', true)->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 72 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L72)
**Commento:**
```text
// TC-011  Creazione progetto con titolo duplicato
```
**Codice di riferimento:**
```php
    //         (il titolo è univoco nella tabella projects)
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectCreationWithDuplicateName(): void
    {
```

### [Riga 73 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L73)
**Commento:**
```text
//         (il titolo è univoco nella tabella projects)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectCreationWithDuplicateName(): void
    {
        $admin = $this->makeAdmin();
```

### [Riga 79 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L79)
**Commento:**
```text
// Primo progetto
```
**Codice di riferimento:**
```php
        $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Alfa',
            'priority' => 'medium',
        ]);
```

### [Riga 85 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L85)
**Commento:**
```text
// Secondo tentativo con lo stesso nome
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'name'     => 'Progetto Alfa',
            'priority' => 'low',
        ]);
```

### [Riga 91 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L91)
**Commento:**
```text
// La validazione (unique su `name`) deve bloccare la creazione
```
**Codice di riferimento:**
```php
        $response->assertSessionHasErrors('name');

        // Nel DB esiste un solo progetto con quel nome
        $this->assertSame(1, Project::where('name', 'Progetto Alfa')->count());
```

### [Riga 94 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L94)
**Commento:**
```text
// Nel DB esiste un solo progetto con quel nome
```
**Codice di riferimento:**
```php
        $this->assertSame(1, Project::where('name', 'Progetto Alfa')->count());
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 99 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L99)
**Commento:**
```text
// TC-012  Archiviazione progetto (soft delete / cambio archived_at)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testProjectArchiving(): void
    {
        $admin = $this->makeAdmin();
```

### [Riga 112 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L112)
**Commento:**
```text
// Lo stato nel DB è "archiviato" (archived_at non null)
```
**Codice di riferimento:**
```php
        $this->assertNotNull($project->fresh()->archived_at);
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 117 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L117)
**Commento:**
```text
// TC-013  Modifica di un progetto archiviato non consentita lato server
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testArchivedProjectUpdateIsBlocked(): void
    {
        $admin = $this->makeAdmin();
```

### [Riga 129 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L129)
**Commento:**
```text
// Il middleware project.access:pm permette l'accesso fisico alla rotta;
```
**Codice di riferimento:**
```php
        // ma il controller deve respingere la modifica su un progetto archiviato.
        // Verifichiamo che i dati NON vengano alterati nel DB.
        $this->actingAs($admin)->patch(route('projects.update', $project), [
            'name'     => 'Titolo Modificato',
```

### [Riga 130 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L130)
**Commento:**
```text
// ma il controller deve respingere la modifica su un progetto archiviato.
```
**Codice di riferimento:**
```php
        // Verifichiamo che i dati NON vengano alterati nel DB.
        $this->actingAs($admin)->patch(route('projects.update', $project), [
            'name'     => 'Titolo Modificato',
            'priority' => 'low',
```

### [Riga 131 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L131)
**Commento:**
```text
// Verifichiamo che i dati NON vengano alterati nel DB.
```
**Codice di riferimento:**
```php
        $this->actingAs($admin)->patch(route('projects.update', $project), [
            'name'     => 'Titolo Modificato',
            'priority' => 'low',
        ]);
```

### [Riga 137 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L137)
**Commento:**
```text
// Il nome originale è rimasto invariato
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('projects', ['name' => 'Progetto Alfa']);
        $this->assertDatabaseMissing('projects', ['name' => 'Titolo Modificato']);
    }

```

### [Riga 143 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L143)
**Commento:**
```text
// TC-014  Un Viewer non può eliminare o modificare un progetto (403)
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testViewerCannotDeleteProject(): void
    {
        $pm     = $this->makeUser();
```

### [Riga 154 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L154)
**Commento:**
```text
// Tentativo di eliminazione da parte del Viewer
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($viewer)
            ->delete(route('projects.destroy', $project));

        $response->assertStatus(403);
```

### [Riga 160 - ProjectCRUDAndPermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ProjectCRUDAndPermissionsTest.php#L160)
**Commento:**
```text
// Il progetto esiste ancora
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
```


---

## tests/Feature/SubtaskTest.php

Percorso file: [ tests/Feature/SubtaskTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php)

### [Riga 12 - SubtaskTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php#L12)
**Commento:**
```text
/**
 * TC-028  Gestione sotto-attività (checklist) (REQ-010)
 */
```
**Codice di riferimento:**
```php
class SubtaskTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 28 - SubtaskTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php#L28)
**Commento:**
```text
// TC-028  Aggiunta, completamento e conteggio delle sotto-attività
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testSubtaskAddCompleteAndCount(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 51 - SubtaskTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php#L51)
**Commento:**
```text
// Aggiunta di due sotto-attività con titoli validi
```
**Codice di riferimento:**
```php
        $addResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
```

### [Riga 66 - SubtaskTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php#L66)
**Commento:**
```text
// Tentativo di aggiungere una sotto-attività con titolo vuoto → 422
```
**Codice di riferimento:**
```php
        $invalidResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => $task->title,
                'priority'     => $task->priority,
```

### [Riga 79 - SubtaskTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/SubtaskTest.php#L79)
**Commento:**
```text
// Contrassegna "Creare form" come completata mantenendo l'altra invariata
```
**Codice di riferimento:**
```php
        $updatedSubtasks = $task->fresh()->subtasks->map(fn ($s) => [
            'title' => $s->title,
            'done'  => $s->title === 'Creare form',
        ])->values()->toArray();
```


---

## tests/Feature/TaskCRUDAndMoveTest.php

Percorso file: [ tests/Feature/TaskCRUDAndMoveTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php)

### [Riga 12 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L12)
**Commento:**
```text
/**
 * TC-022 – TC-025  Gestione task CRUD e spostamento (REQ-007 / REQ-008)
 */
```
**Codice di riferimento:**
```php
class TaskCRUDAndMoveTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 27 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L27)
**Commento:**
```text
/** Helper: progetto con PM e colonna "Da fare" */
```
**Codice di riferimento:**
```php
    private function makeProjectWithColumn(User $pm): array
    {
        $project = Project::create(['name' => 'Progetto Alfa', 'priority' => 'medium']);
        $project->members()->attach($pm->id, ['role' => 'pm']);
```

### [Riga 44 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L44)
**Commento:**
```text
// TC-022  Creazione task con creatore come assegnatario di default
```
**Codice di riferimento:**
```php
    //
    // Il frontend invia sempre almeno un assignee_id (default: il creatore).
    // Il test verifica che il task venga salvato con il creatore come assegnatario.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 46 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L46)
**Commento:**
```text
// Il frontend invia sempre almeno un assignee_id (default: il creatore).
```
**Codice di riferimento:**
```php
    // Il test verifica che il task venga salvato con il creatore come assegnatario.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithCreatorAsDefaultAssignee(): void
    {
```

### [Riga 47 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L47)
**Commento:**
```text
// Il test verifica che il task venga salvato con il creatore come assegnatario.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithCreatorAsDefaultAssignee(): void
    {
        $pm = $this->makeUser();
```

### [Riga 59 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L59)
**Commento:**
```text
// creatore come assegnatario di default
```
**Codice di riferimento:**
```php
            ]);

        $response->assertStatus(201);

```

### [Riga 68 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L68)
**Commento:**
```text
// L'assegnatario registrato nella pivot è il creatore
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $pm->id,
        ]);
```

### [Riga 76 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L76)
**Commento:**
```text
// TC-023  Creazione task con assegnatario non membro del progetto
```
**Codice di riferimento:**
```php
    //
    // StoreTaskRequest verifica che ogni assignee_id sia presente nella pivot
    // project_user con il project_id corretto e senza soft-delete.
    // Un utente non membro deve causare un errore 422.
```

### [Riga 78 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L78)
**Commento:**
```text
// StoreTaskRequest verifica che ogni assignee_id sia presente nella pivot
```
**Codice di riferimento:**
```php
    // project_user con il project_id corretto e senza soft-delete.
    // Un utente non membro deve causare un errore 422.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithNonMemberAssigneeIsRejected(): void
```

### [Riga 79 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L79)
**Commento:**
```text
// project_user con il project_id corretto e senza soft-delete.
```
**Codice di riferimento:**
```php
    // Un utente non membro deve causare un errore 422.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithNonMemberAssigneeIsRejected(): void
    {
```

### [Riga 80 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L80)
**Commento:**
```text
// Un utente non membro deve causare un errore 422.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskCreationWithNonMemberAssigneeIsRejected(): void
    {
        $pm        = $this->makeUser();
```

### [Riga 97 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L97)
**Commento:**
```text
// L'assegnatario non è membro del progetto: la creazione deve essere rifiutata
```
**Codice di riferimento:**
```php
        $response->assertStatus(422);
        $this->assertDatabaseMissing('tasks', ['title' => 'Task con assegnatario esterno']);
    }

```

### [Riga 103 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L103)
**Commento:**
```text
// TC-024  Modifica ed eliminazione task
```
**Codice di riferimento:**
```php
    //
    // NOTA: La conferma prima dell'eliminazione è un comportamento UI (modal);
    //       il test verifica che la richiesta DELETE rimuova il task dal DB.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 105 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L105)
**Commento:**
```text
// NOTA: La conferma prima dell'eliminazione è un comportamento UI (modal);
```
**Codice di riferimento:**
```php
    //       il test verifica che la richiesta DELETE rimuova il task dal DB.
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskUpdateAndDeletion(): void
    {
```

### [Riga 106 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L106)
**Commento:**
```text
//       il test verifica che la richiesta DELETE rimuova il task dal DB.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskUpdateAndDeletion(): void
    {
        $pm = $this->makeUser();
```

### [Riga 121 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L121)
**Commento:**
```text
// Modifica descrizione e priorità
```
**Codice di riferimento:**
```php
        $updateResponse = $this->actingAs($pm)
            ->patchJson(route('tasks.update', [$project, $task]), [
                'title'        => 'Implementare login',
                'description'  => 'Form con validazione',
```

### [Riga 138 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L138)
**Commento:**
```text
// Eliminazione task (solo PM)
```
**Codice di riferimento:**
```php
        $deleteResponse = $this->actingAs($pm)
            ->deleteJson(route('tasks.destroy', [$project, $task]));

        $deleteResponse->assertOk();
```

### [Riga 147 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L147)
**Commento:**
```text
// TC-025  Spostamento task tra colonne dall'interfaccia
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testTaskMoveBetweenColumns(): void
    {
        $pm = $this->makeUser();
```

### [Riga 176 - TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php#L176)
**Commento:**
```text
// Nel DB il column_id è aggiornato alla colonna di destinazione
```
**Codice di riferimento:**
```php
        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'column_id' => $toCol->id,
        ]);
```


---

## tests/Feature/ValidationAndSecurityTest.php

Percorso file: [ tests/Feature/ValidationAndSecurityTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php)

### [Riga 11 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L11)
**Commento:**
```text
/**
 * TC-038 – TC-042  Validazione, errori, feedback UI e sicurezza
 *                  (REQ-NF-001 / REQ-NF-002 / REQ-NF-003 / REQ-NF-005)
 */
```
**Codice di riferimento:**
```php
class ValidationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

```

### [Riga 43 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L43)
**Commento:**
```text
// POST senza il campo obbligatorio 'name'
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'description' => 'Descrizione prova',
            'priority'    => 'high',
            // 'name' mancante
```

### [Riga 50 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L50)
**Commento:**
```text
// La validazione lato server rifiuta la richiesta
```
**Codice di riferimento:**
```php
        $response->assertSessionHasErrors('name');

        // I valori già inseriti sono conservati nella sessione (old input)
        $response->assertSessionHasInput('description');
```

### [Riga 53 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L53)
**Commento:**
```text
// I valori già inseriti sono conservati nella sessione (old input)
```
**Codice di riferimento:**
```php
        $response->assertSessionHasInput('description');
        $response->assertSessionHasInput('priority');

        // Nessun progetto parziale salvato nel DB
```

### [Riga 57 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L57)
**Commento:**
```text
// Nessun progetto parziale salvato nel DB
```
**Codice di riferimento:**
```php
        $this->assertDatabaseCount('projects', 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 62 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L62)
**Commento:**
```text
// TC-039  Pagine di errore personalizzate HTTP 404 e 403
```
**Codice di riferimento:**
```php
    //
    // NOTA: L'aspetto grafico (branding, testi custom) richiede verifica visiva.
    //       Il test verifica solo i codici di stato HTTP corretti.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 65 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L65)
**Commento:**
```text
//       Il test verifica solo i codici di stato HTTP corretti.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testCustom404PageReturned(): void
    {
        $user = $this->makeUser();
```

### [Riga 80 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L80)
**Commento:**
```text
// Utente con ruolo 'user' tenta di accedere alla sezione admin
```
**Codice di riferimento:**
```php
        $response = $this->actingAs($user)->get(route('admin.users'));

        $response->assertStatus(403);
    }
```

### [Riga 87 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L87)
**Commento:**
```text
// TC-040  Feedback visivo: flash message di successo dopo le azioni
```
**Codice di riferimento:**
```php
    //
    // NOTA: Toast e modal UI richiedono verifica visiva nel browser.
    //       Il test verifica che la sessione contenga i messaggi flash attesi.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 89 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L89)
**Commento:**
```text
// NOTA: Toast e modal UI richiedono verifica visiva nel browser.
```
**Codice di riferimento:**
```php
    //       Il test verifica che la sessione contenga i messaggi flash attesi.
    // ─────────────────────────────────────────────────────────────────────────
    public function testSuccessFlashMessageAfterProjectCreation(): void
    {
```

### [Riga 90 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L90)
**Commento:**
```text
//       Il test verifica che la sessione contenga i messaggi flash attesi.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testSuccessFlashMessageAfterProjectCreation(): void
    {
        $admin = $this->makeAdmin();
```

### [Riga 120 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L120)
**Commento:**
```text
// NOTA: VerifyCsrfToken bypassa la verifica in ambiente PHPUnit
```
**Codice di riferimento:**
```php
    //       (runningUnitTests() → true). La protezione a runtime è garantita
    //       dalla presenza del middleware nel gruppo 'web'; il comportamento
    //       HTTP 419 a token mancante/errato si verifica solo via browser.
    //       Il test verifica che il middleware CSRF sia registrato nella route.
```

### [Riga 121 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L121)
**Commento:**
```text
//       (runningUnitTests() → true). La protezione a runtime è garantita
```
**Codice di riferimento:**
```php
    //       dalla presenza del middleware nel gruppo 'web'; il comportamento
    //       HTTP 419 a token mancante/errato si verifica solo via browser.
    //       Il test verifica che il middleware CSRF sia registrato nella route.
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 122 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L122)
**Commento:**
```text
//       dalla presenza del middleware nel gruppo 'web'; il comportamento
```
**Codice di riferimento:**
```php
    //       HTTP 419 a token mancante/errato si verifica solo via browser.
    //       Il test verifica che il middleware CSRF sia registrato nella route.
    // ─────────────────────────────────────────────────────────────────────────
    public function testCsrfMiddlewareIsRegisteredForWebRoutes(): void
```

### [Riga 123 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L123)
**Commento:**
```text
//       HTTP 419 a token mancante/errato si verifica solo via browser.
```
**Codice di riferimento:**
```php
    //       Il test verifica che il middleware CSRF sia registrato nella route.
    // ─────────────────────────────────────────────────────────────────────────
    public function testCsrfMiddlewareIsRegisteredForWebRoutes(): void
    {
```

### [Riga 124 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L124)
**Commento:**
```text
//       Il test verifica che il middleware CSRF sia registrato nella route.
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testCsrfMiddlewareIsRegisteredForWebRoutes(): void
    {
        $projectStoreRoute = app('router')->getRoutes()->getByName('projects.store');
```

### [Riga 131 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L131)
**Commento:**
```text
// Il gruppo 'web' (che contiene VerifyCsrfToken) deve essere nella middleware list
```
**Codice di riferimento:**
```php
        $routeMiddleware = $projectStoreRoute->gatherMiddleware();
        $this->assertContains('web', $routeMiddleware,
            'Il middleware "web" (e quindi CSRF) non è applicato alla route projects.store.');
    }
```

### [Riga 138 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L138)
**Commento:**
```text
// TC-042  Protezione XSS: Blade esegue l'escape dell'output con {{ }}
```
**Codice di riferimento:**
```php
    //
    // NOTA: La verifica che lo script NON venga eseguito nel browser (nessun
    //       popup alert) richiede verifica visiva. Il test verifica che il
    //       payload sia salvato come testo letterale e che la pagina si carichi
```

### [Riga 140 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L140)
**Commento:**
```text
// NOTA: La verifica che lo script NON venga eseguito nel browser (nessun
```
**Codice di riferimento:**
```php
    //       popup alert) richiede verifica visiva. Il test verifica che il
    //       payload sia salvato come testo letterale e che la pagina si carichi
    //       senza errori (Blade non esegue il codice lato server).
    // ─────────────────────────────────────────────────────────────────────────
```

### [Riga 141 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L141)
**Commento:**
```text
//       popup alert) richiede verifica visiva. Il test verifica che il
```
**Codice di riferimento:**
```php
    //       payload sia salvato come testo letterale e che la pagina si carichi
    //       senza errori (Blade non esegue il codice lato server).
    // ─────────────────────────────────────────────────────────────────────────
    public function testXssPayloadInTaskTitleIsStoredLiterally(): void
```

### [Riga 142 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L142)
**Commento:**
```text
//       payload sia salvato come testo letterale e che la pagina si carichi
```
**Codice di riferimento:**
```php
    //       senza errori (Blade non esegue il codice lato server).
    // ─────────────────────────────────────────────────────────────────────────
    public function testXssPayloadInTaskTitleIsStoredLiterally(): void
    {
```

### [Riga 143 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L143)
**Commento:**
```text
//       senza errori (Blade non esegue il codice lato server).
```
**Codice di riferimento:**
```php
    // ─────────────────────────────────────────────────────────────────────────
    public function testXssPayloadInTaskTitleIsStoredLiterally(): void
    {
        $pm      = $this->makeUser();
```

### [Riga 170 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L170)
**Commento:**
```text
// Il titolo è salvato nel DB come testo letterale, non eseguito
```
**Codice di riferimento:**
```php
        $task = $project->tasks()->where('title', $xssPayload)->first();
        $this->assertNotNull($task);
        $this->assertSame($xssPayload, $task->title);

```

### [Riga 175 - ValidationAndSecurityTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ValidationAndSecurityTest.php#L175)
**Commento:**
```text
// La pagina del progetto si carica senza errori (Blade effettua l'escape con {{ }})
```
**Codice di riferimento:**
```php
        $this->actingAs($pm)
            ->get(route('projects.show', $project))
            ->assertOk();
    }
```


---

## tests/Unit/ExampleTest.php

Percorso file: [ tests/Unit/ExampleTest.php ](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Unit/ExampleTest.php)

### [Riga 9 - ExampleTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Unit/ExampleTest.php#L9)
**Commento:**
```text
/**
     * A basic test example.
     */
```
**Codice di riferimento:**
```php
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
```


---

