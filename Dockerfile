FROM php:8.4-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libonig-dev default-mysql-client \
    && docker-php-ext-install mbstring pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json ./
RUN composer update --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=base64:st2FXQB17Q9BmQ1YSRVAF7qh02lXXPHCoFxf5AIfgYg=
ENV APP_URL=http://localhost:3002
ENV PORT=3002
ENV DB_CONNECTION=mysql
ENV DB_HOST=mysql
ENV DB_PORT=3306
ENV DB_DATABASE=service_b
ENV DB_USERNAME=service_b
ENV DB_PASSWORD=service_b_secret
ENV IAE_INTERNAL_KEY=102022400126
ENV IAE_API_KEYS=102022400126

# Integrasi pusat dosen (Tugas 3) - absen 13
ENV IAE_BASE_URL=https://iae-sso.virtualfri.id
ENV IAE_TEAM_ID=TEAM-06
ENV IAE_API_KEY=KEY-MHS-185
ENV IAE_WARGA_EMAIL=warga28@ktp.iae.id
ENV IAE_WARGA_PASSWORD=KtpDigital2026!
ENV LIGHTHOUSE_SCHEMA_CACHE_ENABLE=false

EXPOSE 3002

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
