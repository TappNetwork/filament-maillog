<?php

use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Resources\MailLogResource;
use Tapp\FilamentMailLog\Tests\Fixtures\Tenant;

beforeEach(function (): void {
    Filament::setTenant(null);

    Schema::dropIfExists('mail_logs');
    Schema::dropIfExists('tenants');

    config()->set('filament-maillog.tenancy.enabled', true);
    config()->set('filament-maillog.tenancy.model', Tenant::class);
    config()->set('filament-maillog.tenancy.relationship_name', null);
    config()->set('filament-maillog.tenancy.column', 'tenant_id');
    config()->set('filament-maillog.tenancy.nullable', true);
    config()->set('filament-maillog.tenancy.foreign_key.on_delete', 'cascade');
    config()->set('filament-maillog.tenancy.foreign_key.on_update', 'cascade');
    config()->set('filament-maillog.tenancy.auto_assign', true);
});

afterEach(function (): void {
    Filament::setTenant(null);
});

it('adds a nullable tenant foreign key when tenancy is enabled', function (): void {
    config()->set('filament-maillog.tenancy.column', 'company_id');
    config()->set('filament-maillog.tenancy.foreign_key.on_delete', 'set null');

    createTenantsTable();
    migrateMailLogsTable();

    $columns = collect(DB::select('PRAGMA table_info(mail_logs)'))->keyBy('name');
    $foreignKeys = collect(DB::select('PRAGMA foreign_key_list(mail_logs)'))->keyBy('from');

    expect($columns)->toHaveKey('company_id')
        ->and($columns->get('company_id')->notnull)->toBe(0)
        ->and($foreignKeys)->toHaveKey('company_id')
        ->and($foreignKeys->get('company_id')->on_delete)->toBe('SET NULL');
});

it('auto assigns the current Filament tenant to new mail logs', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $tenant = Tenant::query()->create(['name' => 'Acme']);

    Filament::setTenant($tenant, isQuiet: true);

    $mailLog = MailLog::query()->create(mailLogAttributes());

    expect($mailLog->tenant_id)->toBe($tenant->id);
});

it('allows unassigned mail logs when no tenant context exists', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $mailLog = MailLog::query()->create(mailLogAttributes());

    expect($mailLog->tenant_id)->toBeNull();
});

it('scopes the resource query to the current Filament tenant', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $visibleTenant = Tenant::query()->create(['name' => 'Acme']);
    $hiddenTenant = Tenant::query()->create(['name' => 'Globex']);
    $visibleLog = MailLog::query()->create(mailLogAttributes(['tenant_id' => $visibleTenant->id]));
    MailLog::query()->create(mailLogAttributes(['tenant_id' => $hiddenTenant->id]));

    Filament::setTenant($visibleTenant, isQuiet: true);

    expect(MailLogResource::getEloquentQuery()->pluck('id')->all())->toBe([$visibleLog->id]);
});

function createTenantsTable(): void
{
    Schema::create('tenants', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}

function migrateMailLogsTable(): void
{
    $migration = include __DIR__.'/../database/migrations/create_filament_mail_log_table.php.stub';
    $migration->up();
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function mailLogAttributes(array $overrides = []): array
{
    return array_merge([
        'from' => 'hello@example.com',
        'to' => 'ada@example.com',
        'subject' => 'Welcome',
        'body' => '<p>Welcome to Switchboard.</p>',
    ], $overrides);
}
