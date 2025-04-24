FROM php:8.2-fpm

ENV TZ=Europe/Kiev
ENV DEBIAN_FRONTEND=noninteractive

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
  && echo ${TZ} > /etc/timezone

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
      git \
      curl \
      unzip \
      zip \
      libzip-dev \
      libpng-dev \
      libjpeg-dev \
      libfreetype-dev \
      libonig-dev \
      libicu-dev \
      libpq-dev \
      libxml2-dev \
      libcurl4-openssl-dev \
      supervisor \
      cron \
      nginx \
      bison \
      build-essential \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-source extract \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
      bcmath \
      ctype \
      fileinfo \
      gd \
      mbstring \
      pdo \
      pdo_mysql \
      pdo_pgsql \
      xml \
      zip \
      intl \
      exif \
      curl \
  && docker-php-source delete

RUN curl -sS https://getcomposer.org/installer \
       | php -- --install-dir=/usr/local/bin --filename=composer \
  && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update \
  && apt-get install -y --no-install-recommends nodejs \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
  && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache \
  && composer install --no-dev --prefer-dist --no-progress --no-suggest \
  && npm install \
  && npm run build \
  && php artisan key:generate --force \
  && php artisan storage:link --force \
  && php artisan vendor:publish --force --tag=livewire:assets

# 6) Nginx
COPY nginx/laravel.conf /etc/nginx/sites-available/laravel.conf
RUN rm /etc/nginx/sites-enabled/default \
  && ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/laravel.conf

RUN cat <<EOF > /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true

[program:php-fpm]
; в офіційному php:8.2-fpm бінарник називається php-fpm і лежить у /usr/local/sbin
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/php-fpm.log
stderr_logfile=/var/log/supervisor/php-fpm.err

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/nginx.log
stderr_logfile=/var/log/supervisor/nginx.err

[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --tries=3 --timeout=90 --verbose
autostart=true
autorestart=true
numprocs=1
stdout_logfile=/var/log/supervisor/laravel-queue.log
stderr_logfile=/var/log/supervisor/laravel-queue.err

[program:cron]
command=cron -f
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/cron.log
stderr_logfile=/var/log/supervisor/cron.err
EOF

# 8) Cron для scheduler
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" \
  >> /etc/crontab

# 9) Entrypoint
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
