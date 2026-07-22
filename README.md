# EXA WEB APPROVAL

## Table of Contents

* [Tech Stack](#tech-stack)
* [Requirements](#requirements)
* [Installation](#installation)
* [Email Configuration](#email-configuration-gmail-smtp)
* [Usage](#usage)

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


## Usage

For development, run application using following command

```bash
php artisan serve
```

After running the command, open following URL:
[http://localhost:8000](http://localhost:8000)


---

