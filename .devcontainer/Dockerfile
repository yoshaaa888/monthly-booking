FROM mcr.microsoft.com/devcontainers/base:ubuntu-22.04

# OS deps for WordPress/PHP & tools
RUN apt-get update && apt-get install -y \
    curl unzip git \
    php-cli php-mbstring php-xml php-curl \
    less mysql-client \
  && rm -rf /var/lib/apt/lists/*

# Node is provided via devcontainer "features" (configured in devcontainer.json)
# Playwright browsers/deps are installed in postCreateCommand (container runtime)

WORKDIR /workspaces/monthly-booking
