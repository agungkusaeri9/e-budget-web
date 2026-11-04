<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'type', 'amount', 'description', 'is_default', 'is_saving', 'saving_id', 'order_no'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use BelongsToUser, HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_default' => false,
        'is_saving' => false,
        'order_no' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_default' => 'boolean',
            'is_saving' => 'boolean',
            'order_no' => 'integer',
        ];
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }
}
