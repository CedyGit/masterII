# Image de base PHP avec FPM
FROM php:8.3-fpm-alpine

# Installer les dépendances système
RUN apk update && apk add --no-cache \
    git \
    build-base \
    autoconf \
    libpq-dev \
    postgresql-dev \
    nginx \
    supervisor \
    curl \
    oniguruma-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring

# Répertoire de travail
WORKDIR /var/www/html

# Copier Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copier les fichiers
COPY . .

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Créer les dossiers nécessaires
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Cache Laravel
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Configuration Nginx
RUN echo 'server { \
    listen 8000; \
    root /var/www/html/public; \
    index index.php; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/http.d/default.conf

# Supervisord
RUN echo '[supervisord] \n\
nodaemon=true \n\
[program:php-fpm] \n\
command=php-fpm \n\
autostart=true \n\
autorestart=true \n\
[program:nginx] \n\
command=nginx -g "daemon off;" \n\
autostart=true \n\
autorestart=true' > /etc/supervisord.conf

# Exposer le port
EXPOSE 8000

# Démarrer
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]