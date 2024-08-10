login-app:
	 docker exec -it keestash-api bash
login-db:
	 docker exec -it keestash-db bash
stripe-login:
	 docker exec -it stripe-cli stripe login
stripe-listen:
	 docker exec -it stripe-cli stripe listen --forward-to https://keestash-web/api.php/payment/webhook --skip-verify
stripe-log-tail:
	 docker exec -it stripe-cli stripe logs tail
flush-redis:
	 docker exec keestash-redis redis-cli FLUSHDB
update-composer:
	 docker exec keestash-web composer update -W
test:
	 docker exec keestash-web composer test
run-queue:
	 docker exec keestash-web php bin/console.php worker:run
queue-list:
	 docker exec keestash-web php bin/console.php worker:queue:list
