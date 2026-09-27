#!/bin/sh
set -e

# ── Controllo APP_KEY ─────────────────────────────────────────────────────────
if [ -z "${APP_KEY}" ]; then
    echo "ERRORE: APP_KEY non è impostata nel file .env."
    echo "Esegui 'php artisan key:generate' prima di avviare Docker."
    exit 1
fi

# ── Inizializzazione DB (solo per il processo php-fpm, non per queue/scheduler) ─
if [ "$1" = "php-fpm" ]; then

    # Prima esecuzione: migra e popola il database con i dati di esempio
    if [ ! -f database/.seeded ]; then
        echo "Prima esecuzione: migrazione e seed del database..."
        php artisan migrate --force --seed
        touch database/.seeded
        echo "Database inizializzato."
    else
        # Esecuzioni successive: applica solo eventuali nuove migrazioni
        php artisan migrate --force
    fi

    # Cache delle configurazioni (solo in produzione)
    if [ "${APP_ENV}" = "production" ]; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    fi

fi

exec "$@"
