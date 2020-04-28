docker exec -it build_laravel_1 vendor/bin/phpunit --coverage-clover=coverage.clover
docker cp build_laravel_1:/app/coverage.clover coverage.clover
