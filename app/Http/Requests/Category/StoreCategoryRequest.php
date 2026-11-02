<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('amount') === '') {
            $this->merge(['amount' => null]);
        }

        $this->merge([
            'is_default' => $this->boolean('is_default', false),
            'order_no' => (int) ($this->input('order_no') ?: 0),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:income,expense'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'order_no' => ['nullable', 'integer', 'min:0'],
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
            'name' => 'nama kategori',
            'type' => 'tipe kategori',
            'amount' => 'nominal default',
            'description' => 'deskripsi',
            'is_default' => 'status default',
            'order_no' => 'nomor urut',
        ];
    }
}
