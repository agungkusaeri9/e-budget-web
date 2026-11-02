<?php

namespace App\Models;

use Database\Factories\RecurringExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable([
    'category_id',
    'name',
    'amount',
    'start_month',
    'start_year',
    'end_month',
    'end_year',
    'is_active',
    'description',
])]
class RecurringExpense extends Model
{
    /** @use HasFactory<RecurringExpenseFactory> */
    use HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
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
            'start_month' => 'integer',
            'start_year' => 'integer',
            'end_month' => 'integer',
            'end_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check whether this recurring expense is active for the given month and year.
     */
    public function isActiveForPeriod(int $month, int $year): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $target = $year * 12 + $month;
        $start = $this->start_year * 12 + $this->start_month;

        if ($target < $start) {
            return false;
        }

        if ($this->end_year && $this->end_month) {
            $end = $this->end_year * 12 + $this->end_month;
            if ($target > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total duration in months, or null if ongoing.
     */
    public function getTotalDurationAttribute(): ?int
    {
        if (! $this->end_year || ! $this->end_month) {
            return null;
        }

        return ($this->end_year - $this->start_year) * 12 + ($this->end_month - $this->start_month) + 1;
    }

    /**
     * Get the count of closed budget plans that include this recurring expense.
     */
    public function getPaidCountAttribute(): int
    {
        if (array_key_exists('paid_count', $this->attributes)) {
            return (int) $this->attributes['paid_count'];
        }

        return $this->calculatePaidCount();
    }

    /**
     * Calculate the count of closed budget plans for this recurring expense.
     *
     * @param  Collection<int, BudgetPlan>|null  $closedPlans
     */
    public function calculatePaidCount(?Collection $closedPlans = null): int
    {
        if ($closedPlans !== null) {
            return $closedPlans->filter(function ($plan) {
                $planPeriod = $plan->year * 12 + $plan->month;
                $startPeriod = $this->start_year * 12 + $this->start_month;

                if ($planPeriod < $startPeriod) {
                    return false;
                }

                if ($this->end_year && $this->end_month) {
                    $endPeriod = $this->end_year * 12 + $this->end_month;
                    if ($planPeriod > $endPeriod) {
                        return false;
                    }
                }

                return $plan->items->contains(function ($item) {
                    return (int) $item->category_id === (int) $this->category_id;
                });
            })->count();
        }

        $startPeriod = $this->start_year * 12 + $this->start_month;

        $query = BudgetPlan::query()
            ->where('status', 'closed')
            ->whereHas('items', fn ($q) => $q->where('category_id', $this->category_id))
            ->whereRaw('(year * 12 + month) >= ?', [$startPeriod]);

        if ($this->end_year && $this->end_month) {
            $endPeriod = $this->end_year * 12 + $this->end_month;
            $query->whereRaw('(year * 12 + month) <= ?', [$endPeriod]);
        }

        return $query->distinct()->count();
    }

    /**
     * Get remaining payment count, or null if ongoing.
     */
    public function getRemainingCountAttribute(): ?int
    {
        $total = $this->total_duration;
        if ($total === null) {
            return null;
        }

        return max(0, $total - $this->paid_count);
    }

    /**
     * Get payment progress percentage, or null if ongoing.
     */
    public function getProgressPercentageAttribute(): ?float
    {
        $total = $this->total_duration;
        if ($total === null || $total <= 0) {
            return null;
        }

        return min(100, round(($this->paid_count / $total) * 100, 1));
    }

    /**
     * Check whether all payments have been completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        $total = $this->total_duration;
        if ($total === null) {
            return false;
        }

        return $this->paid_count >= $total;
    }

    /**
     * Get total committed amount (amount * total_duration), or null if ongoing.
     */
    public function getTotalAmountAttribute(): ?float
    {
        $total = $this->total_duration;
        if ($total === null) {
            return null;
        }

        return (float) $this->amount * $total;
    }

    /**
     * Get total paid amount so far based on closed budget plans.
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->amount * $this->paid_count;
    }

    /**
     * Get remaining amount to be paid, or null if ongoing.
     */
    public function getRemainingAmountAttribute(): ?float
    {
        $remaining = $this->remaining_count;
        if ($remaining === null) {
            return null;
        }

        return (float) $this->amount * $remaining;
    }

    /**
     * Get comprehensive payment statistics for this recurring expense.
     *
     * @param  Collection<int, BudgetPlan>|null  $closedPlans
     * @return array{paid_count: int, total_duration: ?int, remaining_count: ?int, progress_percentage: ?float, is_completed: bool, total_amount: ?float, paid_amount: float, remaining_amount: ?float}
     */
    public function getPaymentStats(?Collection $closedPlans = null): array
    {
        $totalDuration = $this->total_duration;
        $paidCount = $this->calculatePaidCount($closedPlans);
        $remainingCount = $totalDuration !== null ? max(0, $totalDuration - $paidCount) : null;
        $progressPercentage = ($totalDuration !== null && $totalDuration > 0)
            ? min(100, round(($paidCount / $totalDuration) * 100, 1))
            : null;
        $isCompleted = $totalDuration !== null && $paidCount >= $totalDuration;

        $amount = (float) $this->amount;
        $totalAmount = $totalDuration !== null ? $amount * $totalDuration : null;
        $paidAmount = $amount * $paidCount;
        $remainingAmount = $remainingCount !== null ? $amount * $remainingCount : null;

        return [
            'paid_count' => $paidCount,
            'total_duration' => $totalDuration,
            'remaining_count' => $remainingCount,
            'progress_percentage' => $progressPercentage,
            'is_completed' => $isCompleted,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
        ];
    }
}
