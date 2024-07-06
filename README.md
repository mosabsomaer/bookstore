First i want to thank you for giving me the extra time submit my project. 
I have spent these 2 days to build this backend from scratch up as i mentioned in email i had busy week, 
While building this system i made sure to follow requirements and rules one of which is to use built in
laravel tools so i made sure to use laravel sanctum for authentication i have made multiple attempt to deploy backend 
to cloud enviroment but AWS and GCP required Visa which sadly mine is expired and i havent had the chance to renew it, 
i was faced with dead end, but i had my way around this where i used ngrok to run server on my machine and have domain from ngrok 
and that way i got local environment server as per the requirement choices.

To test and run the backend:

- change .env.example to .env
- composer install
- php artisan key:generate
- composer update
- paste this in .env at the bottom of the file
ADMIN_EMAIL="admin@admin.com"
ADMIN_PASSWORD="adminpassword"
- php artisan migrate --seed
- php artisan serve -vvv or php artisan serve

You can use admin@admin.com and adminpassword as your email and password to login as admin using route auth/login


Api test endpoints
A postman export file is located in the repository by name bookstore.postman_collection.json


To start docker container
- docker-compose up -d --build
- docker ps

To stop container 
- docker-compose down