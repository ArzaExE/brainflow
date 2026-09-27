# Spiegazione delle Parti Significative di [show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php)

Questo documento estrae e analizza nel dettaglio le parti di codice più importanti e significative dal punto di vista architetturale del file [show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php). Poiché molte parti dell'applicazione (come i modali e i metodi helper per gli assegnatari/etichette) sono ridondanti e ripetitive, abbiamo isolato i **pattern chiave** che governano l'intero funzionamento del pannello di controllo del progetto.

---

## 1. Il Bridge di Dati PHP-JavaScript (Data Ingestion)
### Codice Significativo: [L14-L39](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L14-L39)
```html
<script>
    window.brainflowData = {
        project:     {!! json_encode($project) !!},
        columns:     {!! json_encode($columns) !!},
        tasks:       {!! json_encode($tasks) !!},
        members:     {!! json_encode($members) !!},
        labels:      {!! json_encode($labels) !!},
        activity:    {!! json_encode($activity) !!},
        isPm:        {{ $isPm ? 'true' : 'false' }},
        canEdit:     {{ $canEdit ? 'true' : 'false' }},
    };
</script>

<div
    x-data="projectBoard(
        brainflowData.project,
        brainflowData.columns,
        brainflowData.columnTypes,
        brainflowData.tasks,
        brainflowData.members,
        brainflowData.labels,
        brainflowData.activity,
        brainflowData.isPm,
        brainflowData.canEdit
    )"
    style="display:flex;flex-direction:column;height:100vh;overflow:hidden"
>
```

### Spiegazione
* **Come funziona:** Questo blocco fa da ponte tra il backend Laravel (PHP) e il frontend reattivo gestito da Alpine.js. Invece di fare una chiamata API iniziale (fetch) per caricare i dati del progetto al caricamento della pagina, le variabili di database vengono iniettate direttamente nell'oggetto globale `window.brainflowData` codificandole in stringhe JSON mediante `{!! json_encode(...) !!}`.
* **Perché è importante:** Riduce il tempo di caricamento percepito dall'utente (zero latenza di rete iniziale) ed istanzia immediatamente lo stato del componente Alpine passandone i parametri al costruttore [projectBoard](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1214).

---

## 2. Integrazione di Sortable.js con Alpine.js (Drag & Drop delle Task)
### Codice Significativo: [L2314-L2336](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L2314-L2336)
```javascript
initTaskSortable() {
    if (typeof Sortable === 'undefined') return;
    document.querySelectorAll('.column__body:not([data-task-sortable])').forEach(el => {
        el.dataset.taskSortable = '1';
        Sortable.create(el, {
            group:      'tasks',
            animation:  150,
            ghostClass: 'is-dragging',
            disabled:   !this.canEdit,
            onEnd:      (evt) => {
                if (!this.canEdit) return;
                const taskId   = parseInt(evt.item.dataset.taskId);
                const newColId = parseInt(evt.to.dataset.columnId);
                const task = this.tasks.find(t => t.id === taskId);

                if (task && task.column_id !== newColId) {
                    this.moveTask(taskId, newColId);
                }
            },
        });
    });
}
```

### Spiegazione
* **Come funziona:** Questo metodo inizializza la libreria di terze parti `Sortable.js` agganciandola alle colonne del tabellone Kanban. Per evitare doppie inizializzazioni (ad esempio, quando viene aggiunta una colonna in modo dinamico), il codice contrassegna l'elemento DOM impostando l'attributo `data-task-sortable = '1'`.
* **Sincronizzazione DOM-Stato:** Quando l'utente trascina una task in un'altra colonna, `Sortable` sposta fisicamente l'elemento nel DOM. Il callback `onEnd` cattura le informazioni sull'ID della task (`evt.item.dataset.taskId`) e sull'ID della colonna di destinazione (`evt.to.dataset.columnId`), e innesca la sincronizzazione dello stato richiamando il metodo [moveTask](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1824).

---

