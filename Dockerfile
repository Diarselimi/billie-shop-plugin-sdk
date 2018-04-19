FROM php:7.2-fpm

# Apt caching proxy
ARG APT_PROXY
RUN if [ -n "$APT_PROXY" ]; then \
      echo "Acquire::HTTP::Proxy \"${APT_PROXY}\";" >> /etc/apt/apt.conf.d/01proxy && \
      echo 'Acquire::HTTPS::Proxy "false";' >> /etc/apt/apt.conf.d/01proxy; \
    fi

# procps
RUN apt-get update \
    && apt-get install -y procps \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# pdo
RUN docker-php-ext-install pdo pdo_mysql

# Application environment
ARG APP_ENV
ENV APP_ROOT=/var/www/paella-core  \
		APP_ENV=${APP_ENV:-dev}
ENV PHP_USER_ID=33

RUN ln -sfn "$APP_ROOT/docker/bin/docker-php-entrypoint" /usr/local/bin/docker-php-entrypoint

# timecop
ENV PHPTIMECOP_VERSION 1.2.10
RUN if [ $APP_ENV = 'dev' ]; then \
    pecl install timecop-$PHPTIMECOP_VERSION \
    && docker-php-ext-enable timecop \
    && rm -rf /tmp/pear \
    ; else echo 'skipped'; fi

# sockets
RUN if [ $APP_ENV = 'dev' ]; then \
    docker-php-ext-install sockets \
    ; else echo 'skipped'; fi

#ansible-remove-me#COPY . $APP_ROOT

WORKDIR $APP_ROOT
