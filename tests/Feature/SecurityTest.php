<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_unauthenticated_access_to_admin_api_returns_401(): void
    {
        $this->getJson('/api/v1/admin/stats')->assertUnauthorized();
        $this->getJson('/api/v1/admin/quotes/pending')->assertUnauthorized();
        $this->getJson('/api/v1/admin/reports')->assertUnauthorized();
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
    }

    public function test_non_admin_access_to_admin_api_returns_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->getJson('/api/v1/admin/stats')->assertForbidden();
    }

    public function test_moderator_can_access_admin_api(): void
    {
        $moderator = User::factory()->moderator()->create();

        $response = $this->actingAs($moderator)->getJson('/api/v1/admin/stats');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_submit_report(): void
    {
        $response = $this->postJson('/api/v1/reports', [
            'quote_id' => 1,
            'reason' => 'spam',
        ]);

        $response->assertUnauthorized();
    }
}
