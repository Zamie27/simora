<?php

namespace App\Services;

use App\Models\TrainingLog;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\UploadedFile;

class TrainingLogService
{
    public function __construct(
        private TrainingLogRepository $repository
    ) {}

    /**
     * Create a training log for an athlete.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>|null  $attachments
     */
    public function create(int $athleteId, array $data, ?array $attachments = null): TrainingLog
    {
        $data['athlete_id'] = $athleteId;

        // Auto-calculate avg_speed if not provided
        if (empty($data['avg_speed']) && ! empty($data['distance_km']) && ! empty($data['duration_minutes']) && $data['duration_minutes'] > 0) {
            $data['avg_speed'] = round(($data['distance_km'] / $data['duration_minutes']) * 60, 2);
        }

        $log = TrainingLog::create($data);

        if ($attachments) {
            $this->storeAttachments($log, $attachments);
        }

        return $log->load(['attachments', 'session.exerciseType']);
    }

    /**
     * Update coach evaluation on a training log.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateEvaluation(TrainingLog $log, array $data): TrainingLog
    {
        $log->update($data);

        return $log->fresh();
    }

    /**
     * Store file attachments for a training log.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeAttachments(TrainingLog $log, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('training-attachments', 'public');

            $log->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
