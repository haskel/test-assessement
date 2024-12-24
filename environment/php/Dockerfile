# Dockerfile for development environment
FROM php:8.3

ARG TIMEZONE

RUN apt-get update && apt-get install --no-install-recommends -y \
    openssl \
    git \
    unzip \
    curl \
    zsh \
    wget \
    procps \
    net-tools \
    iproute2 \
    xauth \
    iputils-ping \
    telnet \
    libpq-dev

RUN docker-php-ext-install pcntl && docker-php-ext-enable pcntl; \
    docker-php-ext-install pdo pdo_mysql && docker-php-ext-enable pdo pdo_mysql; \
    # Install xdebug
    pecl install xdebug && docker-php-ext-enable xdebug \
    && rm -rf /tmp/pear \
    # Set timezone
    rm /etc/localtime; \
    ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install Oh My Zsh
RUN wget https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh -O - | zsh || true

ENV TERM xterm-256color

WORKDIR /var/www/app

CMD ["tail", "-f", "/dev/null"]
