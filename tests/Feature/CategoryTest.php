<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
    }

    public function test_category_can_be_created_with_nullable_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Potongan kerja (BPJS)',
            'type' => 'expense',
            'amount' => null,
            'description' => 'Potongan BPJS bulanan',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Potongan kerja (BPJS)',
            'type' => 'expense',
            'amount' => null,
            'description' => 'Potongan BPJS bulanan',
        ]);
    }

    public function test_category_can_be_created_with_default_amount(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Kosan',
            'type' => 'expense',
            'amount' => 1500000,
            'description' => 'Biaya kosan per bulan',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Kosan',
            'type' => 'expense',
            'amount' => 1500000,
        ]);
    }

    public function test_category_can_be_updated(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->expense()->create([
            'name' => 'Motor',
            'amount' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('categories.update', $category), [
            'name' => 'Motor & Bensin',
            'type' => 'expense',
            'amount' => 300000,
            'description' => 'Bensin dan servis rutin',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Motor & Bensin',
            'type' => 'expense',
            'amount' => 300000,
        ]);
    }

    public function test_category_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
