<?php

use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Log\Context\Repository as ContextRepository;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tapp\FilamentMailLog\Contracts\ProvidesMailLogTenant;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Resources\MailLogResource;
use Tapp\FilamentMailLog\Tests\Fixtures\Tenant;

beforeEach(function (): void {
    Filament::setTenant(null);
    if (class_exists(Context::class)) {
        Context::flush();
    }

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

it('uses text columns for mail address and subject fields in the create migration', function (): void {
    migrateMailLogsTable();

    assertMailLogAddressColumnsAreText();
});

it('can widen address columns on existing mail_logs tables', function (): void {
    migrateMailLogsTableWithStringColumns();
    migrateMailLogAddressColumnsToText();

    assertMailLogAddressColumnsAreText();
});

it('can run the address column change migration more than once', function (): void {
    migrateMailLogsTableWithStringColumns();
    migrateMailLogAddressColumnsToText();
    migrateMailLogAddressColumnsToText();

    assertMailLogAddressColumnsAreText();
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

it('assigns a captured Filament tenant after the live tenant is gone', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $tenant = Tenant::query()->create(['name' => 'Acme']);

    Filament::setTenant($tenant, isQuiet: true);

    $payload = app(ContextRepository::class)->dehydrate();

    Filament::setTenant(null);
    Context::flush();
    app(ContextRepository::class)->hydrate($payload);

    $mailLog = MailLog::query()->create(mailLogAttributes());

    expect($mailLog->tenant_id)->toBe($tenant->id);
});

it('assigns a tenant from a notification that implements ProvidesMailLogTenant', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $tenant = Tenant::query()->create(['name' => 'Acme']);

    $notification = new class($tenant->id) implements ProvidesMailLogTenant
    {
        public function __construct(private mixed $tenantId) {}

        public function mailLogTenantId(): mixed
        {
            return $this->tenantId;
        }
    };

    event(new NotificationSending((object) ['email' => 'ada@example.com'], $notification, 'mail'));

    $mailLog = MailLog::query()->create(mailLogAttributes());

    expect($mailLog->tenant_id)->toBe($tenant->id);
});

it('assigns a tenant from a public notification property', function (): void {
    createTenantsTable();
    migrateMailLogsTable();

    $tenant = Tenant::query()->create(['name' => 'Acme']);

    $notification = new class($tenant)
    {
        public function __construct(public Tenant $tenant) {}
    };

    event(new NotificationSending((object) ['email' => 'ada@example.com'], $notification, 'mail'));

    $mailLog = MailLog::query()->create(mailLogAttributes());

    expect($mailLog->tenant_id)->toBe($tenant->id);
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

function migrateMailLogsTableWithStringColumns(): void
{
    Schema::create('mail_logs', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('from')->nullable();
        $table->string('to')->nullable();
        $table->string('cc')->nullable();
        $table->string('bcc')->nullable();
        $table->string('subject');
        $table->longText('body');
    });
}

function migrateMailLogAddressColumnsToText(): void
{
    $migration = include __DIR__.'/../database/migrations/change_mail_log_address_columns_to_text.php.stub';
    $migration->up();
}

function assertMailLogAddressColumnsAreText(): void
{
    $columns = collect(DB::select('PRAGMA table_info(mail_logs)'))->keyBy('name');

    foreach (['from', 'to', 'cc', 'bcc', 'subject'] as $column) {
        expect(strtoupper($columns->get($column)->type))->toBe('TEXT');
    }
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
