ARG APP_ENV=dev
ARG APP_DOCKER_REGISTRY=912850810755.dkr.ecr.eu-central-1.amazonaws.com

# >>> base
FROM ${APP_DOCKER_REGISTRY}/php:7.4-${APP_ENV}-latest as base
ENV APP_NAME=paella-core
ENV APP_ROOT=/var/www/${APP_NAME}
WORKDIR $APP_ROOT

# >>> builder (installs tools)
FROM base as builder
ENV COMPOSER_VERSION 2.1.8
RUN docker-install-composer && \
    docker-install-php-cs-fixer && \
    docker-install-swagger-cli
ENTRYPOINT []

# >>> nosource
FROM base as nosource

# >>> (default build target, with source code)
FROM base
COPY . $APP_ROOT
