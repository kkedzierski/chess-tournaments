.DEFAULT_GOAL := help

CONTAINER_NAME = chess-tournaments-php
NODE_CONTAINER_NAME = chess-tournaments-node

.PHONY: exec-root
exec-root: ## Shell into container
	docker exec -it -u root $(CONTAINER_NAME) /bin/bash

.PHONY: rebuilt-node c-c cc
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
