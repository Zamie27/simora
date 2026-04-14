<?php

namespace App\Console\Commands;

use App\Models\TrainingLog;
use App\Models\TrainingSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateRecurringTrainingLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'training:generate-recurring-logs';

    /**
     * The console command description.
     */
    protected $description = 'Generate training logs for recurring weekly sessions when today matches their scheduled day';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        $sessions = TrainingSession::where('repeat_weekly', true)
            ->with('athletes')
            ->get()
            ->filter(fn (TrainingSession $session) => $session->scheduled_date->dayOfWeek === $todayDayOfWeek);

        if ($sessions->isEmpty()) {
            $this->info('No recurring sessions match today\'s day of week.');

            return self::SUCCESS;
        }

        $createdCount = 0;

        foreach ($sessions as $session) {
            foreach ($session->athletes as $athlete) {
                // Check if a log already exists for this athlete + session + today
                $exists = TrainingLog::where('training_session_id', $session->id)
                    ->where('athlete_id', $athlete->id)
                    ->whereDate('date', $today->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                TrainingLog::create([
                    'training_session_id' => $session->id,
                    'athlete_id' => $athlete->id,
                    'date' => $today->toDateString(),
                    'type' => $session->exerciseType->name ?? 'General',
                    'completion_status' => 'not_started',
                    'attendance_status' => 'absent',
                ]);

                $createdCount++;
            }
        }

        $this->info("Generated {$createdCount} recurring training log(s) for today.");
        Log::info("GenerateRecurringTrainingLogs: Created {$createdCount} log(s) for {$sessions->count()} session(s).");

        return self::SUCCESS;
    }
}
