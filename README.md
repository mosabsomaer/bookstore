demo app

To test and run the backend:
- change .env.example to .env
- composer install
- php artisan key:generate
- composer update
- paste this in .env at the bottom of the file
- ADMIN_EMAIL="admin@admin.com"
- ADMIN_PASSWORD="adminpassword"
- php artisan migrate --seed
- php artisan serve -vvv or php artisan serve
-
- You can use admin@admin.com and adminpassword as your email and password to login as admin using route auth/loginApi

Test endpoints
- A postman export file is located in the repository by name bookstore.postman_collection.json
-
To start docker container
docker-compose up -d --build
docker ps

To stop container
docker-compose down
