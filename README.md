# stripe2mysql v0.0

## Overview
A PHP script to use the stripe API to get all the members for a subscription and put them into a MySQL DB

## Version History

v0.0 - There's no code yet! just a readme.md

## Requirements

In order to run this you need to have:

* [PHP CLI](https://secure.php.net/manual/en/features.commandline.php) >= 5.4 - with [PDO](https://secure.php.net/manual/en/pdo.installation.php)
* [Stripe SDK](https://github.com/stripe/stripe-php/releases) >= 3.5.0
* [MySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/), versoin shouldn't really matter

### Optional

but highly recommended:

* [run-one](https://launchpad.net/ubuntu/+source/run-one) for avoiding lock files/PIDs.
 
## Setup

1. Clone this repo
1. copy config.dist.php to config.php 
1. edit config.php to have your specific ``api_path``, ``mysql_db``, ``mysql_user``, ``mysql_password``, ``mysql_port`` and ``stripe_api_key`` values
1. Run ``php -i stripe2mysql.php`` to populate the DB for the first time with your users.  you'll get errors if your environment is not set up correctly

Beyond that, you should use a cron job with run-one like this``run-one php -i stripe2mysql.php`` to ensure only one instance of the script is running on subsequent updates.

## DB Schema

This is just a guess, need to flesh out!

| name        | type| other|
| ----------- |---------| -----|
| id| int(11)|unique, not null, auto incriment|
| stripe_id| int(11)|unique, not null|
| username| varchar(255)|not null|
| email| varchar(255)|not null|
| full name| varchar(255)|not null|



