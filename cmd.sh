#! /bin/bash
cd /var/www/pimcore_app/var
ls -al
cd /var/www/pimcore_app

./vendor/bin/pimcore-install --admin-username=admin --admin-password=admin --mysql-username=root --mysql-password=root --mysql-database=pimcore_learning_database --mysql-host-socket=db --mysql-port=3306 --no-interaction

cd /var/www/pimcore_app/var
ls -al
cd /var/www/pimcore_app
chown -R www-data:www-data /var/www/pimcore_app/var/cache
php bin/console assets:install public

apache2-foreground