## 3. Il Pattern "Optimistic Update & Rollback"
### Codice Significativo: [L1824-L1861](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1824-L1861)
```javascript
async moveTask(taskId, newColId) {
    if (!this.canEdit) return;

    const t = this.tasks.find(t => t.id === taskId);
    if (!t || t.column_id === newColId) return;

    // ── 1. Optimistic update: aggiorna subito la UI ──
    const previousColId = t.column_id;
    t.column_id = newColId;

    // ── 2. Chiamata al backend ──
    const response = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}/move`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ column_id: newColId }),
    });

    if (!response.ok) {
        // Rollback: ripristina la colonna precedente
        t.column_id = previousColId;
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type: 'error', message: 'Impossibile spostare la task.' }
        }));
        return;
    }

    const data = await response.json();

    const idx = this.tasks.findIndex(t => t.id === taskId);
    if (idx !== -1) this.tasks[idx] = data.task;

    const newActs = data.activities || (data.activity ? [data.activity] : []);
    newActs.forEach(a => this.activity.unshift(a));
}
```

### Spiegazione
* **Ottimizzazione UX:** Invece di attendere la risposta della chiamata Fetch di rete (che potrebbe richiedere centinaia di millisecondi e far apparire l'interfaccia "lenta"), il client aggiorna **immediatamente** lo stato locale (`t.column_id = newColId`). Alpine.js rileva il cambiamento e ridisegna la task nella nuova colonna istantaneamente.
* **Gestione degli errori (Rollback):** Se la chiamata Fetch fallisce (es. errore del server o disconnessione), il client ripristina la posizione precedente della task (`t.column_id = previousColId`), e notifica l'utente tramite un toast di errore. Lo stesso pattern è utilizzato per l'aggiornamento dei colori delle etichette in [updateLabelColor](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L2222).

---

## 4. Gestione dello Stato di Editing Isolato (Draft Pattern)
### Codice Significativo: [L1576-L1597](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1576-L1597)
```javascript
openDrawer(taskId) {
    const t = this.tasks.find(t => t.id === taskId);
    if (!t) return;

    this.openTaskId = taskId;

    this.taskDraft = {
        title:        t.title,
        description:  t.description || '',
        priority:     t.priority,
        due_date:     this.toDateInput(t.due_date),
        assignee_ids: (t.assignees || []).map(a => a.id),
        label_ids:    (t.labels    || []).map(l => l.id),
        subtasks:     (t.subtasks || []).map(s => ({
            id:    s.id,
            title: s.title,
            done:  !!s.done,
        })),
    };
    this.taskDraftErrors = {};
    this.newDraftSubtaskTitle = '';
}
```

### Spiegazione
* **Perché non modificare direttamente l'oggetto della task:** Se l'utente modificasse i campi direttamente sull'oggetto originale della task in `this.tasks`, i cambiamenti si rifletterebbero immediatamente sulla board principale (es. il titolo della scheda cambierebbe mentre l'utente sta digitando). Se poi l'utente cliccasse su "Annulla" o chiudesse il drawer, lo stato rimarrebbe modificato e incoerente con il database.
* **Il ruolo di `taskDraft`:** Questo metodo esegue una copia profonda (*deep copy*) controllata dei dati della task in un oggetto separato (`this.taskDraft`). L'utente lavora esclusivamente sulla bozza. Solo quando preme "Salva modifiche" ([saveTask](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1718)), i dati vengono validati, inviati al server e, solo dopo il successo, scritti nell'oggetto principale all'interno dell'array `tasks`.

---

## 5. Proprietà Computate Reattive
### Codice Significativo: [L1334-L1343](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1334-L1343)
```javascript
completionPct() {
    if (!this.tasks.length || this.doneColumnId() === null) return 0;
    return Math.round(this.doneTasks() / this.tasks.length * 100);
},

