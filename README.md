# stripe2mysql v0.0

## Overview
A PHP script to use the stripe API to get all the members for a subscription and put them into a MySQL DB

## Version History

v0.0 - There's no code yet! just a readme.md

## Requirements

In order to run this you need to have:

* [PHP CLI](https://secure.php.net/manual/en/features.commandline.php) >= 5.4
* [Stripe SDK](https://github.com/stripe/stripe-php/releases) >= 3.5.0
* [MySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/), versoin shouldn't really matter

### Optional

but highly recommended:

* [run-one](https://launchpad.net/ubuntu/+source/run-one) for avoiding lock files/PIDs.
 
## Setup

1. Clone this repo
1. Run ``php -i stripe2mysql.php -verify`` to verify your script is all set
1. Run ``php -i stripe2mysql.php -initialize`` to create the DB
1. Run ``php -i stripe2mysql.php`` (implicitly runs ``-populate``) to populate the DB for the first time

Beyond that, you should use a cron job with ``run-one php -i stripe2mysql.php`` to ensure only one instance of the script is running.

## DB Schema

This is just a guess, need to flesh out!

| name        | type| other|
| ----------- |---------| -----|
| stripe_id| int(11)|unique, not null|
| username| varchar(255)|not null|
| email| varchar(255)|not null|
| full name| varchar(255)|not null|



