<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\ExerciseType;
use App\Models\PhysicalMetric;
use App\Models\TrainingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AthleteDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed([\Database\Seeders\DatabaseSeeder::class]);
        \App\Models\Category::create(['name' => 'Junior', 'description' => 'Junior category']);
        ExerciseType::create(['name' => 'Cycling', 'description' => 'Cycling exercise']);
    }

    public function test_athlete_can_access_dashboard(): void
    {
        $athleteRole = Role::where('name', 'Atlet')->first();
        $user = User::factory()->create([
            'role_id' => $athleteRole->id,
            'category_id' => \App\Models\Category::first()->id,
            'email_verified_at' => now(),
        ]);

        // Need at least one training log for performance trend to avoid null issues if any
        TrainingLog::factory()->create(['athlete_id' => $user->id, 'date' => now()]);

        // Debug: what is the role?
        // dd($user->role->name);

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->get(route('dashboard'));

        // If it returns 200, check what it renders
        if ($response->status() === 200) {
            dump('Rendered component: ' . ($response->original->getData()['page']['component'] ?? 'Unknown'));
        }

        $response->assertRedirect(route('athlete.dashboard'));

        $response = $this->actingAs($user)->get(route('athlete.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('athlete/Dashboard')
        );
    }

    public function test_athlete_can_perform_quick_update(): void
    {
        $athleteRole = Role::where('name', 'Atlet')->first();
        $user = User::factory()->create([
            'role_id' => $athleteRole->id,
            'date_of_birth' => now()->subYears(20),
            'email_verified_at' => now(),
        ]);

        $exerciseType = ExerciseType::first();

        // Perform request
        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post(route('athlete.dashboard.quick-update'), [
            'weight' => 75.5,
            'height' => 180,
            'title' => 'Quick endurance ride',
            'exercise_type_id' => $exerciseType->id,
            'distance_km' => 25.5,
            'duration_minutes' => 60,
            'avg_heart_rate' => 145,
            'rpm' => 90,
            'calories' => 600,
            'notes' => 'Feeling good',
        ]);

        if ($response->status() !== 302) {
            $response->assertSessionHasNoErrors();
        }

        $response->assertRedirect(route('athlete.dashboard'));
        
        // Verify Physical Metric
        $this->assertDatabaseHas('physical_metrics', [
            'user_id' => $user->id,
            'weight' => 75.5,
            'height' => 180,
        ]);

        // Verify Training Log
        $this->assertDatabaseHas('training_logs', [
            'athlete_id' => $user->id,
            'title' => 'Quick endurance ride',
            'distance_km' => 25.5,
            'duration_minutes' => 60,
        ]);
    }
}
