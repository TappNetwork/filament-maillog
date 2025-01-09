<?php

namespace Tapp\FilamentMailLog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array $data
 */
class MailLog extends Model
{
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
