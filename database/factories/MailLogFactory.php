<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tapp\FilamentMailLog\Models\MailLog;

class MailLogFactory extends Factory
{
    protected $model = MailLog::class;

    public function definition(): array
    {
        $definition = [
            'from' => fake()->email(),
            'to' => fake()->email(),
            'subject' => 'test email',
            'body' => fake()->paragraphs(3, asText: true),
        ];

        if (config('filament-maillog.tenancy.enabled', false)) {
            $tenantColumn = MailLog::getTenantColumnName();
            $definition[$tenantColumn] = null;
        }

        return $definition;
    }

    public function forTenant(object|int $tenant): static
    {
        if (! config('filament-maillog.tenancy.enabled', false)) {
            return $this;
        }

        $tenantColumn = MailLog::getTenantColumnName();

        return $this->state(fn (array $attributes) => [
            $tenantColumn => is_object($tenant) ? $tenant->getKey() : $tenant,
        ]);
    }
}
