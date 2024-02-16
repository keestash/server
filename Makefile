login-app:
	 docker exec -it keestash-web bash
login-db:
	 docker exec -it mysql8 bash
flush-redis:
	 docker exec keestash-redis redis-cli FLUSHDB
update-composer:
	 docker exec keestash-web composer update -W
test:
	 docker exec keestash-web composer test
