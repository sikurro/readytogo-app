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
}
