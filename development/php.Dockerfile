FROM php:8.1-cli

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
            libzip-dev \
            libonig-dev \
            unzip \
            libxml2-dev \
            libssl-dev && \
    docker-php-ext-install \
            zip \
            mbstring && \
    docker-php-source delete && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# create a guest user
ARG user=guest
ARG group=guests
ARG uid=1000
ARG gid=1000
RUN groupadd -g ${gid} ${group} && \
    useradd -u ${uid} -g ${group} -s /bin/sh -m ${user}

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=0 \
    PHP_USER_ID=33 \
    COMPOSER_HOME=/home/${user}/.composer \
    PATH=/opt/project:/opt/project/vendor/bin:/home/${user}/.composer/vendor/bin:$PATH

WORKDIR /opt/project

# Switch to user
USER ${uid}
