<?php

namespace Tapp\FilamentMailLog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tapp\FilamentMailLog\Models\MailLog;

class MailLogFactory extends Factory
{
    protected $model = MailLog::class;

    public function definition()
    {
        return [
            'from' => fake()->email(),
            'to' => fake()->email(),
            'subject' => 'test email',
            'body' => fake()->paragraphs(3, asText: true),
        ];
    }
}
