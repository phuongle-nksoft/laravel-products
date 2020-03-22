## Installation

Run the command below to install via Composer

```shell
composer require nksoft/laravel-master
```

## Getting Started

### Add config file config/app.php

```php
<?php
'providers' => [
    ...
    Nksoft\Products\NkSoftProductsServiceProvider::class
]
```

### Migrate package

```shell
php artisan migrate
```

### DB seeder package

```shell
php artisan db:seed --class="\Nksoft\Products\database\seeds\NksoftProductsSeeder"
```

### Publishes file

```shell
php artisan vendor:publish --tag=nksoft --force
```

### Syslink storage file

```shell
php artisan storage:link
```

## License

Released under the MIT License attached with this code.
