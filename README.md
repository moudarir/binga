![Binga logo](http://binga.ma/img/images/logo.png)

# PHP Library for Binga.ma
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://github.com/moudarir/binga/blob/master/LICENSE) 

PHP library for integration with the Binga.ma API (v1.1) in a fast and simple way.

## Requirements
PHP => 7.2+  
Composer

## Installation
> **NOTE:** this version 1.0.* requires php 7.2+ ([php supported versions](http://php.net/supported-versions.php))

The best way to install the library is to use [Composer](https://getcomposer.org/)

If you haven't started using composer, I highly recommend you to use it.

In your `composer.json` file located at the root of your project, add the following code: 

    {
        "require": {
           "moudarir/binga": "^1.0"
        }
    }

And then run: 

```
composer install
```

Or just run : 

```
composer require moudarir/binga
```

Add the autoloader to your project:

```php
    <?php

    require_once 'vendor/autoload.php';
```

You're now ready to begin using the library.

## Examples

## License
Copyright (c) 2018 Abdessamad MOUDARIR, released under [the MIT License](https://github.com/moudarir/binga/blob/master/LICENSE)

#### TODO
- Completing Documentation.
- Adding Some Examples.