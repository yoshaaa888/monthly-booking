FROM mcr.microsoft.com/devcontainers/base:ubuntu-22.04

RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    php-cli \
    php-mbstring \
    php-xml \
    php-curl \
    less \
    mysql-client \
    && rm -rf /var/lib/apt/lists/*

ENV NVM_DIR=/usr/local/nvm
RUN mkdir -p $NVM_DIR
ENV NODE_VERSION=18
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.5/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV PATH="$NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH"

RUN npm install -g playwright && npx playwright install --with-deps

WORKDIR /workspace
