# syntax=docker/dockerfile:1
FROM mcr.microsoft.com/devcontainers/base:ubuntu-22.04

RUN apt-get update && apt-get install -y \
    curl unzip git \
    php-cli php-xml php-mbstring php-curl \
    less mysql-client \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /workspaces/monthly-booking
