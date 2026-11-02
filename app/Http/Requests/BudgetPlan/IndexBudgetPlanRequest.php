<?php

namespace App\Http\Requests\BudgetPlan;

use Illuminate\Foundation\Http\FormRequest;

class IndexBudgetPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['nullable', 'string'],
            'year' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:draft,active,closed,all'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['month', 'year', 'status', 'per_page']);
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 10);
    }
}
