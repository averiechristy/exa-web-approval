# EXA WEB APPROVAL

## Table of Contents

* [Tech Stack](#tech-stack)
* [Requirements](#requirements)
* [Installation](#installation)
* [Email Configuration](#email-configuration-gmail-smtp)
* [Usage](#usage)
* [Deployment](#deployment)

---

## Tech Stack

* Language: PHP
* Framework: Laravel 13
* Database: PostgreSQL
* Testing: Pest

---

## Requirements

* PHP >= 8.3
* Composer
* PostgreSQL

---

## Installation

1. Clone repository

2. Install PHP dependencies
    ```bash
    composer install
    ```

3. Copy .env.example to .env

4. Generate key
    ```bash
    php artisan key:generate
    ```

   Link the storage directory:
      ```bash
      php artisan storage:link
      ```

5. Configure database information

    Configure database information .env file.  
    Don't forget to change DB_USERNAME and fill DB_PASSWORD.
     ```env
      DB_CONNECTION=pgsql
      DB_HOST=127.0.0.1
      DB_PORT=5432
      DB_DATABASE=exa_web_approval
      DB_USERNAME=your_username
      DB_PASSWORD=your_password
    ```

6. Migrate database
    
    ```bash
    php artisan migrate
    ```

7. Database Seeding

    After running the migrations, you need to seed the database with initial data.

    There are two steps to complete:

    #### Run Database Seeder
    This command will execute the `DatabaseSeeder` class to populate the base data:

    ```bash
    php artisan db:seed --class=DatabaseSeeder
    ```

---

### Email Configuration (Gmail SMTP)

This application uses Gmail SMTP for sending emails/notifications. Follow the steps below to configure it:

#### 1. Google Account Setup
Before adding the credentials to your `.env` file, ensure the sender's Google account is properly configured:
1. Log in to your google account.
2. **Enable 2-Step Verification** in the account security settings.
3. Open the [Google App Passwords](https://myaccount.google.com/apppasswords) page.
4. Create a new **App Password** (enter the application name, e.g., `EXA WEB APPROVAL`).
5. **Save/Copy the generated 16-digit password.** *This password will be used in `.env`, not your regular Gmail login password.*

#### 2. Configure the `.env` File
Open your `.env` file and update the following variables using the IT Support email and the generated **App Password**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email.itsupport@domain.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email.itsupport@domain.com
MAIL_FROM_NAME="${APP_NAME}"
```
---

## Usage

For development, run application using following command

```bash
php artisan serve
```

After running the command, open following URL:
[http://localhost:8000](http://localhost:8000)


---

---

## Deployment

When deploying this application to a production server, make sure to adjust the `.env` configuration for security and performance:

1. **Environment & Security Configurations:**
   * Set `APP_ENV=production`
   * Set `APP_DEBUG=false` *(Crucial: prevents leaking credentials and stack traces on error)*
   * Set `APP_URL=https://your-domain.com` *(Use your actual production domain with HTTPS)*
   * Run `php artisan key:generate` on the production server.

2. **Optimize Laravel Application:**
   After updating the `.env` file on the production server, run the following commands to cache configuration and routes for maximum performance:

   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache

---