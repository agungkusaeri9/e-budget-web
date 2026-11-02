<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $team = $user->currentTeam;

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_can_be_filtered_by_month_and_year()
    {
        $user = User::factory()->create();
        BudgetPlan::factory()->create([
            'user_id' => $user->id,
            'month' => 6,
            'year' => 2026,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => 6, 'year' => 2026]));

        $response->assertOk();
    }
}
