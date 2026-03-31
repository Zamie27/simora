<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\ExerciseType;
use App\Models\PhysicalMetric;
use App\Models\TrainingSession;
use App\Models\TrainingLog;
use App\Models\Event;
use App\Models\EventType;
use App\Models\EventPoint;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = ['Pemula', 'Junior', 'Senior', 'Elite', 'Master A', 'Master B'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat], ['description' => "Kategori $cat"]);
        }

        // 2. Exercise Types
        $exerciseTypes = ['Endurance', 'Interval / VO2 Max', 'Recovery / Active Rest', 'Speed / Sprint', 'Tempo / Sweet Spot', 'Strength / Hill Climb'];
        foreach ($exerciseTypes as $type) {
            ExerciseType::firstOrCreate(['name' => $type], ['description' => "Latihan $type"]);
        }

        // Get coaches first so we can assign EventType and EventPoint to them
        $coaches = User::whereHas('role', fn($q) => $q->where('name', 'Pelatih'))->get();
        $athletes = User::whereHas('role', fn($q) => $q->where('name', 'Atlet'))->get();

        if ($coaches->isEmpty() || $athletes->isEmpty()) {
            return; // Needs the base DatabaseSeeder to run first
        }

        $baseCoach = $coaches->first();

        // 3. Event Types & Points
        $eventTypes = ['Road Race', 'Criterium', 'Individual Time Trial (ITT)', 'Team Time Trial (TTT)', 'Cross Country (XC)', 'Track'];
        foreach ($eventTypes as $type) {
            EventType::firstOrCreate(['name' => $type, 'coach_id' => $baseCoach->id]);
        }
        
        $eventPoints = [
            '1st Place',
            '2nd Place',
            '3rd Place',
            'Top 10',
            'Finisher',
        ];
        foreach ($eventPoints as $pointStr) {
            EventPoint::firstOrCreate(['name' => $pointStr, 'coach_id' => $baseCoach->id]);
        }

        $allCategories = Category::all();
        $allExerciseTypes = ExerciseType::all();
        $allEventTypes = EventType::all();
        $allEventPoints = EventPoint::all();

        // 4. Update Athletes (Assign Coach and Category, DOB, Gender, etc.)

        foreach ($athletes as $index => $athlete) {
            // Distribute evenly among coaches
            $assignedCoach = $coaches[$index % $coaches->count()];
            // Random category
            $assignedCategory = $allCategories->random();

            $athlete->update([
                'coach_id' => $assignedCoach->id,
                'category_id' => $assignedCategory->id,
                'date_of_birth' => Carbon::now()->subYears(rand(16, 35))->subDays(rand(1, 365)),
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'is_verified' => true,
            ]);
        }

        // 5. Physical Metrics (Past 3 months, weekly)
        foreach ($athletes as $athlete) {
            $baseWeight = rand(55, 80) + (rand(0, 99) / 100);
            $baseHeight = rand(160, 185);
            $baseRestingHr = rand(45, 65);
            $currentDate = Carbon::now()->subMonths(3);

            while ($currentDate <= Carbon::now()) {
                PhysicalMetric::create([
                    'user_id' => $athlete->id,
                    'recorded_at' => $currentDate->copy(),
                    'weight' => $baseWeight + (rand(-10, 10) / 10), // Fluctuate slightly
                    'height' => $baseHeight,
                    'age' => $athlete->age ?? rand(16, 35),
                    'category' => $athlete->category->name ?? 'Unknown',
                ]);
                $currentDate->addWeek();
                // Slowly improve
                $baseWeight -= rand(0, 2) / 10;
            }
        }

        // 6. Training Sessions & Logs
        foreach ($coaches as $coach) {
            $coachAthletes = User::where('coach_id', $coach->id)->get();
            if ($coachAthletes->isEmpty()) continue;

            // Past sessions
            for ($i = 0; $i < 20; $i++) {
                $sessionDate = Carbon::now()->subDays(rand(1, 90));
                $intensity = ['low', 'medium', 'high', 'very_high'][rand(0, 3)];
                $duration = rand(45, 180);
                
                $session = TrainingSession::create([
                    'coach_id' => $coach->id,
                    'exercise_type_id' => $allExerciseTypes->random()->id,
                    'title' => 'Sesi Latihan ' . $sessionDate->format('M d'),
                    'description' => 'Sesi latihan ' . $intensity . ' intensitas.',
                    'scheduled_date' => $sessionDate,
                    'scheduled_time' => sprintf('%02d:00:00', rand(6, 16)),
                    'target_duration_minutes' => $duration,
                    'target_distance_km' => round($duration * (rand(20, 35) / 60), 2), // Rough distance estimate based on speed 20-35km/h
                    'target_avg_speed' => rand(22, 35) + (rand(0, 99) / 100),
                    'type' => ['endurance', 'interval', 'recovery', 'time_trial'][rand(0, 3)],
                ]);

                // Logs for this session
                foreach ($coachAthletes as $athlete) {
                    if (rand(1, 10) > 2) { // 80% attendance rate
                        $actualDuration = $session->target_duration_minutes + rand(-15, 15);
                        $actualDistance = $session->target_distance_km + rand(-5, 5);
                        
                        TrainingLog::create([
                            'athlete_id' => $athlete->id,
                            'training_session_id' => $session->id,
                            'date' => $session->scheduled_date,
                            'title' => $session->title,
                            'duration_minutes' => max(10, $actualDuration),
                            'distance_km' => max(1, $actualDistance),
                            'avg_speed' => max(15, round($actualDistance / (max(10, $actualDuration) / 60), 2)),
                            'avg_heart_rate' => rand(130, 175),
                            'rpm' => rand(80, 100),
                            'calories' => rand(400, 1200),
                            'intensity' => $intensity,
                            'type' => $session->type,
                            'athlete_notes' => 'Merasa cukup baik hari ini.',
                            'attendance_status' => 'present',
                            'completion_status' => 'completed',
                            'coach_rating' => rand(6, 10),
                            'coach_comments' => 'Kerja bagus, pertahankan cadence.',
                        ]);
                    }
                }
            }

            // Upcoming sessions
            for ($i = 0; $i < 5; $i++) {
                $sessionDate = Carbon::now()->addDays(rand(1, 14));
                TrainingSession::create([
                    'coach_id' => $coach->id,
                    'exercise_type_id' => $allExerciseTypes->random()->id,
                    'title' => 'Sesi Mendatang ' . $sessionDate->format('M d'),
                    'description' => 'Persiapkan fisik dan perlengkapan.',
                    'scheduled_date' => $sessionDate,
                    'scheduled_time' => sprintf('%02d:00:00', rand(6, 16)),
                    'target_duration_minutes' => rand(60, 120),
                    'target_distance_km' => rand(20, 60),
                    'target_avg_speed' => rand(25, 32),
                    'type' => ['endurance', 'interval', 'recovery', 'time_trial'][rand(0, 3)],
                ]);
            }
        }

        // 7. Standalone Logs (self-directed)
        foreach ($athletes as $athlete) {
            for ($i = 0; $i < 10; $i++) { // 10 standalone logs per athlete
                $logDate = Carbon::now()->subDays(rand(1, 90));
                $duration = rand(30, 90);
                $distance = round($duration * (rand(15, 25) / 60), 2);
                
                TrainingLog::create([
                    'athlete_id' => $athlete->id,
                    'date' => $logDate,
                    'title' => 'Latihan Mandiri ' . $logDate->format('d/m'),
                    'duration_minutes' => $duration,
                    'distance_km' => $distance,
                    'avg_speed' => round($distance / ($duration / 60), 2),
                    'rpm' => rand(70, 95),
                    'calories' => rand(300, 800),
                    'intensity' => ['low', 'medium'][rand(0, 1)],
                    'type' => 'recovery',
                    'attendance_status' => 'present',
                    'completion_status' => 'completed',
                ]);
            }
        }

        // 8. Events
        // Get generic coach (for simplicity, using random)
        for ($i = 0; $i < 3; $i++) {
            $isPast = $i < 2;
            $eventDate = $isPast ? Carbon::now()->subDays(rand(10, 60)) : Carbon::now()->addDays(rand(10, 30));
            $eventCoach = $coaches->random();
            
            $event = Event::create([
                'coach_id' => $eventCoach->id,
                'event_type_id' => $allEventTypes->random()->id,
                'title' => ($isPast ? 'Kejuaraan Balap Sepeda ' : 'Persiapan Lomba ') . fake()->city(),
                'description' => 'Event lintasan regional.',
                'location' => fake()->city(),
                'event_date' => $eventDate,
            ]);

            // Assign athletes to event
            $participants = User::whereHas('role', fn($q) => $q->where('name', 'Atlet'))->inRandomOrder()->take(rand(3, 7))->get();
            foreach ($participants as $participant) {
                DB::table('event_user')->insert([
                    'event_id' => $event->id,
                    'user_id' => $participant->id,
                    'event_point_id' => $isPast ? $allEventPoints->random()->id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 9. Messages
        foreach ($coaches as $coach) {
            $coachAthletes = User::where('coach_id', $coach->id)->get();
            foreach ($coachAthletes as $athlete) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                    Message::create([
                        'sender_id' => $coach->id,
                        'receiver_id' => $athlete->id,
                        'content' => 'Feedback sesi terakhir: ' . fake()->sentence(),
                        'is_read' => rand(0, 1) == 1,
                        'created_at' => Carbon::now()->subHours(rand(1, 72)),
                        'updated_at' => Carbon::now()->subHours(rand(1, 72)),
                    ]);
                }
            }
        }
    }
}
