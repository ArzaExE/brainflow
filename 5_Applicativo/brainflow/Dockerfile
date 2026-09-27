FROM php:8.2-fpm

# Dipendenze di sistema + Node.js per compilare gli asset con Vite
RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    && rm -rf /var/lib/apt/lists/*

# Estensioni PHP richieste da Laravel con MySQL
RUN docker-php-ext-install pdo_mysql bcmath \
    && docker-php-ext-enable opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia tutto il progetto
COPY . .

# Installa dipendenze PHP (senza dev, senza script post-install per evitare
# conflitti con package dev-only come laravel/pail)
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Compila gli asset CSS/JS
RUN npm install && npm run build

# Crea le cartelle richieste da Laravel a runtime (potrebbero essere escluse dal .dockerignore)
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             storage/app/public \
             bootstrap/cache

# Permessi
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]

EXPOSE 9000
CMD ["php-fpm"]
