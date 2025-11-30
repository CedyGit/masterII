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
    bash \
    && docker-php-ext-install pdo pdo_pgsql mbstring

# Répertoire de travail
WORKDIR /var/www/html

# Copier Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copier les fichiers
COPY . .

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier le script de démarrage
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Créer les dossiers nécessaires
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

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

# Configuration Supervisord
RUN printf "[supervisord]\n\
nodaemon=true\n\
logfile=/dev/stdout\n\
logfile_maxbytes=0\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g 'daemon off;'\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n" > /etc/supervisord.conf

# Exposer le port
EXPOSE 8000

# Utiliser le script de démarrage
CMD ["/usr/local/bin/start.sh"]