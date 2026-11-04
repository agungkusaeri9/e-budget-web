<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['saving_id', 'budget_plan_id', 'amount', 'transaction_date', 'note'])]
class SavingTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date:Y-m-d',
        ];
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(Saving::class);
    }
}
