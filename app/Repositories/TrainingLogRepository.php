<?php

namespace App\Repositories;

use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TrainingLogRepository
{
    /**
     * Get training logs for an athlete within a date range.
     */
    public function getForAthlete(int $athleteId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = TrainingLog::forAthlete($athleteId)
            ->with(['session.exerciseType', 'attachments'])
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        return $query->get();
    }

    /**
     * Get statistics for an athlete within a date range.
     *
     * @return array{total_distance_km: float, total_duration_minutes: int, avg_speed: float, avg_rpm: float, total_sessions: int, completed_sessions: int}
     */
    public function getStatistics(int $athleteId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TrainingLog::forAthlete($athleteId);

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        $stats = $query->selectRaw('
            COALESCE(SUM(distance_km), 0) as total_distance_km,
            COALESCE(SUM(duration_minutes), 0) as total_duration_minutes,
            COALESCE(AVG(avg_speed), 0) as avg_speed,
            COALESCE(AVG(rpm), 0) as avg_rpm,
            COUNT(*) as total_sessions,
            SUM(CASE WHEN completion_status = "completed" THEN 1 ELSE 0 END) as completed_sessions
        ')->first();

        return [
            'total_distance_km' => round((float) $stats->total_distance_km, 2),
            'total_duration_minutes' => (int) $stats->total_duration_minutes,
            'avg_speed' => round((float) $stats->avg_speed, 2),
            'avg_rpm' => round((float) $stats->avg_rpm, 1),
            'total_sessions' => (int) $stats->total_sessions,
            'completed_sessions' => (int) $stats->completed_sessions,
        ];
    }

    /**
     * Get chart data for performance trends.
     *
     * @return array<int, array{date: string, distance_km: float, duration_minutes: int, avg_speed: float, rpm: float}>
     */
    public function getPerformanceTrend(int $athleteId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TrainingLog::forAthlete($athleteId)
            ->select('date', 'distance_km', 'duration_minutes', 'avg_speed', 'rpm', 'intensity')
            ->orderBy('date');

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        return $query->get()->map(fn ($log) => [
            'date' => $log->date->format('Y-m-d'),
            'distance_km' => (float) $log->distance_km,
            'duration_minutes' => (int) $log->duration_minutes,
            'avg_speed' => (float) $log->avg_speed,
            'rpm' => (float) $log->rpm,
            'intensity' => $log->intensity,
        ])->toArray();
    }

    /**
     * Get comparison data for multiple athletes.
     */
    public function getComparisonData(array $athleteIds, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = TrainingLog::whereIn('athlete_id', $athleteIds)
            ->select('athlete_id')
            ->selectRaw('COALESCE(SUM(distance_km), 0) as total_distance_km')
            ->selectRaw('COALESCE(AVG(avg_speed), 0) as avg_speed')
            ->selectRaw('COALESCE(AVG(rpm), 0) as avg_rpm')
            ->selectRaw('COALESCE(SUM(duration_minutes), 0) as total_duration_minutes')
            ->selectRaw('COUNT(*) as total_sessions')
            ->groupBy('athlete_id');

        if ($startDate && $endDate) {
            $query->forPeriod($startDate, $endDate);
        }

        return $query->get()->map(fn ($row) => [
            'athlete_id' => $row->athlete_id,
            'total_distance_km' => round((float) $row->total_distance_km, 2),
            'avg_speed' => round((float) $row->avg_speed, 2),
            'avg_rpm' => round((float) $row->avg_rpm, 1),
            'total_duration_minutes' => (int) $row->total_duration_minutes,
            'total_sessions' => (int) $row->total_sessions,
        ])->toArray();
    }

    /**
     * Get upcoming sessions for an athlete.
     */
    public function getUpcomingSessions(int $athleteId): \Illuminate\Support\Collection
    {
        $sessions = TrainingSession::whereHas('athletes', function ($q) use ($athleteId) {
            $q->where('users.id', $athleteId);
        })
            ->upcoming()
            ->with([
                'coach:id,name',
                'exerciseType',
            ])
            ->get();

        $processed = collect();

        foreach ($sessions as $session) {
            // Ensure we have a model instance
            if (! $session instanceof TrainingSession) {
                continue;
            }

            $instanceDate = $session->getActiveInstanceDate();

            // Check if there is already a log for this specific instance date
            $log = TrainingLog::where('training_session_id', $session->id)
                ->where('athlete_id', $athleteId)
                ->whereDate('date', '=', $instanceDate->toDateString())
                ->first();

            // Hide if already completed for this instance
            if ($log && $log->completion_status === 'completed') {
                continue;
            }

            // Append instance info for the frontend
            $session->instance_date = $instanceDate->toDateString();
            $session->current_log = $log;

            $processed->push($session);
        }

        return $processed;
    }

    /**
     * Get athlete ranking based on performance (avg_speed) in a given period.
     */
    public function getAthleteRanking(?array $athleteIds = null, int $days = 30): array
    {
        $query = User::whereRole('Atlet')
            ->with(['athleteProfile', 'category'])
            ->withCount(['trainingLogs as performance_score' => function ($q) use ($days) {
                $q->where('date', '>=', now()->subDays($days)->toDateString())
                    ->select(\Illuminate\Support\Facades\DB::raw('ROUND(AVG(avg_speed), 2)'));
            }])
            ->withCount(['trainingLogs as total_distance' => function ($q) use ($days) {
                $q->where('date', '>=', now()->subDays($days)->toDateString())
                    ->select(\Illuminate\Support\Facades\DB::raw('COALESCE(SUM(distance_km), 0)'));
            }])
            ->whereHas('trainingLogs', function ($q) use ($days) {
                $q->where('date', '>=', now()->subDays($days)->toDateString())
                    ->where('avg_speed', '>', 0);
            })
            ->orderByDesc('performance_score');

        if ($athleteIds) {
            $query->whereIn('id', $athleteIds);
        }

        return $query->get()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'category_name' => $user->category?->name ?? 'Personal',
            'performance_score' => (float) $user->performance_score,
            'total_distance' => (float) $user->total_distance,
        ])->toArray();
    }
}
