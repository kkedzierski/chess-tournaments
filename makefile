.DEFAULT_GOAL := help

CONTAINER_NAME = chess-tournaments-php
NODE_CONTAINER_NAME = chess-tournaments-node

.PHONY: exec-root
exec-root: ## Shell into container
	docker exec -it -u root $(CONTAINER_NAME) /bin/bash

.PHONY: rebuilt-node r-n rn
rebuilt-node: ## Rebuild node container
	docker-compose run --rm ${NODE_CONTAINER_NAME} npm run build
r-n: rebuilt-node ## Alias for rebuilt-node
rn: rebuilt-node ## Alias for rebuilt-node

.PHONY: exec-node
exec-node: ## Shell into node container
	docker exec -it -u root $(NODE_CONTAINER_NAME) /bin/bash

.PHONY: clear-cache c-c cc
clear-cache: ## Clear cache
	docker exec -it -u root $(CONTAINER_NAME) bin/console cache:clear
c-c: clear-cache ## Alias for clear-cache
cc: cc ## Alias for clear-cache

.PHONY: tests t
tests: ## tests
	docker exec -it -u root $(CONTAINER_NAME) ./vendor/bin/phpunit
	docker exec -it -u root $(CONTAINER_NAME) ./vendor/bin/infection --min-msi=100 --min-covered-msi=100
test: tests ## Alias for tests
tests-all: test ## Alias for test

.PHONY: infection tests
infection: ## infection tests
	docker exec -it -u root $(CONTAINER_NAME) ./vendor/bin/infection --min-msi=100 --min-covered-msi=100
inf: infection ## Alias for mutation
test-infection: infection ## Alias for mutation
tests-inf: infection ## Alias for mutation

.PHONY: unit tests
phpunit: ## unit tests
	docker exec -it -u root $(CONTAINER_NAME) ./vendor/bin/phpunit
unit-test: phpunit ## Alias for mutation
phpunit-test: phpunit ## Alias for mutation
test-unit: phpunit ## Alias for mutation
tests-unit: phpunit ## Alias for mutation