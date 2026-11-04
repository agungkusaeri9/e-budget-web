<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['target_amount', 'deadline', 'saving_provider_id'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'saving_provider_id' => ['nullable', 'integer', 'exists:saving_providers,id'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama tabungan',
            'saving_provider_id' => 'tempat penyimpanan',
            'target_amount' => 'target nominal',
            'deadline' => 'tenggat',
            'notes' => 'catatan',
        ];
    }
}
