# Analisi della Struttura del Progetto & Mappa delle Funzionalità (BrainFlow)

Benvenuto in **BrainFlow**, un'applicazione Kanban sviluppata con **Laravel 12**, **Tailwind CSS** e **Alpine.js**. Questo documento mappa in modo strutturato tutte le funzionalità del backend e del frontend, indicando con precisione in quali file sono implementate, e spiega i concetti tecnici più complessi del progetto.

---

## 🛠️ Architettura Generale (MVC)

L'applicazione segue la classica struttura MVC (Model-View-Controller) di Laravel 12:

- **Modelli (Models)**: Contengono la definizione delle tabelle del database SQLite/MySQL, le relazioni tra le tabelle e la logica di business fondamentale. Si trovano in [app/Models](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models).
- **Controllori (Controllers)**: Gestiscono le richieste HTTP, coordinano la logica aziendale con i modelli e restituiscono le risposte (JSON o viste). Si trovano in [app/Http/Controllers](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers).
- **Viste (Views)**: Sviluppate con Blade, integrate con Alpine.js per la reattività dinamica dell'interfaccia utente. Si trovano in [resources/views](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views).
- **Rotte (Routes)**: Definiscono gli URL esposti dall'applicazione e i relativi controllori. Si trovano in [routes](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes).

---

## 🗺️ Mappa delle Funzionalità e Componenti

### 1. Autenticazione & Sicurezza Utenti
*   **Descrizione**: Registrazione, login, recupero password, verifica email e logout.
*   **File Principali**:
    *   Rotte di Autenticazione: [routes/auth.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/auth.php)
    *   Controllori di Auth: [app/Http/Controllers/Auth](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/Auth) (come [AuthenticatedSessionController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/Auth/AuthenticatedSessionController.php) o [RegisteredUserController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/Auth/RegisteredUserController.php))
    *   Notifiche Personalizzate: [CustomVerifyEmail.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomVerifyEmail.php) e [CustomResetPassword.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/CustomResetPassword.php)
    *   Viste di Login/Register: [resources/views/auth](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/auth)

### 2. Gestione Ruoli & Autorizzazioni (Access Control)
L'applicazione implementa un sistema di autorizzazioni a due livelli:
*   **Ruoli a Livello di Sistema (Globale)**:
    *   *Admin*: Visibilità globale, gestione utenti e progetti.
    *   *User*: Accesso limitato solo ai propri progetti.
    *   Middleware: [EnsureSystemRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureSystemRole.php) (registrato in [app.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/bootstrap/app.php) con l'alias `system.role`).
*   **Ruoli a Livello di Progetto**:
    *   *PM (Project Manager)*: Gestione completa di colonne, membri, task, impostazioni e archiviazione del progetto.
    *   *Developer*: Creazione, modifica e spostamento dei task.
    *   *Viewer*: Accesso in sola lettura.
    *   Middleware: [EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php) (registrato in [app.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/bootstrap/app.php) con l'alias `project.access`).

### 3. Gestione Progetti (CRUD & Archiviazione)
*   **Descrizione**: Creazione, modifica, eliminazione e gestione dello stato attivo/archiviato di un progetto.
*   **File Principali**:
    *   Modello: [Project.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Project.php)
    *   Controllore: [ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php)
    *   Request di Validazione: [StoreProjectRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreProjectRequest.php) e [UpdateProjectRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateProjectRequest.php)
    *   Viste (Dashboard & Bacheca): [index.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/index.blade.php) e [show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php)

### 4. Lavagna Kanban (Colonne e Spostamenti)
*   **Descrizione**: Configurazione delle colonne e gestione della bacheca Kanban, riordinamento delle colonne tramite trascinamento (drag-and-drop).
*   **File Principali**:
    *   Modelli: [ProjectColumn.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ProjectColumn.php) e [ColumnType.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ColumnType.php)
    *   Controllore: [ProjectColumnsController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectColumnsController.php)
    *   Requests: [StoreProjectColumnRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreProjectColumnRequest.php) e [UpdateProjectColumnPositionRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateProjectColumnPositionRequest.php)

### 5. Gestione Task e Sottoattività (Subtasks)
*   **Descrizione**: Creazione, modifica, spostamento (ottimizzato lato frontend con rollback automatico in caso di errore), cancellazione di task e gestione atomica delle sottoattività.
*   **File Principali**:
    *   Modelli: [Task.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Task.php) e [Subtask.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Subtask.php)
    *   Controllore: [TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php)
    *   Requests: [StoreTaskRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreTaskRequest.php) e [UpdateTaskRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateTaskRequest.php)

### 6. Gestione Membri del Progetto
*   **Descrizione**: Associazione di utenti a un progetto con un determinato ruolo di progetto (`pm`, `developer`, `viewer`), con vincoli per impedire l'eliminazione di un membro se ad esso sono ancora assegnati dei task attivi.
*   **File Principali**:
    *   Modello Pivot: [ProjectUser.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ProjectUser.php)
    *   Controllore: [MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php)
    *   Requests: [StoreProjectMemberRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreProjectMemberRequest.php) e [UpdateProjectMemberRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateProjectMemberRequest.php)

### 7. Gestione Etichette (Labels)
*   **Descrizione**: Creazione ed associazione di etichette colorate personalizzate per differenziare visivamente i compiti nei task.
*   **File Principali**:
    *   Modello: [Label.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/Label.php)
    *   Controllore: [LabelController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/LabelController.php)
    *   Requests: [StoreProjectLabelRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreProjectLabelRequest.php) e [UpdateProjectLabelRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateProjectLabelRequest.php)

### 8. Gestione Utenti di Sistema (Pannello Admin)
*   **Descrizione**: Riservata agli amministratori globali per visualizzare, creare, aggiornare ed eliminare gli utenti di sistema (con vincoli di autoprotezione per evitare l'eliminazione o il declassamento dell'ultimo admin).
*   **File Principali**:
    *   Modello: [User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php)
    *   Controllore: [AdminController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php)
    *   Requests: [StoreUserRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/StoreUserRequest.php) e [UpdateUserRequest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Requests/UpdateUserRequest.php)
    *   Vista: [users.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/admin/users.blade.php)

### 9. Notifiche Email Asincrone
*   **Descrizione**: Le notifiche e-mail vengono gestite asincronamente tramite code (`Queue`) implementando l'interfaccia `ShouldQueue` per non bloccare i tempi di risposta del server.
*   **File Principali**:
    *   Notifica Assegnazione Task: [TaskAssignment.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php)
    *   Notifica Spostamento Task: [TaskMove.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskMove.php)
    *   Notifica Invito al Progetto: [InviteMember.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/InviteMember.php)
    *   Notifica Creazione Utente da Admin: [UserRegistrationFromAdmin.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/UserRegistrationFromAdmin.php)

### 10. Scheduler Notifiche Scadenze
*   **Descrizione**: Un comando Artisan personalizzato schedulato per notificare via e-mail gli utenti assegnati a task che scadono l'indomani.
*   **File Principali**:
    *   Comando Artisan: [SendDueSoonNotifications.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php) (registrato in [routes/console.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/console.php))
    *   Classe Notifica: [TaskDueSoon.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskDueSoon.php)

### 11. Feed Attività (Activity Logs)
*   **Descrizione**: Cronologia dettagliata per ogni progetto, visualizzabile all'interno della bacheca sotto la scheda "Attività". Utilizza un campo JSON flessibile `meta` deserializzato automaticamente in array da Eloquent.
*   **File Principali**:
    *   Modello: [ActivityLog.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php)
    *   Logica di tracciamento inserita nei controllori: [TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php) e [ProjectController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/ProjectController.php)

---

## 🗄️ Struttura Database (Migrazioni)

Il database è descritto e strutturato nelle seguenti migrazioni situate in [database/migrations](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/database/migrations):

1.  **Tabella Utenti**: `0001_01_01_000000_create_users_table.php` (contiene la colonna `sys_role` per distinguere tra `admin` e `user`).
2.  **Tabella Progetti**: `2026_05_18_060622_create_projects_table.php` (traccia il nome, la descrizione e la data di archiviazione `archived_at`).
3.  **Tabella Pivot Utenti-Progetti (Membri)**: `2026_05_18_060651_create_project_user_table.php` (traccia i membri e il loro ruolo specifico nel progetto: `pm`, `developer`, `viewer`).
4.  **Tabelle Colonne**: `2026_05_18_060705_create_column_types_table.php` (tipi di colonna disponibili come Idee, To Do, In Progress, In Review, Done) e `2026_05_18_060706_create_project_columns_table.php` (colonne effettivamente presenti nel progetto).
5.  **Tabella Task**: `2026_05_18_060719_create_tasks_table.php` (traccia titolo, descrizione, priorità, data di scadenza, colonna di appartenenza e ordinamento).
6.  **Tabella Sottoattività**: `2026_05_18_060720_create_subtasks_table.php` (collegati uno-a-molti con i task, tracciano il completamento `done`).
7.  **Tabella Etichette**: `2026_05_18_060733_create_labels_table.php` (etichette di progetto con nome e colore).
8.  **Tabella Log Attività**: `2026_05_18_060744_create_activity_logs_table.php` (contiene la colonna JSON `meta` per un log flessibile ed estendibile).

---

## 🎨 Componenti Vista Condivisi (Blade)

I componenti riutilizzabili dell'interfaccia utente si trovano in [resources/views/components](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components):

- **Barra Laterale (Sidebar)**: [sidebar.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/sidebar.blade.php)
- **Finestre Modali**: [modal.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/modal.blade.php)
- **Banner Notifiche in Tempo Reale (Toast)**: [toast.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/toast.blade.php)
- **Priorità Badge & Avatar**: [priority-badge.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/priority-badge.blade.php) e [avatar.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/components/avatar.blade.php)

---

## 🧪 Test Automatizzati

Il progetto ha una copertura di test estesa. I test delle funzionalità (Feature Tests) si trovano in [tests/Feature](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature):

- **Attività Log**: [ActivityLogTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/ActivityLogTest.php)
- **Amministrazione Utenti**: [AdminUsersTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/AdminUsersTest.php)
- **Sicurezza e Ruoli Progetto**: [MemberManagementAndRolePermissionsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/MemberManagementAndRolePermissionsTest.php)
- **Gestione Colonne**: [KanbanColumnsTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/KanbanColumnsTest.php)
- **CRUD e Spostamento Task**: [TaskCRUDAndMoveTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/TaskCRUDAndMoveTest.php)
- **Invio Notifiche**: [EmailNotificationTest.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/tests/Feature/EmailNotificationTest.php)

---

## 📖 Glossario e Concetti Tecnici Complessi

Di seguito sono descritti e spiegati i concetti architetturali e i costrutti di codice più avanzati implementati all'interno di questo progetto:

### 1. HTTP Middleware & Route Aliasing
I Middleware agiscono come filtri per le richieste HTTP che entrano nella tua applicazione. Nel file [app.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/bootstrap/app.php#L15-L20) sono definiti due alias fondamentali:
- `'system.role'`: Mappa sul middleware [EnsureSystemRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureSystemRole.php) ed è usato per proteggere le rotte amministrative. Verifica se il ruolo globale del profilo utente (`sys_role`) è `admin`.
- `'project.access'`: Mappa sul middleware [EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php). Intercetta la richiesta per le rotte nidificate sotto `/projects/{project}`. Questo middleware estrae il parametro `{project}` dall'URL, interroga il database per determinare se l'utente appartiene al progetto e confronta il suo ruolo pivot (`pm`, `developer`, `viewer`) con i ruoli consentiti per quella rotta (es. `project.access:pm,developer`). Se l'utente è un amministratore globale di sistema, il controllo viene automaticamente ignorato (`bypass`), concedendo accesso completo.

### 2. Aggiornamento Atomico delle Relazioni "Uno-a-Molti"
Nelle relazioni molti-a-molti (Many-to-Many), Laravel offre la comodissima funzione `$model->relation()->sync([id1, id2])` per allineare automaticamente la tabella pivot ai soli ID forniti, inserendo, aggiornando o cancellando le righe necessarie.
Nelle relazioni uno-a-molti (One-to-Many), come quella tra `Task` e `Subtask`, questa funzione non esiste. Nel metodo `syncSubtasks` di [TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L217-L228), viene applicata una tecnica di **aggiornamento atomico tramite ricreazione**:
1. Si eliminano tutte le sottoattività correnti associate al task tramite `$task->subtasks()->delete()`.
2. Si esegue un ciclo inserendo ex-novo le sottoattività aggiornate passate nella richiesta HTTP.
Questo approccio evita disallineamenti di ID e assicura che lo stato del DB rifletta fedelmente l'esatto elenco inviato dall'interfaccia utente.

### 3. Aggiornamento Ottimistico (Optimistic UI Update) e Gestione del Rollback
Nel file [show.blade.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/resources/views/projects/show.blade.php) è implementata una logica di trascinamento dei task (drag-and-drop) tramite Alpine.js. 
- Quando l'utente sposta una card, il client aggiorna immediatamente l'interfaccia visiva (spostando la card nella nuova colonna) **prima** ancora di inviare la richiesta di rete al server. Questo rende l'interfaccia "istantanea" e piacevole (senza ritardi).
- Contemporaneamente, viene inviata la richiesta `PATCH` all'endpoint di spostamento del backend. Se l'API restituisce un errore (ad esempio per problemi di connessione o perché l'utente non ha i permessi di scrittura sul progetto), il frontend intercetta l'errore ed esegue un **rollback**, ripristinando la card nella colonna di partenza originaria.

### 4. Casting JSON in Eloquent
Nel database SQLite o MySQL, i log delle attività (`activity_logs`) richiedono di salvare dati flessibili ed eterogenei (es. quando si sposta un task serve salvare la colonna di partenza e arrivo; quando si cambia titolo serve il vecchio e il nuovo titolo; quando si assegna un utente serve il nome).
Invece di creare decine di colonne separate per ciascuno scenario, viene utilizzata una singola colonna di tipo JSON chiamata `meta`.
Nel modello [ActivityLog.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php#L15), la colonna `meta` è definita con il cast `array`:
```php
protected function casts(): array
{
    return [
        'meta' => 'array',
    ];
}
```
Questo indica a Eloquent di convertire in automatico la stringa JSON del database in un array associativo PHP in fase di lettura, e serializzarlo nuovamente in formato JSON in fase di scrittura, semplificando la manipolazione dei dati nel controller.

### 5. Coda Asincrona dei Job (Queue) e interfaccia "ShouldQueue"
Inviare un'e-mail a un server SMTP esterno durante una chiamata HTTP è un'operazione lenta (può richiedere da 1 a 5 secondi). Se gestita in modo sincrono, l'utente vedrebbe la pagina bloccata ad ogni salvataggio di task.
Per risolvere questo problema, le notifiche e-mail (es. [TaskAssignment.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskAssignment.php)) implementano l'interfaccia `ShouldQueue`.
- Quando viene scatenato l'evento di notifica, Laravel non invia subito l'e-mail. Al contrario, scrive un record (contenente i dati del job da svolgere) nella tabella `jobs` del database e risponde subito all'utente (connessione HTTP istantanea).
- Un processo worker in esecuzione in background (lanciato tramite il comando `php artisan queue:work`) interroga periodicamente la tabella `jobs` ed elabora l'effettivo invio delle e-mail senza impattare sui tempi di risposta dell'applicazione web.

### 6. Soft Deletes (Cancellazioni Logiche)
Nel modello [User.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/User.php) è attivo il tratto `use SoftDeletes`.
- Quando viene richiamato il metodo `$user->delete()`, il record non viene cancellato fisicamente dal database (operazione `DELETE` SQL). Viene invece compilata una colonna `deleted_at` con la data e l'ora correnti.
- Per le query standard di Eloquent, l'utente risulterà invisibile (come se non esistesse). Tuttavia, i dati storici legati ad esso (es. chi ha eseguito una determinata azione nel log delle attività) rimangono intatti e validi nel database.
- L'utente può essere ripristinato in qualsiasi momento con il metodo `$user->restore()`.

### 7. Validazione Transizionale del Progetto
Nel metodo `move` di [TaskController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L152-L160), la richiesta contiene il parametro `column_id`. Per evitare che utenti malintenzionati inviino modifiche manipolando le chiamate HTTP (ad esempio inviando l'ID di una colonna appartenente a un altro progetto per rubare o spostare un task), la validazione applica una regola di transizione condizionata:
```php
Rule::exists('project_columns', 'id')->where('project_id', $project->id)
```
Questo assicura che il backend accetti lo spostamento del task *solo ed esclusivamente* verso una colonna valida associata a quello specifico progetto.

### 8. Gestione delle Relazioni Pivot in Eloquent
Per allineare e manipolare la tabella pivot `project_user` in [MemberController.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php), vengono usate funzioni specializzate di Eloquent:
- `attach($userId, ['role' => 'pm'])`: Aggiunge un record nella tabella pivot.
- `detach($userId)`: Rimuove il record dalla tabella pivot (rimuovendo il membro dal progetto).
- `updateExistingPivot($userId, ['role' => 'developer'])`: Aggiorna solo le colonne aggiuntive presenti sulla tabella pivot (in questo caso, il ruolo) per un record esistente.
- `withTrashed()`: Permette di recuperare i record nella tabella dei membri anche se sono stati precedentemente eliminati logicamente (soft delete), rendendo possibile il ripristino dell'utente tramite `restore()`.
