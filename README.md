open xampp
Create new db task-management-app
start apache and mysql
clone git repo in htdoc 


# Setting Up Task Management App

1. **Clone the Repository**
    ```bash
    git clone https://github.com/santosh97/task-management-app.git
    ```

2. **Navigate to Your Project Directory**
    ```bash
    cd task-management-app
    ```

3. **Install Composer Dependencies**
    ```bash
    composer install
    ```

4. **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5. **Seed the Database**
    ```bash
    php artisan db:seed
    ```

6. **Create Authentication Views and Routes**
    ```bash
    php artisan make:auth
    ```

7. **Install Laravel Sanctum**
    ```bash
    php artisan sanctum:install
    ```

8. **Start the Laravel Development Server**
    ```bash
    php artisan serve
    ```

Now you have completed the steps to set up the Task Management App. Follow any additional instructions or requirements mentioned in the project's documentation. Access your application by visiting [http://localhost:8000](http://localhost:8000) in your web browser.