# Filament plugin to view outgoing mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tapp/filament-maillog/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tapp/filament-maillog/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tapp/filament-maillog/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tapp/filament-maillog/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.


## Installation

You can install the package via composer:

```bash
composer require tapp/filament-maillog
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-maillog-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-maillog-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-maillog-views"
```

## Usage

```php
$FilamentMailLog = new Tapp\FilamentMailLog();
echo $FilamentMailLog->echoPhrase('Hello, Tapp!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Steve Williamson](https://github.com/swilla)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
