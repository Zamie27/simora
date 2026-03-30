<?php

namespace Tests\Feature;

use App\Models\ExerciseType;
use App\Models\Role;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoachDashboardTest extends TestCase
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

    public function test_coach_is_redirected_to_coach_dashboard()
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $coach = User::factory()->create([
            'role_id' => $coachRole->id,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($coach)->get('/dashboard');

        $response->assertRedirect(route('coach.dashboard'));
    }

    public function test_coach_can_access_dashboard_with_data()
    {
        $coachRole = Role::where('name', 'Pelatih')->first();
        $atletRole = Role::where('name', 'Atlet')->first();
        
        $coach = User::factory()->create([
            'role_id' => $coachRole->id,
            'status' => 'verified',
        ]);

        $athlete = User::factory()->create([
            'role_id' => $atletRole->id,
            'coach_id' => $coach->id,
            'status' => 'verified',
        ]);

        $exerciseType = ExerciseType::factory()->create();

        // Create a session
        TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => now()->addDay(),
        ]);

        // Create a log
        TrainingLog::factory()->create([
            'athlete_id' => $athlete->id,
            'exercise_type_id' => $exerciseType->id,
            'date' => now(),
            'distance_km' => 15.5,
        ]);

        $response = $this->actingAs($coach)->get(route('coach.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('coach/Dashboard')
            ->has('stats.total_athletes')
            ->where('stats.total_athletes', 1)
            ->has('upcomingSessions')
            ->has('recentLogs')
            ->has('performanceTrend')
        );
    }
}
