# Filament plugin to view outgoing mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)
![GitHub Tests Action Status](https://github.com/TappNetwork/filament-maillog/actions/workflows/run-tests.yml/badge.svg)
![GitHub Code Style Action Status](https://github.com/TappNetwork/filament-maillog/actions/workflows/fix-php-code-style-issues.yml/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp/filament-maillog.svg?style=flat-square)](https://packagist.org/packages/tapp/filament-maillog)

This plugin adds an event listener to log emails sent on `mail_logs` database table. It also adds a Filament resource to view the mail logs.

## Version Compatibility

| Filament | Filament MailLog | Documentation
:---------|:-----------------|:--------------
| 4.x/5.x | 2.x              | Current
| 3.x     | 1.x              | [Check the docs](https://github.com/TappNetwork/filament-maillog/tree/1.x)

## Installation

You can install the package via Composer:

```bash
composer require tapp/filament-maillog:"^2.0"
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
        'MailLogResource' => \Tapp\FilamentMailLog\Resources\MailLogResource::class,
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

    'tenancy' => [
        'enabled' => env('FILAMENT_MAILLOG_TENANCY_ENABLED', false),
        'model' => null, // e.g. \App\Models\Team::class
        'relationship_name' => env('FILAMENT_MAILLOG_TENANCY_RELATIONSHIP_NAME', null),
        'column' => env('FILAMENT_MAILLOG_TENANCY_COLUMN', null),
        'nullable' => env('FILAMENT_MAILLOG_TENANCY_NULLABLE', true),
        'foreign_key' => [
            'on_delete' => env('FILAMENT_MAILLOG_TENANCY_ON_DELETE', 'cascade'),
            'on_update' => env('FILAMENT_MAILLOG_TENANCY_ON_UPDATE', 'cascade'),
        ],
        'auto_assign' => env('FILAMENT_MAILLOG_TENANCY_AUTO_ASSIGN', true),
    ],
];
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-maillog-migrations"
php artisan migrate
```

> **Warning:** If you use multi-tenancy, configure tenancy **before** publishing and running migrations. See "Multi-Tenancy Support" below.

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

## Multi-Tenancy Support

Mail log entries can be scoped to a tenant (e.g. team or organization) when your Filament panel uses tenancy.

### Setup

1. **Configure tenancy before migrations**

   Publish the config and set in `config/filament-maillog.php`:

   ```php
   'tenancy' => [
       'enabled' => true,
       'model' => \App\Models\Team::class,
       'relationship_name' => 'team',
       'column' => 'team_id',
       'nullable' => true,
       'auto_assign' => true,
   ],
   ```

   Or use env vars: `FILAMENT_MAILLOG_TENANCY_ENABLED=true`, `FILAMENT_MAILLOG_TENANCY_COLUMN=team_id`, etc.

2. **Publish and run migrations**

   The `mail_logs` table will get the tenant foreign key when tenancy is enabled:

   ```bash
   php artisan vendor:publish --tag="filament-maillog-migrations"
   php artisan migrate
   ```

3. **Panel**

   Ensure your panel uses the same tenant model, e.g. `->tenant(\App\Models\Team::class)`.

When tenancy is enabled, the resource is scoped to the current tenant and new mail logs are associated with the current tenant.
The current Filament tenant is captured into Laravel's context so queued notifications still get a tenant id after the HTTP request ends.

Notifications can also provide a tenant by implementing `Tapp\FilamentMailLog\Contracts\ProvidesMailLogTenant`, or by exposing a public property that is an instance of the configured tenant model.

The tenant column is nullable by default so emails sent before a tenant context exists, such as registration or verification messages, can still be logged.

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
