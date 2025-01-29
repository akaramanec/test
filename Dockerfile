FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kiev

RUN apt-get update \
  && apt-get remove -y php8.1-common php8.1-fpm php8.1-cli php8.1-* || true \
  && apt-get autoremove -y

RUN apt-get install -y software-properties-common
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get update && apt-get install -y \
    php8.2-fpm \
    php8.2-bcmath \
    php8.2-ctype \
    php8.2-fileinfo \
    php8.2-mbstring \
    php8.2-pdo \
    php8.2-pgsql \
    php8.2-mysql \
    php8.2-tokenizer \
    php8.2-xml \
    php8.2-gd \
    php8.2-exif \
    php8.2-curl \
    php8.2-zip \
    php8.2-intl \
    curl \
    unzip \
    git \
    nginx \
    supervisor \
    cron

RUN mkdir -p /run/php && chown -R www-data:www-data /run/php
RUN sed -i 's|^pid = .*$|pid = /run/php/php8.2-fpm.pid|' /etc/php/8.2/fpm/php-fpm.conf
RUN sed -i 's|^listen = .*$|listen = 127.0.0.1:9000|' /etc/php/8.2/fpm/pool.d/www.conf
RUN php-fpm8.2 -t

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest
RUN npm install && npm run build
RUN php artisan key:generate || true
RUN php artisan storage:link || true

RUN php artisan vendor:publish --force --tag=livewire:assets


RUN rm /etc/nginx/sites-enabled/default && \
    echo "server { listen 80; root /var/www/html/public; index index.php; location / { try_files \$uri \$uri/ /index.php?\$query_string; } location ~ \\.php\$ { include snippets/fastcgi-php.conf; fastcgi_pass 127.0.0.1:9000; } }" \
    > /etc/nginx/sites-available/laravel.conf && \
    ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/laravel.conf

RUN echo "[supervisord]\nnodaemon=true\n\n[program:php-fpm]\ncommand=/usr/sbin/php-fpm8.2 -F\nautostart=true\nredirect_stderr=true\n\n[program:nginx]\ncommand=nginx -g 'daemon off;'\nautostart=true\nredirect_stderr=true" \
> /etc/supervisor/conf.d/supervisord.conf

RUN echo "* * * * * cd /var/www/html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
