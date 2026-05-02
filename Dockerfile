FROM php:8.2-cli

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev zip

# Installer extensions PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir dossier de travail
WORKDIR /app

# Copier fichiers
COPY . .

# Installer dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chmod -R 777 storage bootstrap/cache

# Port Render
EXPOSE 10000

# Démarrage
CMD php artisan serve --host=0.0.0.0 --port=10000