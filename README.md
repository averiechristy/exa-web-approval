# Exa Web Approval

## Table of Contents

* [Tech Stack](#tech-stack)
* [Requirements](#requirements)
* [PHP Extensions Required](#php-extensions-required)
* [Installation](#installation)
* [Usage](#usage)
* [Queue & Background Jobs](#queue--background-jobs)
* [Useful Commands](#useful-commands)

---

## Tech Stack

* Language: PHP
* Framework: Laravel
* Frontend UI: Bootstrap 5
* Database: PostgreSQL

---

## Requirements

* PHP >= 8.2
* Composer >= 2.x
* PostgreSQL >= 14

---

## PHP Extensions Required

Ensure the following PHP extensions are enabled in your `php.ini` file:

extension=pdo_pgsql
extension=pgsql
extension=openssl
extension=mbstring
extension=fileinfo
extension=curl

---

## Installation

1. Clone repository
   git clone https://github.com/username/exa-web-approval.git
   cd exa-web-approval

2. Install PHP dependencies
   composer install

3. Copy .env.example to .env
   cp .env.example .env

4. Generate application key
   php artisan key:generate

5. Configure database information

   Configure database and mail credentials in your .env file.  
   Make sure to update DB_USERNAME and DB_PASSWORD based on your local PostgreSQL setup.

   APP_NAME="Exa Web Approval"
   APP_URL=http://exa-web-approval.test

   # Database Configuration
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=exa_web_approval
   DB_USERNAME=postgres
   DB_PASSWORD=your_postgres_password

   # Queue & Cache Configuration
   QUEUE_CONNECTION=database
   CACHE_STORE=database

   # Mail Configuration (Gmail SMTP)
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your_email@gmail.com
   MAIL_PASSWORD=your_app_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your_email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"

6. Migrate and seed database

   Ensure the PostgreSQL database exa_web_approval exists before running:
   php artisan migrate --seed

7. Storage link (Optional)

   Create symbolic link for file storage if handling file uploads:
   php artisan storage:link

---

## Usage

For development, run application using following command:

php artisan serve

After running the command, open the following URL in your browser:  
http://127.0.0.1:8000

*Note: You can specify a custom port if needed using php artisan serve --port=8000.*

---

## Queue & Background Jobs

Since QUEUE_CONNECTION=database is configured, you must run the queue worker to process background jobs (such as email notifications):

php artisan queue:work

---

## Useful Commands

* Clear application cache & configuration:
  php artisan optimize:clear

* Verify installed PostgreSQL PHP extensions:
  php -m | grep pgsql