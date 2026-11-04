<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
