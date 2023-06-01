FROM php:8.1-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    git \
    && rm -r /var/lib/apt/lists/*

# Installer les extensions PHP requises
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configurer PHP-FPM
COPY docker/php-fpm/php-ini-overrides.ini /usr/local/etc/php/php.ini

# Copier les fichiers de l'application Symfony dans le conteneur
COPY . /application

# Changer le propriétaire des fichiers
RUN chown -R www-data:www-data /application

# Installer les dépendances de l'application Symfony
RUN cd /application && composer install --no-scripts --no-interaction

# Définir le répertoire de travail
WORKDIR /application

# Commande de sortie (pour exécuter le container)
CMD ["php-fpm"]