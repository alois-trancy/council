# Council [![Build Status](https://travis-ci.org/alois-trancy/council.svg?branch=master)](https://travis-ci.org/alois-trancy/council)

This is an open source forum that was built and maintained at Laracasts.com.

## Installation

### Prerequisites

* To run this project, you must have PHP 7 installed.
* You should setup a host on your web server for your local domain. For this you could also configure Laravel Homestead or Valet. 
* If you want use Redis as your cache driver you need to install the Redis Server. You can either use homebrew on a Mac or compile from source (https://redis.io/topics/quickstart). 

### Step 1.

> To run this project, you must have PHP 7 installed as a prerequisite.
Begin by cloning this repository to your machine, and installing all Composer dependencies.

```bash
git clone https://github.com/alois-trancy/council.git
cd council && composer install
php artisan key:generate
mv .env.example .env
```

### Step 2.

Next, create a new database and reference its name and username/password within the project's `.env` file. In the example below, we've named the database, "council."

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=council
DB_USERNAME=root
DB_PASSWORD=
```

Then, migrate your database to create tables.

```
php artisan migrate
```

### Step 3.

Until an administration portal is available, manually insert any number of "channels" (think of these as forum categories) into the "channels" table in your database.

Once finished, clear your server cache, and you're all set to go!

```
php artisan cache:clear
```

### Step 5.

Use your forum! Visit `http://council.test/threads` to create a new account and publish your first thread.