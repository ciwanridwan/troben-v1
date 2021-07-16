#!/bin/sh
set -e

echo "Deploying application ..."

# Enter maintenance mode
(php artisan down) || true
    sudo chmod -R 777 storage
    sudo chmod -R 777 bootstrap/cache

    # Install dependencies based on lock file
    composer install --no-interaction --prefer-dist --optimize-autoloader

    # Migrate database
    # php artisan migrate:fresh --force
    php artisan migrate --force

    # Seeder db
    # php artisan db:seed --class=StagingDatabaseSeeder

    # Patching update
    php artisan patcher:run --force

    # Note: If you're using queue workers, this is the place to restart them.
    php artisan horizon:terminate

    # run NPM in screen mode and forget it
    # screen -dm bash -c 'npm ci && npm run production'
    npm ci && npm run production

    # Clear cache
    php artisan optimize

    # Reload PHP to update opcache
    echo "" | sudo -S service php7.4-fpm reload

# Exit maintenance mode
php artisan up

echo "Application deployed!"
