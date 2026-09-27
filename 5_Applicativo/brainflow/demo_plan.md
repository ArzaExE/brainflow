# Piano Demo & Script Presentatore: Focus Backend (10 Minuti)

Questo documento contiene lo **script dettagliato passo-passo** per condurre una demo di 10 minuti di **BrainFlow**, focalizzandosi sulle scelte architetturali del backend (Laravel 12, Eloquent, custom middleware, code, schedulazione e vincoli di integrità).

---

## ⏱️ Tabella di Marcia ed Elenco dei Moduli Coinvolti

```mermaid
gantt
    title Scaletta Demo Backend (10 Minuti)
    dateFormat  X
    axisFormat %s
    section Scaletta
    1. Accesso & Middlewares (0:00 - 2:00)         :active, 0, 120
    2. Vincoli & Integrità DB (2:00 - 4:00)       : 120, 240
    3. Sincronizzazione Atomica Task (4:00 - 6:00) : 240, 360
    4. Drag-and-Drop & API Spostamento (6:00 - 7:30): 360, 450
    5. JSON Meta & Activity Log (7:30 - 8:30)      : 450, 510
    6. Code e Schedulazione Notifiche (8:30 - 10:00): 510, 600
```

---

## 🎙️ Script Passo-Passo per la Demo

---

### Step 1: Accesso, Gestione Ruoli Globale e Middleware di Progetto (Minuti 0:00 - 2:00)

*   **Azione a schermo**:
    1. Apri la schermata di login su `http://localhost:8000` (o `http://localhost:7777` se usi Docker).
    2. Effettua l'accesso come Amministratore (`christian@brainflow.it`, password `Password1`).
    3. Naviga brevemente nell'Admin Panel (`/admin/users`) mostrando l'elenco utenti.
*   **Script del Presentatore**:
    > "Benvenuti alla demo di BrainFlow. Oggi ci concentreremo sull'architettura e sulle logiche backend dell'applicazione.
    >
    > L'applicazione gestisce due livelli indipendenti di autorizzazione: il ruolo globale di sistema (`sys_role`) e il ruolo specifico all'interno di ciascun progetto. 
    > 
    > Al primo livello, ho effettuato l'accesso come amministratore di sistema. Questo ruolo ha visibilità globale ed è in grado di creare o modificare utenti nel sistema, oltre ad accedere a tutti i progetti. 
    >
    > Questo controllo degli accessi è centralizzato a livello HTTP tramite middleware personalizzati. Vediamo come."
*   **Dettagli Tecnici da citare**:
    *   **Middleware globali**: Mostra come [EnsureSystemRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureSystemRole.php) protegge le rotte `/admin/*` controllando la colonna `sys_role`.
    *   **Middleware di Progetto**: Mostra [EnsureProjectRole.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Middleware/EnsureProjectRole.php) (registrato in [app.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/bootstrap/app.php) con l'alias `project.access`). Spiega che questo middleware intercetta le richieste `/projects/{project}/*`, estrae il parametro della rotta, verifica l'associazione nella tabella pivot `project_user` e controlla se il ruolo dell'utente (PM, Developer, Viewer) è incluso tra quelli autorizzati per quella specifica rotta. Se l'utente è amministratore di sistema, il middleware bypassa il controllo, concedendo l'accesso completo in modo trasparente.

---

### Step 2: Logiche di Integrità nel Database & Autoprotezione (Minuti 2:00 - 4:00)

*   **Azione a schermo**:
    1. Dall'Admin Panel, prova a modificare il ruolo dell'unico amministratore rimasto a `user` o a eliminarlo (mostra l'errore restituito).
    2. Naviga all'interno del Progetto Demo e prova a eliminare un membro che ha dei task attivi assegnati (mostra l'errore restituito dal backend).
*   **Script del Presentatore**:
    > "Una delle priorità dello sviluppo backend è stata garantire l'integrità del database, prevenendo stati di inconsistenza derivanti da operazioni errate degli utenti.
    >
    > Ad esempio, il sistema impedisce l'eliminazione accidentale dell'ultimo amministratore globale o la sua retrocessione a utente normale. Se proviamo a farlo, l'API del backend restituisce un errore di validazione `422`.
    >
    > Allo stesso modo, a livello di progetto, un utente non può essere rimosso se ha dei task assegnati a suo carico. Questo evita la creazione di task 'orfani' e costringe il PM a riassegnare o completare le attività pendenti prima di escludere il collaboratore."
*   **Dettagli Tecnici da citare**:
    *   **Autoprotezione Admin**: Nel metodo `update` e `destroy` di [AdminController.php:L58-92](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/AdminController.php#L58-L92) viene effettuato un conteggio preventivo (`User::where('sys_role', 'admin')->count()`) per bloccare l'azione se l'utente target è l'ultimo amministratore.
    *   **Integrità Membri-Task**: Nel metodo `destroy` di [MemberController.php:L81-117](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/MemberController.php#L81-L117), viene eseguita una query Eloquent con `whereHas` per estrarre tutti i task associati all'utente in quel progetto. Se la collezione non è vuota, l'eliminazione viene interrotta restituendo l'elenco dei primi 5 task bloccanti.
    *   **Soft Deletes**: Per gli utenti è abilitato il Soft Delete (`use SoftDeletes` nel modello `User.php`). La cancellazione di un utente sul pannello Admin è logica e non fisica, consentendo di ripristinare l'account o consultare storici passati senza violare i vincoli di integrità referenziale.

---

### Step 3: Database Relazionale e Aggiornamento Atomico dei Task (Minuti 4:00 - 6:00)

*   **Azione a schermo**:
    1. Entra nella bacheca del progetto, clicca su una card per aprire il Drawer dei dettagli.
    2. Modifica il titolo del task, assegna un nuovo membro, seleziona una label e aggiungi/spunta un paio di sottoattività (subtasks). Clicca su "Salva".
*   **Script del Presentatore**:
    > "La gestione di un task coinvolge relazioni di tipo diverso. Un task ha assegnatari multipli (relazione molti-a-molti), etichette multiple (molti-a-molti) e una checklist di sottoattività (relazione uno-a-molti).
    >
    > Per le relazioni molti-a-molti, usiamo i metodi di sincronizzazione nativi di Laravel. Per la relazione uno-a-molti delle sottoattività, invece, abbiamo dovuto implementare una logica di aggiornamento atomico personalizzata nel backend per gestire in modo sicuro l'aggiunta, la modifica o la cancellazione delle singole voci."
*   **Dettagli Tecnici da citare**:
    *   **Relazioni Eloquent**: Mostra l'uso di `$task->assignees()->sync(...)` e `$task->labels()->sync(...)` nel controller.
    *   **Sincronizzazione Uno-a-Molti personalizzata**: Spiega che, a differenza delle relazioni molti-a-molti, per una relazione uno-a-molti come le sottoattività (`subtasks`), Laravel non dispone di un metodo `sync` nativo. Nel metodo [TaskController.php:L217-228](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L217-L228), viene eseguita un'operazione atomica: si cancellano preventivamente tutte le sottoattività associate al task (`$task->subtasks()->delete()`) e si ricreano da zero le sottoattività aggiornate passate nella richiesta. Questo garantisce la coerenza dello stato del DB senza generare record duplicati o orfani.

---

### Step 4: Spostamento Task: Validazione di Transizione & Aggiornamento Ottimistico (Minuti 6:00 - 7:30)

*   **Azione a schermo**:
    1. Trascina una card da una colonna a un'altra.
    2. Evidenzia la reattività visiva istantanea (dovuta all'aggiornamento ottimistico lato client).
*   **Script del Presentatore**:
    > "Quando un utente sposta un task sulla bacheca, l'interfaccia esegue un aggiornamento ottimistico, assumendo che l'operazione andrà a buon fine per garantire la massima reattività.
    >
    > Contemporaneamente, viene inviata una richiesta PATCH al server. Il backend riceve la richiesta ed effettua validazioni critiche di sicurezza: verifica che l'utente abbia i permessi di scrittura sul progetto e convalida che la colonna di destinazione esista nel database e appartenga effettivamente a questo specifico progetto.
    >
    > Se la richiesta fallisce per motivi di rete o autorizzazioni, il front-end esegue un rollback automatico ripristinando la card nella colonna originale."
*   **Dettagli Tecnici da citare**:
    *   **Validazione transizionale**: Nel metodo `move` di [TaskController.php:L152-196](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Http/Controllers/TaskController.php#L152-L196), lo spostamento viene convalidato controllando l'esistenza della colonna tramite `Rule::exists('project_columns', 'id')->where('project_id', $project->id)`. Questo impedisce a utenti malintenzionati di spostare surrettiziamente i task su colonne di altri progetti modificando gli ID delle chiamate HTTP.

---

### Step 5: JSON Meta & Activity Log Flessibile (Minuti 7:30 - 8:30)

*   **Azione a schermo**:
    1. Clicca sulla scheda "Attività" del progetto.
    2. Mostra le ultime righe di log generate automaticamente a seguito dello spostamento e della modifica dei task fatti in precedenza.
*   **Script del Presentatore**:
    > "Ogni azione effettuata sul progetto viene tracciata in modo strutturato sul database per alimentare il feed delle attività.
    >
    > Per evitare di appesantire lo schema del database con tabelle di log rigide per ogni tipo di azione (creazione, spostamento, modifica nome, ecc.), abbiamo implementato una tabella di log flessibile. 
    >
    > Utilizziamo una colonna di tipo JSON chiamata `meta` per memorizzare dettagli variabili come il titolo precedente del task, il nome della colonna di partenza e di quella di arrivo, o l'elenco degli utenti assegnati. Questa colonna viene convertita automaticamente in array da Eloquent."
*   **Dettagli Tecnici da citare**:
    *   **JSON Casting**: Nel modello [ActivityLog.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Models/ActivityLog.php), la colonna `meta` è inserita nel metodo `casts()` come `array`. Questo converte automaticamente la colonna in formato JSON in fase di scrittura e la deserializza in un array PHP in fase di lettura.
    *   **Salvataggio e Lettura**: Mostra come il controller inserisce i metadati (es. `['from_column' => $from, 'to_column' => $to]`) e come il front-end in Alpine compila dinamicamente il testo tramite la funzione `activityText(entry)`.

---

### Step 6: Code in Background e Schedulazione Notifiche (Minuti 8:30 - 10:00)

*   **Azione a schermo**:
    1. Apri un terminale separato e mostra il log del scheduler o delle code.
    2. Esegui il comando console manuale: `php artisan tasks:notify-due-soon`.
    3. Mostra nel log di Laravel (`storage/logs/laravel.log`) l'avvenuto invio/accodamento delle email di scadenza.
*   **Script del Presentatore**:
    > "Infine, passiamo alla gestione delle notifiche email e dell'asincronismo.
    >
    > Per evitare che l'utente attenda la transazione di invio e-mail (che può richiedere alcuni secondi) durante il salvataggio o lo spostamento di un task, le e-mail vengono gestite in modo asincrono tramite il sistema di code (Queue) di Laravel.
    >
    > Inoltre, abbiamo implementato un comando console personalizzato schedulato per eseguire controlli automatici sulle scadenze dei task. Questo comando viene invocato automaticamente dallo scheduler di Laravel ogni mattina alle 8:00."
*   **Dettagli Tecnici da citare**:
    *   **Scheduler e Command**: Il comando personalizzato `tasks:notify-due-soon` (implementato in [SendDueSoonNotifications.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Console/Commands/SendDueSoonNotifications.php)) è registrato in [console.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/routes/console.php#L11-L14) per girare in background giornalmente.
    *   **Logica di Query Ottimizzata**: Il comando estrae solo i task che scadono il giorno successivo, escludendo quelli nelle colonne contrassegnate come completate (`is_done = true`) o appartenenti a progetti archiviati (`archived_at IS NOT NULL`), riducendo al minimo il carico sul DB.
    *   **Code asincrone**: Mostra come la notifica [TaskDueSoon.php](file:///C:/Users/chris/Desktop/Project/brainflow/5_Applicativo/brainflow/app/Notifications/TaskDueSoon.php) implementi l'interfaccia `ShouldQueue`. Laravel spinge automaticamente queste notifiche nella tabella/coda dei job (`jobs`), lasciando che sia il worker (`php artisan queue:work`) a elaborarle in background senza bloccare la richiesta dell'utente.

---

## 🛠️ Come Preparare l'Ambiente per la Demo (Cheat Sheet)

Per far sì che la demo si svolga senza intoppi, esegui questi comandi preliminari:

1.  **Reset e Popolamento Database**:
    ```bash
    php artisan migrate:fresh --seed
    ```
    *Questo assicura che ci siano 3 progetti reali, 6 utenti pre-registrati, label e activity log di esempio.*

2.  **Configurazione del Mail Driver**:
    Nel file `.env`, assicurati che il driver sia impostato su `log` per vedere l'output nel terminale/log:
    ```env
    MAIL_MAILER=log
    QUEUE_CONNECTION=database
    ```

3.  **Avvio dei Servizi di Background (Terminale 1 & 2)**:
    *   Terminale 1 (Elaborazione code asincrone):
        ```bash
        php artisan queue:work
        ```
    *   Terminale 2 (Scheduler di Laravel):
        ```bash
        php artisan schedule:work
        ```
