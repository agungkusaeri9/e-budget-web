<?php

namespace App\Http\Requests\RecurringExpense;

use Illuminate\Foundation\Http\FormRequest;

class IndexRecurringExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'string', 'in:true,false,1,0,all'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'year' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->only(['search', 'category_id', 'year', 'per_page']);

        if ($this->has('is_active') && $this->input('is_active') !== 'all' && $this->input('is_active') !== null) {
            $filters['is_active'] = $this->input('is_active');
        }

        return $filters;
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 10);
    }
}
