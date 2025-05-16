# Laravel + Vue finance data demo

![preview](./preview.png?raw=true "Preview")

You can try static demo [here](https://ggtachev.github.io/laravel/staticdemo/demo.html).

## Requirements
This app requires PHP 8.3+ and NPM 18+

## Installation

1. Clone
```
git clone git@github.com:ggtachev/laravel.git
cd laravel
```

2. Copy example environment file

```
cp .env.example .env
```

update .env DB_DATABASE=./database/database.sqlite if needed

3. Install with composer

If you don't have it locally download composer
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

then 
```
php composer.phar install
```

or if you have it globally just run
```
composer install
```


4. Install NPM dependencies and build assets
```
npm install
npm run build
```

5. Run local server 
```
php artisan serve
```

You can see the app on http://127.0.0.1:8000/