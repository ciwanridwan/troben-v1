#!/bin/sh
set -e

echo "Deploying application ..."

# Enter maintenance mode
(php artisan down) || true
    # Update codebase
#    git fetch origin master
#    git pull origin master

    sudo chmod -R 777 storage
    sudo chmod -R 777 bootstrap/cache

    # Install dependencies based on lock file
    composer install --no-interaction --prefer-dist --optimize-autoloader

    # Migrate database
    php artisan migrate --force

    # Patching update
    php artisan patcher:run --force

    # Note: If you're using queue workers, this is the place to restart them.
    php artisan horizon:terminate

    # NPM
    npm install
    npm run production

    # Clear cache
    php artisan optimize

    # Reload PHP to update opcache
    echo "" | sudo -S service php8.0-fpm reload

# Exit maintenance mode
php artisan up

echo "Application deployed!"
