# BrainFlow

Applicativo Kanban sviluppato con Laravel 12, Tailwind CSS e Alpine.js.

---

## Requisiti

| Strumento | Versione minima |
|-----------|----------------|
| PHP       | 8.2            |
| Composer  | 2.x            |
| Node.js   | 18.x           |
| npm       | 9.x            |

Il database di default e **SQLite** (nessuna installazione aggiuntiva richiesta).

---

## Installazione

### 1. Dipendenze PHP

```bash
composer install
```

### 2. File di configurazione

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database

```bash
php artisan migrate --seed
```

Il comando esegue le migrazioni e popola il database con dati di esempio (3 progetti, 6 utenti, task, label e activity log).

### 4. Dipendenze JavaScript e compilazione assets

```bash
npm install
npm run build
```

Per lo sviluppo con hot-reload usare `npm run dev` al posto di `npm run build`.

---

## Avvio

```bash
php artisan serve
```

L'applicazione e disponibile su [http://localhost:8000](http://localhost:8000).

---

## Avvio con Docker

Alternativa all'installazione manuale. Richiede solo **Docker Desktop** installato.

### 1. Primo avvio

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed --force
```

L'applicazione sarà disponibile su [http://localhost:7777](http://localhost).

### 2. Comandi quotidiani

```bash
docker compose up -d      # avvia i container
docker compose down       # ferma i container (i dati restano)
docker compose down -v    # ferma e cancella tutto, incluso il database
```

### 3. Rebuild dopo modifiche al codice

```bash
docker compose build --no-cache
docker compose up -d
```

> I container avviati sono tre: **app** (PHP-FPM), **webserver** (Nginx), **db** (MySQL 8.0).
> Il file `.env` non viene copiato nell'immagine — le variabili vengono iniettate a runtime tramite `env_file`.

---

## Credenziali demo

| Ruolo  | Email                  | Password  |
|--------|------------------------|-----------|
| Admin  | christian@brainflow.it | Password1 |
| Utente | sofia@brainflow.it     | Password1 |
| Utente | luca@brainflow.it      | Password1 |
| Utente | marta@brainflow.it     | Password1 |
| Utente | davide@brainflow.it    | Password1 |
| Utente | elena@brainflow.it     | Password1 |

---

## Notifiche email

Per impostazione predefinita le email vengono scritte nel file di log (`storage/logs/laravel.log`).

Per attivare le notifiche di scadenza imminente (task con `due_date` = domani) avviare lo scheduler in un terminale separato:

```bash
php artisan schedule:work
```

In alternativa, eseguire il comando manualmente:

```bash
php artisan tasks:notify-due-soon
```

---

## Code in background

Le notifiche email vengono accodate. Per processare la coda avviare il worker in un terminale separato:

```bash
php artisan queue:work
```

---

## Test

```bash
php artisan test
```

Per eseguire solo una suite specifica:

```bash
php artisan test tests/Feature/KanbanColumnsTest.php
```
