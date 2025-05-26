FROM php:8.2-fpm

ARG user
ARG uid

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

# Install app from apt
RUN apt-get update && apt-get install -y  \
    supervisor \
    nodejs  \
    yarn  \
    git  \
    unzip  \
    wget  \
    nano  \
    ssh-client

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install php extensions
RUN apt-get install -y --no-install-recommends \
    libssl-dev  \
    zlib1g-dev  \
    libxml2-dev  \
    libpq-dev  \
    libzip-dev  \
    libpng-dev  \
    libjpeg-dev  \
    libfreetype-dev  \
    libmagickwand-dev \
    libz-dev  \
    libsodium-dev  \
    libmemcached-dev \
    nodejs \
    npm

RUN docker-php-ext-install sockets

# Clear apt list and temp files
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install pecl dependency
RUN pecl install xdebug &&  \
    pecl install imagick -o -f &&  \
    pecl install apcu

RUN pecl install redis && docker-php-ext-enable redis

# Configure php dependency
RUN docker-php-ext-configure soap &&  \
    docker-php-ext-configure gd --with-freetype --with-jpeg

# Install php dependencies
RUN docker-php-ext-install -j$(nproc)  \
    pdo_mysql  \
    pdo_pgsql  \
    mysqli  \
    gd  \
    sodium  \
    pgsql  \
    intl  \
    opcache  \
    soap \
    zip \
    sysvsem

# Enable php dependencies
RUN docker-php-ext-enable imagick &&  \
    docker-php-ext-enable apcu

RUN docker-php-ext-install calendar

RUN apt-get update && \
    apt-get install -y libyaml-dev && \
    pecl install yaml && \
    docker-php-ext-enable yaml

# Install required dependencies
RUN apt-get update && apt-get install -y build-essential wget

RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Add configs
ADD configs/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www

USER ${uid}

EXPOSE 9000