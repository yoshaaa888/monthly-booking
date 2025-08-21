# Simple dev targets for Docker-based WordPress
ENV_FILE=dev/.env
COMPOSE=docker compose --env-file $(ENV_FILE) -f dev/docker-compose.yml

.PHONY: up down logs cli wp shell reset-db import-sample activate-plugin smoke

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down -v

logs:
	$(COMPOSE) logs -f

cli:
	$(COMPOSE) run --rm wpcli bash

wp:
	$(COMPOSE) run --rm wpcli wp $(ARGS)

shell:
	$(COMPOSE) run --rm wpcli bash

activate-plugin:
	$(COMPOSE) run --rm wpcli bash /scripts/activate_plugin.sh

import-sample:
	$(COMPOSE) run --rm wpcli bash /scripts/import_sample_sql.sh

reset-db:
	$(COMPOSE) run --rm wpcli wp db reset --yes && \
	$(COMPOSE) run --rm wpcli wp core install --url=$${SITE_URL:-http://localhost:8080} --title="Monthly Booking Dev" --admin_user=admin --admin_password=admin --admin_email=admin@example.com

smoke:
	$(COMPOSE) ps && curl -fsS $${SITE_URL:-http://localhost:8080}/wp-login.php >/dev/null && echo "WP up"
