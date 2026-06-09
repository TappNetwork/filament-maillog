<?php

use Tapp\FilamentMailLog\Resources\MailLogResource;

return [
    'amazon-ses' => [
        'configuration-set' => null,
    ],

    'resources' => [
        'MailLogResource' => MailLogResource::class,
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
        'model' => null,
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
