ARG APP_ENV=dev
ARG APP_DOCKER_REGISTRY
FROM ${APP_DOCKER_REGISTRY}/php:7.3-${APP_ENV}-latest

ENV APP_ROOT=/var/www/paella-core
ENV APP_NAME=paella-core
WORKDIR $APP_ROOT

# bcmath
RUN docker-php-ext-install bcmath

#ansible-remove-me#COPY . $APP_ROOT
