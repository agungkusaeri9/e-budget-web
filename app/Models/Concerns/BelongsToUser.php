<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    public static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where($builder->getModel()->qualifyColumn('user_id'), auth()->id());
            }
        });

        static::creating(function (Model $model) {
            if (auth()->check()) {
                $model->user_id = auth()->id();
            } elseif (empty($model->user_id)) {
                $model->user_id = User::query()->latest('id')->value('id');
            }
        });

        static::updating(function (Model $model) {
            if (auth()->check()) {
                abort_if((int) $model->getOriginal('user_id') !== (int) auth()->id(), 403, 'Akses ditolak.');
                $model->user_id = auth()->id();
            }
        });

        static::deleting(function (Model $model) {
            if (auth()->check()) {
                abort_if((int) $model->user_id !== (int) auth()->id(), 403, 'Akses ditolak.');
            }
        });
    }

    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        return $query->where($this->qualifyColumn('user_id'), $userId ?? auth()->id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
