# Simple dev targets for Docker-based WordPress
ENV_FILE=dev/.env
COMPOSE=docker compose --env-file $(ENV_FILE) -f dev/docker-compose.yml

.PHONY: up down logs cli wp shell reset-db import-sample activate-plugin smoke

up:
\t$(COMPOSE) up -d

down:
\t$(COMPOSE) down -v

logs:
\t$(COMPOSE) logs -f

cli:
\t$(COMPOSE) run --rm wpcli bash

wp:
\t$(COMPOSE) run --rm wpcli wp $(ARGS)

shell:
\t$(COMPOSE) run --rm wpcli bash

activate-plugin:
\t$(COMPOSE) run --rm wpcli bash /scripts/activate_plugin.sh

import-sample:
\t$(COMPOSE) run --rm wpcli bash /scripts/import_sample_sql.sh

reset-db:
\t$(COMPOSE) run --rm wpcli wp db reset --yes && \\\n\t$(COMPOSE) run --rm wpcli wp core install --url=$${SITE_URL:-http://localhost:8080} --title=\"Monthly Booking Dev\" --admin_user=admin --admin_password=admin --admin_email=admin@example.com

smoke:
\t$(COMPOSE) ps && curl -fsS $${SITE_URL:-http://localhost:8080}/wp-login.php >/dev/null && echo \"WP up\"
