open xampp
Create new db task-management-app
start apache and mysql
clone git repo in htdoc 


git clone https://github.com/santosh97/task-management-app.git
cd task-management-app 
composer install
php artisan key:generate
php artisan db:seed
php artisan make:auth
php artisan sanctum:install
php artisan serve
