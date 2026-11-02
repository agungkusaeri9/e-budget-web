<?php

namespace App\Http\Requests\RecurringExpense;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRecurringExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_id' => $this->input('category_id') && $this->input('category_id') !== '__manual__'
                ? (int) $this->input('category_id')
                : null,
            'category_name' => $this->input('category_name') ? trim($this->input('category_name')) : null,
            'is_active' => $this->boolean('is_active', true),
            'start_month' => (int) $this->input('start_month'),
            'start_year' => (int) $this->input('start_year'),
            'end_month' => $this->input('end_month') ? (int) $this->input('end_month') : null,
            'end_year' => $this->input('end_year') ? (int) $this->input('end_year') : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'required_without:category_name', 'integer', 'exists:categories,id'],
            'category_name' => ['nullable', 'required_without:category_id', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'start_month' => ['required', 'integer', 'between:1,12'],
            'start_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'end_month' => ['nullable', 'integer', 'between:1,12'],
            'end_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $startMonth = (int) $this->input('start_month');
                $startYear = (int) $this->input('start_year');
                $endMonth = $this->input('end_month') ? (int) $this->input('end_month') : null;
                $endYear = $this->input('end_year') ? (int) $this->input('end_year') : null;

                if (($endMonth && ! $endYear) || (! $endMonth && $endYear)) {
                    $validator->errors()->add('end_year', 'Bulan dan tahun akhir harus diisi bersamaan.');
                }

                if ($endMonth && $endYear) {
                    $startPeriod = $startYear * 12 + $startMonth;
                    $endPeriod = $endYear * 12 + $endMonth;

                    if ($endPeriod < $startPeriod) {
                        $validator->errors()->add('end_month', 'Periode akhir tidak boleh lebih awal dari periode mulai.');
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori pengeluaran',
            'category_name' => 'nama kategori baru',
            'name' => 'nama komitmen pengeluaran',
            'amount' => 'nominal pengeluaran',
            'start_month' => 'bulan mulai',
            'start_year' => 'tahun mulai',
            'end_month' => 'bulan selesai',
            'end_year' => 'tahun selesai',
            'is_active' => 'status aktif',
            'description' => 'deskripsi / catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required_without' => 'Kategori pengeluaran wajib dipilih atau diisi.',
            'category_name.required_without' => 'Kategori pengeluaran wajib dipilih atau diisi.',
        ];
    }
}
