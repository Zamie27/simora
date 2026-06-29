<?php

namespace Database\Seeders;

use App\Models\DataFisik;
use App\Models\Event;
use App\Models\JenisEvent;
use App\Models\JenisLatihan;
use App\Models\Kategori;
use App\Models\LaporanBug;
use App\Models\LogLatihan;
use App\Models\Pesan;
use App\Models\PoinEvent;
use App\Models\ProfilAtlet;
use App\Models\SesiLatihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = ['Pemula', 'Junior', 'Senior', 'Elite', 'Master A', 'Master B'];
        foreach ($categories as $cat) {
            Kategori::firstOrCreate(['name' => $cat], ['description' => "Kategori $cat"]);
        }

        // 2. Exercise Types
        $exerciseTypes = ['Endurance', 'Interval / VO2 Max', 'Recovery / Active Rest', 'Speed / Sprint', 'Tempo / Sweet Spot', 'Strength / Hill Climb'];
        foreach ($exerciseTypes as $type) {
            JenisLatihan::firstOrCreate(['name' => $type], ['description' => "Latihan $type"]);
        }

        // Get coaches first so we can assign EventType and EventPoint to them
        $coaches = User::whereHas('role', fn ($q) => $q->where('name', 'Pelatih'))->get();
        $athletes = User::whereHas('role', fn ($q) => $q->where('name', 'Atlet'))->get();

        if ($coaches->isEmpty() || $athletes->isEmpty()) {
            return; // Needs the base DatabaseSeeder to run first
        }

        $baseCoach = $coaches->first();

        // 3. Event Types & Points
        $eventTypes = ['Road Race', 'Criterium', 'Individual Time Trial (ITT)', 'Team Time Trial (TTT)', 'Cross Country (XC)', 'Track'];
        foreach ($eventTypes as $type) {
            JenisEvent::firstOrCreate(['name' => $type, 'coach_id' => $baseCoach->id]);
        }

        $eventPoints = [
            '1st Place',
            '2nd Place',
            '3rd Place',
            'Top 10',
            'Finisher',
        ];
        foreach ($eventPoints as $pointStr) {
            PoinEvent::firstOrCreate(['name' => $pointStr, 'coach_id' => $baseCoach->id]);
        }

        $allCategories = Kategori::all();
        $allExerciseTypes = JenisLatihan::all();
        $allEventTypes = JenisEvent::all();
        $allEventPoints = PoinEvent::all();

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

        // 5. Physical Metrics (Past 6 months, weekly)
        foreach ($athletes as $athlete) {
            $baseWeight = rand(55, 80) + (rand(0, 99) / 100);
            $baseHeight = rand(160, 185);
            $baseRestingHr = rand(45, 65);
            $currentDate = Carbon::now()->subMonths(6);

            while ($currentDate <= Carbon::now()) {
                DataFisik::create([
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
            if ($coachAthletes->isEmpty()) {
                continue;
            }

            // Past sessions
            for ($i = 0; $i < 40; $i++) {
                $sessionDate = Carbon::now()->subDays(rand(1, 180));
                $intensity = ['low', 'medium', 'high', 'very_high'][rand(0, 3)];
                $duration = rand(45, 180);

                $session = SesiLatihan::create([
                    'coach_id' => $coach->id,
                    'exercise_type_id' => $allExerciseTypes->random()->id,
                    'title' => 'Sesi Latihan '.$sessionDate->format('M d'),
                    'description' => 'Sesi latihan '.$intensity.' intensitas.',
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

                        LogLatihan::create([
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
                SesiLatihan::create([
                    'coach_id' => $coach->id,
                    'exercise_type_id' => $allExerciseTypes->random()->id,
                    'title' => 'Sesi Mendatang '.$sessionDate->format('M d'),
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
            for ($i = 0; $i < 30; $i++) { // 30 standalone logs per athlete
                $logDate = Carbon::now()->subDays(rand(1, 180));
                $duration = rand(30, 90);
                $distance = round($duration * (rand(15, 25) / 60), 2);

                LogLatihan::create([
                    'athlete_id' => $athlete->id,
                    'date' => $logDate,
                    'title' => 'Latihan Mandiri '.$logDate->format('d/m'),
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

        // 8. Athlete Profiles (ProfilAtlet)
        foreach ($athletes as $athlete) {
            ProfilAtlet::create([
                'user_id' => $athlete->id,
                'profile_photo_path' => 'avatars/dummy-athlete-'.$athlete->id.'.png',
                'birth_certificate_path' => 'documents/birth-cert-'.$athlete->id.'.pdf',
                'family_card_path' => 'documents/family-card-'.$athlete->id.'.pdf',
                'id_card_path' => 'documents/ktp-'.$athlete->id.'.pdf',
                'license_path' => 'documents/license-'.$athlete->id.'.pdf',
                'uci_id' => 'INA'.rand(2000000, 2099999),
                'license_valid_until' => Carbon::now()->addYear()->subDays(rand(0, 180)),
            ]);
        }

        // 9. Events (Generate 15 events, past and future)
        for ($i = 0; $i < 15; $i++) {
            $isPast = $i < 10;
            $eventDate = $isPast ? Carbon::now()->subDays(rand(10, 120)) : Carbon::now()->addDays(rand(10, 90));
            $eventCoach = $coaches->random();

            $event = Event::create([
                'coach_id' => $eventCoach->id,
                'event_type_id' => $allEventTypes->random()->id,
                'title' => ($isPast ? 'Kejuaraan Balap Sepeda ' : 'Persiapan Lomba ').fake()->city().' Series '.($i + 1),
                'description' => 'Event balapan lintasan resmi daerah tingkat regional.',
                'location' => fake()->city().' Velodrome',
                'event_date' => $eventDate,
                'requires_license' => rand(0, 1) === 1,
            ]);

            // Assign athletes to event
            $participants = User::whereHas('role', fn ($q) => $q->where('name', 'Atlet'))->inRandomOrder()->take(rand(4, 9))->get();
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

        // 10. Messages (Detailed Chat History)
        $chatTemplates = [
            ['sender' => 'athlete', 'text' => 'Coach, untuk latihan endurance hari ini cukup melelahkan di kilometer akhir.'],
            ['sender' => 'coach', 'text' => 'Bagus, itu normal. Coba perhatikan cadence kamu agar tidak terlalu cepat lelah. Bagaimana pernapasan?'],
            ['sender' => 'athlete', 'text' => 'Pernapasan aman coach, tapi paha kanan terasa agak tegang.'],
            ['sender' => 'coach', 'text' => 'Lakukan stretching tambahan malam ini dan perbanyak minum air hangat.'],
            ['sender' => 'athlete', 'text' => 'Baik coach, besok jadwal latihan recovery kan?'],
            ['sender' => 'coach', 'text' => 'Betul, besok active rest saja. Jaga detak jantung di zone 1-2.'],
            ['sender' => 'athlete', 'text' => 'Siap coach, terima kasih banyak sarannya.'],
            ['sender' => 'coach', 'text' => 'Sama-sama. Jangan lupa input data berat badan dan denyut nadi istirahat besok pagi ya.'],
            ['sender' => 'athlete', 'text' => 'Sudah saya update di sistem coach, grafik data fisik saya sudah aman.'],
            ['sender' => 'coach', 'text' => 'Mantap, saya cek perkembangan VO2 Max kamu di dashboard membaik. Pertahankan!'],
        ];

        foreach ($coaches as $coach) {
            $coachAthletes = User::where('coach_id', $coach->id)->get();
            foreach ($coachAthletes as $athlete) {
                $baseTime = Carbon::now()->subDays(10);
                foreach ($chatTemplates as $idx => $template) {
                    $senderId = $template['sender'] === 'coach' ? $coach->id : $athlete->id;
                    $receiverId = $template['sender'] === 'coach' ? $athlete->id : $coach->id;

                    Pesan::create([
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'content' => $template['text'],
                        'is_read' => $idx < count($chatTemplates) - 2 ? true : rand(0, 1) === 1,
                        'created_at' => $baseTime->copy()->addHours($idx * 6 + rand(1, 120)),
                        'updated_at' => $baseTime->copy()->addHours($idx * 6 + rand(1, 120)),
                    ]);
                }
            }
        }

        // 11. Bug Reports (LaporanBug)
        $bugReports = [
            'Gagal mengunggah foto profil di halaman profil atlet',
            'Menu grafik performa lambat dimuat saat data latihan terlalu banyak',
            'Error 500 ketika coach memberikan rekomendasi latihan kosong',
            'Notifikasi pesan tidak langsung muncul secara real-time',
            'Kesalahan kalkulasi total kalori pada log latihan bulanan',
            'Tombol edit log latihan tidak merespon di perangkat mobile',
            'Tampilan tabel event berantakan di layar ukuran kecil',
            'Halaman manajemen atlet tidak menampilkan data kategori dengan benar',
        ];

        $allUsers = User::all();
        foreach ($bugReports as $index => $title) {
            $status = ['pending', 'in_progress', 'resolved'][rand(0, 2)];
            $reporter = $allUsers->random();

            LaporanBug::create([
                'title' => $title,
                'description' => 'Ditemukan error saat sedang melakukan simulasi UAT di fitur terkait. Mohon perbaikan secepatnya.',
                'image_path' => ['bugs/dummy-screenshot-'.($index + 1).'.png'],
                'reporter_name' => $reporter->name,
                'reporter_contact' => $reporter->email,
                'url' => '/dashboard',
                'user_id' => $reporter->id,
                'status' => $status,
                'in_progress_at' => $status !== 'pending' ? Carbon::now()->subDays(rand(1, 5)) : null,
                'resolved_at' => $status === 'resolved' ? Carbon::now()->subDays(rand(0, 2)) : null,
            ]);
        }
    }
}
