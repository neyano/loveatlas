<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Quote;
use App\Models\Report;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    // --- Quote Approval ---

    public function test_admin_can_list_pending_quotes(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        Quote::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/quotes/pending');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_admin_can_approve_quote(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/quotes/{$quote->id}/approve");

        $response->assertOk();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'status' => 'approved']);
    }

    public function test_admin_can_reject_quote(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/quotes/{$quote->id}/reject", [
            'reason' => '不適切な内容',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'status' => 'rejected']);
    }

    public function test_regular_user_cannot_access_admin_quotes(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/admin/quotes/pending');

        $response->assertForbidden();
    }

    // --- Reports ---

    public function test_admin_can_list_reports(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
        ]);
        Report::create([
            'reporter_id' => $this->user->id,
            'quote_id' => $quote->id,
            'reason' => 'spam',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/reports');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_admin_can_resolve_report(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
        ]);
        $report = Report::create([
            'reporter_id' => $this->user->id,
            'quote_id' => $quote->id,
            'reason' => 'spam',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/reports/{$report->id}", [
            'status' => 'resolved',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'reviewed_by' => $this->admin->id,
        ]);
    }

    // --- User report submission ---

    public function test_user_can_submit_report(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        $quote = Quote::factory()->create([
            'user_id' => $this->admin->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/reports', [
            'quote_id' => $quote->id,
            'reason' => 'inappropriate',
            'description' => 'テスト通報',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'quote_id' => $quote->id,
            'reason' => 'inappropriate',
        ]);
    }

    public function test_unauthenticated_user_cannot_submit_report(): void
    {
        $response = $this->postJson('/api/v1/reports', [
            'quote_id' => 1,
            'reason' => 'spam',
        ]);

        $response->assertUnauthorized();
    }

    // --- Stats ---

    public function test_admin_can_view_stats(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/stats');

        $response->assertOk();
        $response->assertJsonStructure([
            'users' => ['total', 'active'],
            'quotes' => ['total', 'approved', 'pending', 'rejected'],
            'works' => ['total', 'approved'],
            'reports' => ['total', 'open'],
            'visits' => ['total'],
        ]);
    }

    // --- User Management ---

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/users');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_admin_can_change_user_role(): void
    {
        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/users/{$this->user->id}/role", [
            'role' => 'moderator',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'role' => 'moderator']);
    }

    public function test_admin_can_ban_user(): void
    {
        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/users/{$this->user->id}/ban");

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'is_active' => false]);
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->user)->getJson('/api/v1/admin/stats')->assertForbidden();
        $this->actingAs($this->user)->getJson('/api/v1/admin/users')->assertForbidden();
        $this->actingAs($this->user)->getJson('/api/v1/admin/reports')->assertForbidden();
    }

    // --- Work Management ---

    public function test_admin_can_create_work(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/works', [
            'title' => 'テスト映画',
            'type' => 'movie',
            'year' => 2024,
            'country' => '日本',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('works', ['title' => 'テスト映画', 'type' => 'movie']);
    }

    public function test_admin_can_update_work(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/works/{$work->id}", [
            'title' => '更新された作品名',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('works', ['id' => $work->id, 'title' => '更新された作品名']);
    }

    public function test_admin_can_delete_work(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/works/{$work->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('works', ['id' => $work->id]);
    }

    public function test_admin_can_approve_work(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id, 'is_approved' => false]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/works/{$work->id}/approve");

        $response->assertOk();
        $this->assertDatabaseHas('works', ['id' => $work->id, 'is_approved' => true]);
    }

    public function test_admin_cannot_delete_work_with_quotes(): void
    {
        $work = Work::factory()->create(['submitted_by' => $this->admin->id]);
        $location = Location::factory()->create();
        Quote::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $work->id,
            'location_id' => $location->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/admin/works/{$work->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('works', ['id' => $work->id]);
    }
}
