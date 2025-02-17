FROM ubuntu:20.04
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kiev

RUN apt-get update \
  && apt-get remove -y php8.1-common php8.1-fpm php8.1-cli php8.1-* || true \
  && apt-get autoremove -y \
  && apt-get install -y software-properties-common \
  && add-apt-repository ppa:ondrej/php -y \
  && apt-get update && apt-get install -y \
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
      cron \
      logrotate

RUN mkdir -p /run/php && chown -R www-data:www-data /run/php \
  && sed -i 's|^pid = .*$|pid = /run/php/php8.2-fpm.pid|' /etc/php/8.2/fpm/php-fpm.conf \
  && sed -i 's|^listen = .*$|listen = 127.0.0.1:9000|' /etc/php/8.2/fpm/pool.d/www.conf \
  && php-fpm8.2 -t

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs

WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest \
  && npm install \
  && npm run build \
  && php artisan key:generate || true \
  && php artisan storage:link || true \
  && php artisan vendor:publish --force --tag=livewire:assets

COPY nginx/laravel.conf /etc/nginx/sites-available/laravel.conf

RUN rm /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/laravel.conf

RUN cat <<EOF > /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true

[program:php-fpm]
command=/usr/sbin/php-fpm8.2 -F
autostart=true
autorestart=true
redirect_stderr=false
stdout_logfile=/var/log/supervisor/php-fpm.log
stderr_logfile=/var/log/supervisor/php-fpm.err

[program:nginx]
command=nginx -g 'daemon off;'
autostart=true
autorestart=true
redirect_stderr=false
stdout_logfile=/var/log/supervisor/nginx.log
stderr_logfile=/var/log/supervisor/nginx.err

[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --tries=3 --timeout=90 --verbose
autostart=true
autorestart=true
numprocs=1
redirect_stderr=false
stdout_logfile=/var/log/supervisor/laravel-queue.log
stderr_logfile=/var/log/supervisor/laravel-queue.err

[program:cron]
command=cron -f
autostart=true
autorestart=true
redirect_stderr=false
stdout_logfile=/var/log/supervisor/cron.log
stderr_logfile=/var/log/supervisor/cron.err
EOF

RUN echo "* * * * * cd /var/www/html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
