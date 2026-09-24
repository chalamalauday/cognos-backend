FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite headers
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini
ENV PORT=10000
WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir -p /var/www/html/backend/uploads/id_cards && \
    chown -R www-data:www-data /var/www/html/backend/uploads && \
    chmod -R 775 /var/www/html/backend/uploads
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf
EXPOSE 10000
CMD ["apache2-foreground"]
