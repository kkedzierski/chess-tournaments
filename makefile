.DEFAULT_GOAL := help

CONTAINER_NAME = chess-tournaments-php

.PHONY: exec-root
exec-root: ## Shell into container
	docker exec -it -u root $(CONTAINER_NAME) /bin/bash

