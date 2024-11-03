DOCKER_DIR=docker

create:
	make up
	make install

up:
	@composer install

install:
	@cd ${DOCKER_DIR} && docker compose up -d --build && \
	if [ $$? -eq 0 ]; then \
	    echo 'System is running at http://localhost:8000'; \
	else \
	    echo 'Failed to start system. Check Docker logs for details.'; \
	fi

in:
	@echo 'Entering container'
	@docker exec -it docker-php bash

db-migrate:
	@echo 'Start migrations'
	@docker exec -it docker-php php bin/console doctrine:migrations:migrate

db-diff:
	@echo 'Creating migrations'
	@docker exec -it docker-php php bin/console doctrine:migrations:diff --allow-empty-diff

phpstan:
	./vendor/bin/phpstan analyse src

phpcs:
	./vendor/bin/phpcs --error-severity=1 --warning-severity=2 src

phpcs-fix:
	./vendor/bin/phpcbf src

functional-tests:
	docker exec -it docker-php php bin/phpunit --testsuite functional

phpunit-tests:
	docker exec -it docker-php php bin/phpunit --testsuite default




