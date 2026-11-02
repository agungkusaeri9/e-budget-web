<?php

namespace Tests\Unit;

use App\Http\Requests\Category\StoreCategoryRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryRequestTest extends TestCase
{
    public function test_store_category_request_allows_nullable_amount_and_description(): void
    {
        $request = new StoreCategoryRequest;

        $validator = Validator::make([
            'name' => 'Potongan kerja (BPJS)',
            'type' => 'expense',
            'amount' => null,
            'description' => null,
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_category_request_validates_type_enum(): void
    {
        $request = new StoreCategoryRequest;

        $validator = Validator::make([
            'name' => 'Salary Juni 2026',
            'type' => 'invalid_type',
            'amount' => 5000000,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }
}
