sh:
	docker-compose exec core sh

install:
	docker-compose exec core composer update && php artisan key:generate

log:
	docker-compose exec core tail -f -n 200 storage/logs/laravel.log

optimize:
	docker-compose exec core php artisan optimize && docker-compose exec core composer dump-autoload && docker-compose exec core php artisan view:cache && docker-compose exec core chown -R www-data:www-data /var/www/storage

up-quick:
	docker-compose up -d

up:
	docker-compose up -d --build
    
nocache:
    docker-compose build --no-cache

down:
	docker-compose down

.PHONY: sh
