FROM php:8.3.11-cli-bookworm

ARG USERID

ENV TZ=Europe/Prague
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get -y update && apt-get -y autoclean && apt-get install -y \
  git \
  curl \
  nano \
  zip \
  unzip \
  openssl \
  libicu-dev \
  icu-devtools \
  iputils-ping

# Install Redis
RUN pecl install -o -f redis && docker-php-ext-enable redis

# Install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install stryng types validation methodes for better symfony performence
RUN docker-php-ext-install ctype

# Install postgres and mysql drivers
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql

COPY --from=composer:2.7.6 /usr/bin/composer /usr/local/bin/composer

# Download symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install -y symfony-cli

RUN adduser --system --home /home/quizapp --uid $USERID --disabled-password --group quizapp

# as the UID and GID might have changed, change the ownership of the home directory workdir again
RUN chown -R quizapp:quizapp /home/quizapp

USER quizapp

WORKDIR /app