<?php

namespace Tests\Feature\Dashboard;

use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\Role;
use App\Models\SesiLatihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPelatihTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::create(['name' => 'Manajemen']);
        Role::create(['name' => 'Pelatih']);
        Role::create(['name' => 'Atlet']);
    }

    public function test_pelatih_dialihkan_ke_dashboard_pelatih()
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $coach = User::factory()->create([
            'role_id' => $coachRole->id,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($coach)->get('/dashboard');

        $response->assertRedirect(route('pelatih.dashboard'));
    }

    public function test_pelatih_dapat_mengakses_dashboard_dengan_data()
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $atletRole = Role::where('name', 'Atlet')->first();

        $coach = User::factory()->create([
            'role_id' => $coachRole->id,
            'is_verified' => true,
        ]);

        $athlete = User::factory()->create([
            'role_id' => $atletRole->id,
            'coach_id' => $coach->id,
            'is_verified' => true,
        ]);

        $exerciseType = JenisLatihan::factory()->create();

        // Create a session
        SesiLatihan::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => now()->addDay(),
        ]);

        // Create a log
        LogLatihan::factory()->create([
            'athlete_id' => $athlete->id,
            'date' => now(),
            'distance_km' => 15.5,
        ]);

        $response = $this->actingAs($coach)->get(route('pelatih.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard/Pelatih')
            ->has('stats.total_athletes')
            ->where('stats.total_athletes', 1)
            ->has('upcomingSessions')
            ->has('recentLogs')
            ->has('performanceTrend')
        );
    }
}
