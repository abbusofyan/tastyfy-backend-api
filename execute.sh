ls
sudo su -
cd /www/wwwroot/tasteasia
git config --global --add safe.directory /www/wwwroot/tasteasia
git stash
git pull origin staging


rm -f /usr/bin/php
ln -s /www/server/php/82/bin/php /usr/bin/php
composer install
php artisan optimize:clear
php artisan storage:link
php artisan migrate
php artisan l5-swagger:generate
# Insert chmod and chown commands here
chmod 754 storage/api-docs/api-docs.json
chown -R www:www storage/api-docs
source /www/server/nvm/nvm.sh
nvm use 20.10.0
npm install
npm run build
exit
