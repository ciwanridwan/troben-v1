sh:
	docker-compose exec core sh

phpv:
	docker-compose exec core php -v

install:
	docker-compose exec core composer update && php artisan key:generate

log:
	docker-compose exec core tail -f -n 200 storage/logs/laravel.log

asset:
	npm run prod

optimize:
	docker-compose exec core php artisan optimize && docker-compose exec core composer dump-autoload && docker-compose exec core php artisan view:cache && docker-compose exec core chown -R www-data:www-data /var/www/storage

up-quick:
	docker-compose up -d

up:
	docker-compose up -d --build
    
down:
	docker-compose down

.PHONY: sh
