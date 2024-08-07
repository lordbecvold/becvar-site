#!/bin/bash

# stop apache and clear cache
sudo systemctl stop apache2
sudo sh scripts/clear.sh
                   
# pull the latest changes
git pull

# set the environment to production
sed -i 's/^\(APP_ENV=\)dev/\1prod/' .env

# install dependencies
sh scripts/install.sh

# migrate database
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
                    
# run app commands
php bin/console projects:list:update
php bin/console auth:tokens:regenerate

# set permissions
sudo chmod -R 777 var/
sudo chown -R www-data:www-data var/

# start apache
sudo systemctl start apache2
