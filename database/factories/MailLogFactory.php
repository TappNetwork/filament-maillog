<?php

namespace Tapp\FilamentMailLog\Database\Factories;

use Tapp\FilamentMailLog\Models\MailLog;
use Illuminate\Database\Eloquent\Factories\Factory;

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
