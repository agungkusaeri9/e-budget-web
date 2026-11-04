<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['user_id', 'name', 'account_number', 'account_holder', 'description'])]
class SavingProvider extends Model
{
    use BelongsToUser;

    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class);
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(SavingTransaction::class, Saving::class);
    }
}
