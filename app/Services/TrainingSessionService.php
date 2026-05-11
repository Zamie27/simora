<?php

namespace App\Services;

use App\Models\LogLatihan;
use App\Models\SesiLatihan;
use App\Models\User;
use App\Notifications\NewTrainingSessionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TrainingSessionService
{
    /**
     * Create a new training session with many athletes.
     */
    public function createSession(array $data, array $athleteIds): SesiLatihan
    {
        return DB::transaction(function () use ($data, $athleteIds) {
            $session = SesiLatihan::create($data);
            $session->athletes()->sync($athleteIds);

            // Create initial logs for each athlete for the starting date
            foreach ($athleteIds as $athleteId) {
                LogLatihan::create([
                    'training_session_id' => $session->id,
                    'athlete_id' => $athleteId,
                    'date' => $session->scheduled_date,
                    'type' => $session->exerciseType->name ?? 'General',
                    'completion_status' => 'not_started',
                    'attendance_status' => 'absent',
                ]);
            }

            // Notify assigned athletes
            $athletes = User::whereIn('id', $athleteIds)->get();
            Notification::send($athletes, new NewTrainingSessionNotification($session));

            return $session;
        });
    }

    /**
     * Update an existing session.
     */
    public function updateSession(SesiLatihan $session, array $data, array $athleteIds): SesiLatihan
    {
        return DB::transaction(function () use ($session, $data, $athleteIds) {
            $session->update($data);
            if (! empty($athleteIds)) {
                $session->athletes()->sync($athleteIds);
            }

            return $session;
        });
    }

    /**
     * Delete a session.
     */
    public function deleteSession(SesiLatihan $session): bool
    {
        return $session->delete();
    }
}
