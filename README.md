# Test application based on the book 'WEB DEVELOPMENT WITH PHALCON PHP'.

Skeleton application using  [Phalcon framework](https://phalcon.io).

## Requirements

- PHP >= 7.4.1
- Phalcon >= 5.0.0

## Installing via Git

```bash
cd my-project
git clone --depth=1 https://github.com/bfgnetbook/phalconbook .
composer install
```
## Config database and execute migrations

- Open /path_myproject/app/config/config.php and enter your database connection details
- excute command line: /path_myproject/vendor/bin/phalcon-migrations run

## Test users

- ROL: employed:

username: user
password: demo

- ROL: manager:

username: admin
password: demo