# Filament plugin to view outgoing mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)
![GitHub Tests Action Status](https://github.com/TappNetwork/filament-maillog/actions/workflows/run-tests.yml/badge.svg)
![GitHub Code Style Action Status](https://github.com/TappNetwork/filament-maillog/actions/workflows/fix-php-code-style-issues.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)

This plugin adds an event listener to log emails sent on `mail_logs` database table. It also adds a Filament resource to view the mail logs.

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
    'amazon-ses' => [
        'configuration-set' => null,
    ],

    'resources' => [
        'MaiLogResource' => \Tapp\FilamentMailLog\Resources\MailLogResource::class,
    ],

    'navigation' => [
        'maillog' => [
            'register' => true,
            'sort' => 1,
            'icon' => 'heroicon-o-rectangle-stack',
        ],
    ],

    'sort' => [
        'column' => 'created_at',
        'direction' => 'desc',
    ],
];
```

Optionally, you can publish the translations files with:

```bash
php artisan vendor:publish --tag="filament-maillog-translations"
```

## Using the Resource

Add this plugin to a panel on `plugins()` method. 
E.g. in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Tapp\FilamentMailLog\FilamentMailLogPlugin;
 
public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentMailLogPlugin::make(),
            //...
        ]);
}
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
