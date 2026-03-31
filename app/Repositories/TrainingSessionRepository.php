<?php

namespace App\Repositories;

use App\Models\TrainingSession;
use Illuminate\Database\Eloquent\Collection;

class TrainingSessionRepository
{
    /**
     * Get upcoming sessions for an athlete.
     */
    public function getUpcomingForAthlete(int $athleteId): Collection
    {
        return TrainingSession::whereHas('athletes', function ($query) use ($athleteId) {
            $query->where('users.id', $athleteId);
        })
            ->with(['exerciseType', 'coach'])
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date', 'asc')
            ->get();
    }

    /**
     * Get coached sessions.
     */
    public function getCoachedSessions(int $coachId): Collection
    {
        return TrainingSession::where('coach_id', $coachId)
            ->with(['exerciseType', 'athletes'])
            ->withCount('athletes')
            ->orderBy('scheduled_date', 'desc')
            ->get();
    }

    /**
     * Find session with all needed relations.
     */
    public function findWithDetails(int $id): ?TrainingSession
    {
        return TrainingSession::with([
            'exerciseType',
            'coach',
            'athletes',
            'logs.athlete',
            'logs.attachments',
        ])->find($id);
    }
}
