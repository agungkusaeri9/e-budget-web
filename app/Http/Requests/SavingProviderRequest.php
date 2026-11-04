<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['account_number', 'account_holder', 'description'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama tempat penyimpanan',
            'account_number' => 'nomor rekening',
            'account_holder' => 'atas nama',
            'description' => 'deskripsi',
        ];
    }
}
