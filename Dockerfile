FROM yiisoftware/yii2-php:8.5-apache-latest

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /app/runtime /app/web/assets \
    && chmod -R ug+rwX /app/runtime /app/web/assets

EXPOSE 80