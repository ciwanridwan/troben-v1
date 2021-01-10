job("Test") {
    container("jalameta/php-cli:8.0") {
        shellScript {
            content = """
                cp .env.example .env
                composer install
                php artisan key:generate
                php artisan test
            """
        }
    }

    container("jalameta/php-cli:7.4") {
            shellScript {
                content = """
                    cp .env.example .env
                    composer install
                    php artisan key:generate
                    php artisan test
                """
            }
        }
}
