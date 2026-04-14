<?php

namespace Tests\Feature;

use App\Models\ExerciseType;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecurringTrainingLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_logs_for_recurring_sessions_on_matching_day(): void
    {
        // Freeze time to a known day (Monday)
        Carbon::setTestNow(Carbon::parse('2026-04-13 08:00:00')); // Monday

        $coach = User::factory()->create();
        $exerciseType = ExerciseType::factory()->create();
        $athlete = User::factory()->create(['coach_id' => $coach->id]);

        // Create a recurring session scheduled on a Monday
        $session = TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => '2026-04-06', // Previous Monday
            'repeat_weekly' => true,
        ]);
        $session->athletes()->attach($athlete->id);

        $this->artisan('training:generate-recurring-logs')
            ->assertExitCode(0);

        $this->assertDatabaseHas('training_logs', [
            'training_session_id' => $session->id,
            'athlete_id' => $athlete->id,
            'date' => Carbon::parse('2026-04-13')->toDateTimeString(),
            'completion_status' => 'not_started',
        ]);
    }

    public function test_does_not_generate_logs_on_non_matching_day(): void
    {
        // Freeze time to Tuesday
        Carbon::setTestNow(Carbon::parse('2026-04-14 08:00:00')); // Tuesday

        $coach = User::factory()->create();
        $exerciseType = ExerciseType::factory()->create();
        $athlete = User::factory()->create(['coach_id' => $coach->id]);

        // Create a recurring session scheduled on Monday
        $session = TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => '2026-04-06', // Monday
            'repeat_weekly' => true,
        ]);
        $session->athletes()->attach($athlete->id);

        $this->artisan('training:generate-recurring-logs')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('training_logs', [
            'training_session_id' => $session->id,
            'athlete_id' => $athlete->id,
            'date' => Carbon::parse('2026-04-14')->toDateTimeString(),
        ]);
    }

    public function test_does_not_duplicate_logs_if_already_exists(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-13 08:00:00')); // Monday

        $coach = User::factory()->create();
        $exerciseType = ExerciseType::factory()->create();
        $athlete = User::factory()->create(['coach_id' => $coach->id]);

        $session = TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => '2026-04-06',
            'repeat_weekly' => true,
        ]);
        $session->athletes()->attach($athlete->id);

        // Pre-create a log for today
        TrainingLog::create([
            'training_session_id' => $session->id,
            'athlete_id' => $athlete->id,
            'date' => '2026-04-13',
            'type' => $exerciseType->name,
            'completion_status' => 'not_started',
            'attendance_status' => 'absent',
        ]);

        $this->artisan('training:generate-recurring-logs')
            ->assertExitCode(0);

        // Should still only have 1 log, not 2
        $this->assertSame(
            1,
            TrainingLog::where('training_session_id', $session->id)
                ->where('athlete_id', $athlete->id)
                ->whereDate('date', '=', '2026-04-13')
                ->count()
        );
    }

    public function test_does_not_generate_logs_for_non_recurring_sessions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-13 08:00:00'));

        $coach = User::factory()->create();
        $exerciseType = ExerciseType::factory()->create();
        $athlete = User::factory()->create(['coach_id' => $coach->id]);

        $session = TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => '2026-04-06',
            'repeat_weekly' => false,
        ]);
        $session->athletes()->attach($athlete->id);

        $this->artisan('training:generate-recurring-logs')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('training_logs', [
            'training_session_id' => $session->id,
            'date' => Carbon::parse('2026-04-13')->toDateTimeString(),
        ]);
    }

    public function test_generates_logs_for_multiple_athletes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-13 08:00:00'));

        $coach = User::factory()->create();
        $exerciseType = ExerciseType::factory()->create();
        $athlete1 = User::factory()->create(['coach_id' => $coach->id]);
        $athlete2 = User::factory()->create(['coach_id' => $coach->id]);

        $session = TrainingSession::factory()->create([
            'coach_id' => $coach->id,
            'exercise_type_id' => $exerciseType->id,
            'scheduled_date' => '2026-04-06',
            'repeat_weekly' => true,
        ]);
        $session->athletes()->attach([$athlete1->id, $athlete2->id]);

        $this->artisan('training:generate-recurring-logs')
            ->assertExitCode(0);

        $this->assertDatabaseHas('training_logs', [
            'training_session_id' => $session->id,
            'athlete_id' => $athlete1->id,
            'date' => Carbon::parse('2026-04-13')->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('training_logs', [
            'training_session_id' => $session->id,
            'athlete_id' => $athlete2->id,
            'date' => Carbon::parse('2026-04-13')->toDateTimeString(),
        ]);
    }
}
