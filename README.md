# Test application based on the book 'WEB DEVELOPMENT WITH PHALCON PHP'.

Skeleton application using  [Phalcon framework](https://phalcon.io).

## Requirements

- PHP >= 7.4.1
- Phalcon >= 5.0.0

## Installing via Git

```bash
git clone --depth=1 https://github.com/bfgnetbook/phalconbook my-project
rm -rf ./my-project/.git
find ./my-project -type f -name "*.gitkeep" -exec rm -f {} \;
```

Once installed, you can test it out immediately using PHP's built-in web server:

```bash
cd my-project
php -S 0.0.0.0:8000 -t public
```
