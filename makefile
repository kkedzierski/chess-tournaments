.DEFAULT_GOAL := help

CONTAINER_NAME = chess-tournaments-php
NODE_CONTAINER_NAME = chess-tournaments-node

.PHONY: exec-root
exec-root: ## Shell into container
	docker exec -it -u root $(CONTAINER_NAME) /bin/bash

rebuilt-node: ## Rebuild node container
	docker-compose run --rm ${NODE_CONTAINER_NAME} npm run build
