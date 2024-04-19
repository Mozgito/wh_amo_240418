FROM php:8.2.18-cli

RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends apt-utils\
    git \
    libpq-dev \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    libfreetype6-dev \
    libz-dev \
    libxpm-dev \
    libssl-dev \
    libbson-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && chmod a+x /usr/bin/composer

RUN apt-get clean