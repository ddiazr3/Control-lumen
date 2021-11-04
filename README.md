# Lumen PHP Framework

PARA LA CRACION DEL STORAGE LINK
https://stackoverflow.com/questions/47772360/how-to-create-symlink-for-storage-public-folder-for-lumen

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Contributing

Thank you for considering contributing to Lumen! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


Problem 1
- phpoffice/phpspreadsheet is locked to version 1.18.0 and an update of this package was not requested.
- phpoffice/phpspreadsheet 1.18.0 requires ext-gd * -> it is missing from your system. Install or enable PHP's gd extension.
Problem 2
- phpoffice/phpspreadsheet 1.18.0 requires ext-gd * -> it is missing from your system. Install or enable PHP's gd extension.
- maatwebsite/excel 3.1.33 requires phpoffice/phpspreadsheet ^1.18 -> satisfiable by phpoffice/phpspreadsheet[1.18.0].
- maatwebsite/excel is locked to version 3.1.33 and an update of this package was not requested.

To enable extensions, verify that they are enabled in your .ini files:
-
- /usr/local/etc/php/conf.d/docker-php-ext-pcntl.ini
- /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini
- /usr/local/etc/php/conf.d/docker-php-ext-soap.ini
- /usr/local/etc/php/conf.d/docker-php-ext-sodium.ini
- /usr/local/etc/php/conf.d/limits.ini
You can also run `php --ini` inside terminal to see which files are used by PHP in CLI mode.
root@23e1c215cbed:/var/www# php artisan db:seed 
