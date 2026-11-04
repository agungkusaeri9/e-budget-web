<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'saving_provider_id', 'target_amount', 'deadline', 'notes'])]
class Saving extends Model
{
    use BelongsToUser, HasFactory;

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'deadline' => 'date:Y-m-d',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SavingProvider::class, 'saving_provider_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingTransaction::class);
    }
}
