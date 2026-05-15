<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BandingkanPerformaDanMemfilterRiwayatControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear users to have a clean slate for each test
        User::query()->delete();

        // Ensure roles exist
        $roles = ['Manajemen', 'Pelatih', 'Atlet', 'Report'];
        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }

    public function test_management_can_view_all_athletes(): void
    {
        $managementRole = Role::where('name', 'Manajemen')->first();
        $management = User::factory()->create(['role_id' => $managementRole->id]);

        $coachRole = Role::where('name', 'Pelatih')->first();
        $coach = User::factory()->create(['role_id' => $coachRole->id]);

        $atletRole = Role::where('name', 'Atlet')->first();
        $atlet1 = User::factory()->create(['role_id' => $atletRole->id, 'coach_id' => $coach->id]);
        $atlet2 = User::factory()->create(['role_id' => $atletRole->id]);

        $response = $this->actingAs($management)
            ->get(route('manajemen.komparasi.comparison'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('komparasi-performa/Index')
            ->has('athletes', 2)
        );
    }

    public function test_coach_can_only_view_their_athletes(): void
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $coach1 = User::factory()->create(['role_id' => $coachRole->id]);
        $coach2 = User::factory()->create(['role_id' => $coachRole->id]);

        $atletRole = Role::where('name', 'Atlet')->first();
        User::factory()->count(2)->create(['role_id' => $atletRole->id, 'coach_id' => $coach1->id]);
        User::factory()->count(3)->create(['role_id' => $atletRole->id, 'coach_id' => $coach2->id]);

        $response = $this->actingAs($coach1)
            ->get(route('pelatih.komparasi.comparison'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('komparasi-performa/Index')
            ->has('athletes', 2)
        );
    }

    public function test_coach_cannot_get_comparison_data_for_unassigned_athletes(): void
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $coach = User::factory()->create(['role_id' => $coachRole->id]);

        $atletRole = Role::where('name', 'Atlet')->first();
        $atlet1 = User::factory()->create(['role_id' => $atletRole->id, 'coach_id' => $coach->id]);
        $atlet2 = User::factory()->create(['role_id' => $atletRole->id]); // Unassigned

        $response = $this->actingAs($coach)
            ->getJson(route('pelatih.komparasi.comparison.data', [
                'athlete_ids' => [$atlet1->id, $atlet2->id],
            ]));

        $response->assertStatus(403);
    }
}