isOverdue(task) {
    if (!task || !task.due_date) return false;
    const doneId = this.doneColumnId();
    return new Date(task.due_date) < new Date() && task.column_id !== doneId;
}
```

### Spiegazione
* **Reattività Dichiarativa:** Alpine.js, ispirandosi a Vue.js, supporta i getter JavaScript come proprietà calcolate reattive.
* **Come funzionano:** `completionPct()` viene invocato automaticamente nei punti del markup HTML in cui è presente la direttiva `x-text="completionPct()"` o `:style="\`width:\${completionPct()}%\`"`. Non è necessario ricalcolare a mano la percentuale ogni volta che una task viene spostata o completata: Alpine traccia le dipendenze (l'array `this.tasks` e il valore di `this.doneColumnId()`) e aggiorna automaticamente i componenti visivi coinvolti ogni volta che queste cambiano.

---

## 6. Flusso CRUD Completo delle Task
Le operazioni fondamentali di creazione (Create), lettura (Read), aggiornamento (Update) ed eliminazione (Delete) sono orchestrate tramite fetch asincrone e gestione coerente dello stato in Alpine.js.

### A. Visualizzazione (Read / Open Drawer)
La visualizzazione in dettaglio di una task avviene attivando il pannello laterale (drawer). La task viene selezionata dall'array reattivo `this.tasks` e viene istanziato lo stato di modifica isolato (`this.taskDraft`).
* **Metodo chiave:** [openDrawer](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1576-L1597) (illustrato nella Sezione 4).

### B. Aggiunta (Create)
L'aggiunta avviene catturando i dati di input da un modale temporaneo, validando i dati obbligatori lato client e inviando un payload JSON al backend.
#### Codice Significativo: [L1627-L1717](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1627-L1717)
```javascript
async submitNewTask() {
    if (!this.canEdit) return;

    // ── 1. Validazione Client-Side ──
    const errors = {};
    if (!this.newTaskTitle.trim()) {
        errors.title = 'Il titolo è obbligatorio.';
    }
    if (this.newTaskAssignees.length === 0) {
        errors.assignees = 'Seleziona almeno un assegnatario.';
    }
    this.newTaskErrors = errors;
    if (Object.keys(errors).length > 0) return;

    // ── 2. Payload ──
    const payload = {
        title:        this.newTaskTitle.trim(),
        description:  this.newTaskDescription.trim() || null,
        priority:     this.newTaskPriority,
        column_id:    this.newTaskColId,
        due_date:     this.newTaskDueDate || null,
        assignee_ids: this.newTaskAssignees,
        label_ids:    this.newTaskLabels,
        subtasks:     this.newTaskSubtasks
            .filter(s => s.title.trim())
            .map(s => ({ title: s.title.trim(), done: !!s.done })),
    };

    // ── 3. Chiamata API ──
    const response = await fetch(`/projects/{{ $project->id }}/tasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        // Gestione errori di validazione backend (422) e mapping del dizionario
        // ...
        return;
    }

    // ── 4. Successo: Aggiunta allo Stato locale e chiusura ──
    const result = await response.json();
    this.tasks.push(result.task); // Inserisce la task creata nella UI

    const newActs = result.activities || (result.activity ? [result.activity] : []);
    newActs.forEach(a => this.activity.unshift(a)); // Aggiorna il feed attività

    this.closeNewTaskModal();
}
```

### C. Modifica (Update)
L'aggiornamento invia il contenuto consolidato dell'oggetto `this.taskDraft` tramite una richiesta HTTP `PATCH`. Se il server conferma il salvataggio, lo stato locale della task viene aggiornato direttamente all'interno dell'array.
#### Codice Significativo: [L1718-L1822](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1718-L1822)
```javascript
async saveTask() {
    if (!this.canEdit || !this.taskDraft || this.savingTask) return;

    // Validazione client-side...
    const errors = {};
    if (!this.taskDraft.title.trim()) { errors.title = 'Il titolo è obbligatorio.'; }
    if (this.taskDraft.assignee_ids.length === 0) { errors.assignees = 'Seleziona almeno un assegnatario.'; }
    this.taskDraftErrors = errors;
    if (Object.keys(errors).length > 0) return;

    this.savingTask = true;

    // Richiesta asincrona
    const response = await fetch(`/projects/{{ $project->id }}/tasks/${this.openTaskId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            title:        this.taskDraft.title.trim(),
            description:  this.taskDraft.description.trim() || null,
            priority:     this.taskDraft.priority,
            due_date:     this.taskDraft.due_date || null,
            assignee_ids: this.taskDraft.assignee_ids,
            label_ids:    this.taskDraft.label_ids,
            subtasks:     this.taskDraft.subtasks
                .filter(s => s.title.trim())
                .map(s => ({ id: s.id, title: s.title.trim(), done: !!s.done })),
        }),
    });

    this.savingTask = false;

    if (!response.ok) {
        // Gestione errori backend...
        return;
    }

    const data = await response.json();
    const task = data.task || data;

    // Trova l'indice della task originale e sovrascrive i nuovi dati
    const idx = this.tasks.findIndex(t => t.id === this.openTaskId);
    if (idx !== -1) this.tasks[idx] = task;

    // Aggiornamento feed attività recente
    const newActs = data.activities || (data.activity ? [data.activity] : []);
    newActs.forEach(a => this.activity.unshift(a));

    this.closeDrawer();
}
```

### D. Eliminazione (Delete)
L'eliminazione prevede l'apertura di un modale di conferma. Quando l'utente conferma, viene effettuata una chiamata asincrona `DELETE`. Al successo, la task viene rimossa dall'array locale mediante un filtro `filter()`.
#### Codice Significativo: [L1871-L1909](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php#L1871-L1909)
```javascript
async confirmDeleteTask() {
    if (!this.deleteTaskTarget) return;

    const taskId = this.deleteTaskTarget.id;
    this.deletingTask     = true;
    this.deleteTaskError  = '';

    const response = await fetch(`/projects/{{ $project->id }}/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    });

    if (!response.ok) {
        this.deleteTaskError = 'Impossibile eliminare la task. Riprova.';
        this.deletingTask = false;
        return;
    }

    // Rimuove la task filtrandola dall'array locale
    this.tasks = this.tasks.filter(t => t.id !== taskId);

    const data = await response.json().catch(() => ({}));
    const newActs = data.activities || (data.activity ? [data.activity] : []);
    newActs.forEach(a => this.activity.unshift(a));

    // Pulisce lo stato e chiude i pannelli
    this.showDeleteTaskConfirm = false;
    this.deleteTaskTarget      = null;
    this.deletingTask          = false;
    this.closeDrawer();
}
```
