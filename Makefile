login-app:
	 docker exec -it keestash-api bash
login-db:
	 docker exec -it keestash-db bash
flush-redis:
	 docker exec keestash-redis redis-cli FLUSHDB
composer-update:
	 docker exec keestash-api composer update -W
composer-install:
	 docker exec keestash-api composer install
test:
	 docker exec keestash-api composer test
run-queue:
	 docker exec keestash-api php bin/console.php worker:run
queue-list:
	 docker exec keestash-api php bin/console.php worker:queue:list
