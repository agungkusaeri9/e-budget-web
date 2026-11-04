<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\Category;
use App\Models\Saving;
use App\Models\SavingProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_crud_and_manual_adjust(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('saving-providers.store'), [
            'name' => 'Bank Mandiri',
            'account_number' => '',
            'account_holder' => '',
            'description' => '',
        ])->assertRedirect(route('saving-providers.index'));
        $provider = SavingProvider::firstWhere('name', 'Bank Mandiri');
        $this->assertNull($provider->account_number);

        $this->actingAs($user)->post(route('savings.store'), [
            'name' => 'Nikah',
            'saving_provider_id' => $provider->id,
            'deadline' => '',
        ])->assertRedirect(route('savings.index'));
        $saving = Saving::firstWhere('name', 'Nikah');
        $this->assertNull($saving->deadline);
        $this->assertSame($provider->id, $saving->saving_provider_id);

        $this->actingAs($user)->get(route('savings.index'))->assertOk();
        $this->actingAs($user)->get(route('savings.show', $saving))->assertOk();

        $this->actingAs($user)->post(route('savings.transactions.store', $saving), ['amount' => 500000, 'transaction_date' => '2026-11-01']);
        $this->actingAs($user)->post(route('savings.transactions.store', $saving), ['amount' => -200000, 'transaction_date' => '2026-11-02']);
        $this->assertSame(300000.0, (float) $saving->transactions()->sum('amount'));

        $this->actingAs($user)->delete(route('savings.destroy', $saving));
        $this->assertDatabaseMissing('savings', ['id' => $saving->id]);
    }

    public function test_saving_category_creates_transaction_from_budget_plan(): void
    {
        $user = User::factory()->create();
        $saving = Saving::factory()->create();
        $cat = Category::factory()->expense()->create(['is_saving' => true, 'saving_id' => $saving->id]);

        $payload = fn (string $status) => [
            'month' => 11, 'year' => 2026, 'status' => $status,
            'items' => [['category_id' => $cat->id, 'name' => $cat->name, 'type' => 'expense', 'amount' => 700000]],
        ];

        $this->actingAs($user)->post(route('budget-plans.store'), $payload('active'));
        $this->assertSame(0.0, (float) $saving->transactions()->sum('amount'));

        $plan = BudgetPlan::first();
        $this->actingAs($user)->put(route('budget-plans.update', $plan), $payload('closed'));
        $this->assertSame(700000.0, (float) $saving->transactions()->sum('amount'));
        $this->assertSame(1, $saving->transactions()->count());
    }

    public function test_saving_category_requires_saving_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('categories.store'), ['name' => 'X', 'type' => 'expense', 'is_saving' => true])
            ->assertSessionHasErrors('saving_id');
    }
}
