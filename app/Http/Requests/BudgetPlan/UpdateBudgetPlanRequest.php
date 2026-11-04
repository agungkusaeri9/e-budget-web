<?php

namespace App\Http\Requests\BudgetPlan;

use App\Models\BudgetPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'items.*.category_id' => ['nullable', 'integer', auth()->check() ? Rule::exists('categories', 'id')->where('user_id', auth()->id()) : 'exists:categories,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'string', 'in:income,expense'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $routePlan = $this->route('budget_plan');
                $ignoreId = $routePlan instanceof BudgetPlan ? $routePlan->id : (int) $routePlan;

                $exists = BudgetPlan::query()
                    ->where('month', (int) $this->input('month'))
                    ->where('year', (int) $this->input('year'))
                    ->where('id', '!=', $ignoreId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('month', 'Budget Plan untuk bulan dan tahun tersebut sudah ada.');
                }
            },
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
