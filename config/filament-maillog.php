<?php

use Tapp\FilamentMailLog\Resources\MailLogResource;

return [
    'amazon-ses' => [
        'configuration-set' => null,
    ],

    'resources' => [
        'MaiLogResource' => MailLogResource::class,
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
