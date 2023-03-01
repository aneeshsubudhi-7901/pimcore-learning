FROM pimcore/pimcore:PHP8.0-apache
RUN apt update;\
    apt install nano
WORKDIR /var/www/pimcore_app
COPY composer.lock ./
COPY composer.json ./
RUN composer update
RUN composer install
COPY . .
# RUN php bin/console cache:clear --no-warmup --env=prod;\
#     php bin/console cache:clear --no-warmup --env=dev;\
#     php bin/console pimcore:cache:clear
RUN php bin/console cache:clear;\
    php bin/console pimcore:cache:clear

RUN chown -R www-data:www-data /var/www/pimcore_app
#asset install
# RUN php bin/console assets:install public 

RUN cp pimcore_app.conf /etc/apache2/sites-available/pimcore_app.conf
WORKDIR /etc/apache2/sites-available
RUN service apache2 start;\
    a2ensite pimcore_app.conf;\
    service apache2 reload;\
    a2dissite 000-default.conf;\
    service apache2 reload;\
    apache2ctl configtest;\
    service apache2 reload;\
    service apache2 status
WORKDIR /var/www/pimcore_app
EXPOSE 80
# CMD ["apache2-foreground"]

# RUN ./vendor/bin/pimcore-install --admin-username=admin --admin-password=admin --mysql-username=root --mysql-password=root --mysql-database=pimcore_learning_database --mysql-host-socket=0.0.0.0 --mysql-port=3306 --no-interaction

# CMD ["./vendor/bin/pimcore-install", "--admin-username=admin", "--admin-password=admin", "--mysql-username=root", "--mysql-password=root", "--mysql-database=pimcore_learning_database", "--mysql-host-socket=db", "--mysql-port=3306", "--no-interaction;","apache2-foreground"]

# CMD ["apache2-foreground"]

CMD ["/bin/bash","-c","./cmd.sh"]
