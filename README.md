# Spectrum Oversee

Laravel 5.7

## Requirements

- PHP 7
- NodeJs
- NPM
- Composer

## Setup
1. Clone repository
2. Copy `.env.example` and save it as `.env`
3. Create empty database and copy it's details to the `.env` file
4. (OPTIONAL) Update the env file with these values to allow sending of email through gmail smtp
    - MAIL_DRIVER=smtp
    - MAIL_HOST=smtp.gmail.com
    - MAIL_PORT=587
    - MAIL_USERNAME = {YOUR GMAIL USERNAME}
    - MAIL_PASSWORD = {YOUR GMAIL PASSWORD}
    - MAIL_ENCRYPTION = tls
5. `cd` into the project folder and run `bash install_dev`
6. run `php artisan serve`
7. login using these credentials (admin@mail.com/password)
