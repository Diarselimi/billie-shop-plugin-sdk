ARG APP_ENV=dev
ARG APP_DOCKER_REGISTRY
FROM ${APP_DOCKER_REGISTRY}/php:${APP_ENV}-latest

ENV APP_ROOT=/var/www/paella-core
WORKDIR $APP_ROOT

# bcmath
RUN docker-php-ext-install bcmath

# monit
RUN apt-get update && \
    apt-get install -y --no-install-recommends monit && \
    apt-get clean -y && rm -rf /var/lib/apt/lists/*
COPY ./docker/monit/start-stop-script /usr/local/bin/
RUN mkdir /var/run/paellacore && chown www-data:www-data /var/run/paellacore && \
    rm -rf /etc/monit/conf.d/* && \
    rm -rf /etc/monit/conf-available/* && \
    chmod 0755 /usr/local/bin/start-stop-script && \
    ln -s $APP_ROOT/docker/monit/docker-monit-start /usr/local/bin/docker-monit-start
COPY ./docker/monit/monitors /etc/monit/conf-available

#ansible-remove-me#COPY . $APP_ROOT
