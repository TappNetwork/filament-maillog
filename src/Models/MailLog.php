<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tapp\FilamentMailLog\Models\Traits\BelongsToTenant;

/**
 * @property array $data
 */
class MailLog extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function getDataJsonAttribute()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }
}
