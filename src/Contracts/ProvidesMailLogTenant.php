<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Contracts;

interface ProvidesMailLogTenant
{
    /**
     * Return the tenant key that should be stored on the mail log.
     */
    public function mailLogTenantId(): mixed;
}
