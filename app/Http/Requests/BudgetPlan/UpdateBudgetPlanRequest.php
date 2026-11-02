<?php

namespace App\Http\Requests\BudgetPlan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['required', 'string', 'in:draft,active,closed'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'string', 'in:income,expense'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'month' => 'bulan',
            'year' => 'tahun',
            'status' => 'status',
            'notes' => 'catatan',
            'items' => 'daftar item anggaran',
            'items.*.name' => 'nama item',
            'items.*.type' => 'tipe item',
            'items.*.amount' => 'nominal item',
        ];
    }
}
