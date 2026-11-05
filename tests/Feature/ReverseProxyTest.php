<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReverseProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginator_uses_https_when_app_url_is_https(): void
    {
        config(['app.url' => 'https://e-budget.masagung.my.id']);
        (new AppServiceProvider(app()))->boot();

        $this->get('http://e-budget.masagung.my.id/login');

        $this->assertSame('https://e-budget.masagung.my.id/login', Paginator::resolveCurrentPath());

        URL::forceScheme(null);
        URL::forceRootUrl(null);
    }

    public function test_redirect_uses_forwarded_host_and_scheme_from_reverse_proxy(): void
    {
        $response = $this
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'ebudget.example.com',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/dashboard');

        $response->assertRedirect('https://ebudget.example.com/login');
    }

    public function test_redirect_uses_request_host_when_accessed_directly_over_http(): void
    {
        $response = $this->get('http://203.0.113.10:8080/dashboard');

        $response->assertRedirect('http://203.0.113.10:8080/login');
    }

    public function test_page_does_not_force_upgrade_insecure_requests(): void
    {
        $response = $this->get('http://203.0.113.10:8080/login');

        $response->assertOk();
        $response->assertDontSee('upgrade-insecure-requests', false);
        $response->assertDontSee('https://203.0.113.10', false);
    }
}
