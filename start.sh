#!/bin/sh

echo "🚀 Starting Laravel deployment..."

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Créer les dossiers si nécessaire
mkdir -p storage/logs
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache

# Permissions
chmod -R 775 storage bootstrap/cache

# Lancer les migrations
php artisan migrate --force

if [ "$SEED" = "true" ]; then
    echo "📊 Seeding database..."
    php artisan db:seed --force
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf

echo "✅ Deployment complete!"

# Démarrer supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf