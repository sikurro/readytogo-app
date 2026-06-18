<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Incident;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncidentReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::create(['name' => 'Admin']);
        $this->petugasRole = Role::create(['name' => 'Petugas']);

        // Create test user
        $this->user = User::factory()->create([
            'role_id' => $this->petugasRole->id,
        ]);
    }

    public function test_guest_cannot_access_incidents()
    {
        $response = $this->get(route('incidents.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('incidents.create'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('incidents.store'), []);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_incidents_index()
    {
        $response = $this->actingAs($this->user)->get(route('incidents.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_create_form()
    {
        $response = $this->actingAs($this->user)->get(route('incidents.create'));
        $response->assertStatus(200);
    }

    public function test_incident_validation_rules()
    {
        $response = $this->actingAs($this->user)->post(route('incidents.store'), []);
        $response->assertSessionHasErrors(['category', 'severity', 'description']);
    }

    public function test_authenticated_user_can_submit_incident_report()
    {
        Storage::fake('public');

        $payload = [
            'category' => 'unsafe_condition',
            'severity' => 'medium',
            'description' => 'Tangga pandu patah di lambung kanan kapal.',
            'latitude' => -6.123456,
            'longitude' => 106.123456,
            'image' => UploadedFile::fake()->image('unsafe_ladder.jpg'),
        ];

        $response = $this->actingAs($this->user)->post(route('incidents.store'), $payload);

        $response->assertRedirect(route('incidents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('incidents', [
            'user_id' => $this->user->id,
            'category' => 'unsafe_condition',
            'severity' => 'medium',
            'description' => 'Tangga pandu patah di lambung kanan kapal.',
            'latitude' => -6.123456,
            'longitude' => 106.123456,
            'status' => 'open',
        ]);

        $incident = Incident::first();
        $this->assertNotNull($incident->image_path);
        Storage::disk('public')->assertExists($incident->image_path);
    }

    public function test_non_admin_cannot_access_admin_incidents_index()
    {
        $response = $this->actingAs($this->user)->get(route('admin.incidents.index'));
        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_update_incident_status()
    {
        $incident = Incident::create([
            'user_id' => $this->user->id,
            'category' => 'unsafe_condition',
            'severity' => 'medium',
            'description' => 'Contoh laporan.',
            'status' => 'open'
        ]);

        $response = $this->actingAs($this->user)->put(route('admin.incidents.update-status', $incident->id), [
            'status' => 'closed',
            'admin_feedback' => 'Tindakan sudah diambil.'
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_incidents_index()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get(route('admin.incidents.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_incident_status_and_trigger_notification()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $incident = Incident::create([
            'user_id' => $this->user->id,
            'category' => 'unsafe_condition',
            'severity' => 'medium',
            'description' => 'Tangga pandu patah.',
            'status' => 'open'
        ]);

        $response = $this->actingAs($admin)->put(route('admin.incidents.update-status', $incident->id), [
            'status' => 'closed',
            'admin_feedback' => 'Tangga pandu sudah diperbaiki.'
        ]);

        $response->assertRedirect(route('admin.incidents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'closed',
            'admin_feedback' => 'Tangga pandu sudah diperbaiki.',
            'resolved_by' => $admin->id,
        ]);

        // Check if database notification was sent to the reporter
        $this->assertCount(1, $this->user->unreadNotifications);
        $notification = $this->user->unreadNotifications->first();
        $this->assertEquals('App\Notifications\IncidentResolved', $notification->type);
        $this->assertEquals('Tangga pandu sudah diperbaiki.', $notification->data['admin_feedback']);
    }

    public function test_non_admin_cannot_access_incident_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('admin.incidents.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_incident_dashboard()
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->get(route('admin.incidents.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Incidents/Dashboard')
            ->has('stats')
            ->has('trend')
            ->has('composition')
            ->has('mapIncidents')
            ->has('topReporters')
            ->has('criticalIncidents')
        );
    }
}
