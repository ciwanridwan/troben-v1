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
}
