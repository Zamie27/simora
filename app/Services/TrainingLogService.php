<?php

namespace App\Services;

use App\Models\PhysicalMetric;
use App\Models\TrainingLog;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\UploadedFile;

class TrainingLogService
{
    public function __construct(
        private TrainingLogRepository $repository,
        private TrainingMetricsService $metricsService
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

        // Use provided date or default to current date
        $data['date'] = $data['date'] ?? now()->toDateString();

        $this->syncPhysicalMetrics($athleteId, $data);

        // Auto-calculate all metrics using TrainingMetricsService
        $user = User::with('latestPhysicalMetric')->find($athleteId);
        $physicalMetric = $user?->latestPhysicalMetric;

        $calculatedMetrics = $this->metricsService->calculateMetrics($data, $physicalMetric);

        $data = array_merge($data, array_filter($calculatedMetrics, fn ($val) => $val !== null));

        $log = TrainingLog::create($data);

        if ($attachments) {
            $this->storeAttachments($log, $attachments);
        }

        return $log->load(['attachments', 'session.exerciseType']);
    }

    /**
     * Update a training log.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>|null  $attachments
     */
    public function update(TrainingLog $log, array $data, ?array $attachments = null): TrainingLog
    {
        // Restriction: Only same day edit allowed for athletes through specific UI,
        // but we allow the service to perform the update if the controller passes it.
        // The controller should handle authorization and policy.
        // We'll keep a basic check or just remove this to allow Coach edits to work for older logs too.
        // if (! $log->date?->isToday()) {
        //     abort(403, 'Sesi latihan hanya dapat diedit pada hari yang sama.');
        // }

        $this->syncPhysicalMetrics($log->athlete_id, $data);

        // Auto-calculate all metrics using TrainingMetricsService
        $user = User::with('latestPhysicalMetric')->find($log->athlete_id);
        $physicalMetric = $user?->latestPhysicalMetric;

        $calculatedMetrics = $this->metricsService->calculateMetrics($data, $physicalMetric);

        $mergedData = array_merge($data, array_filter($calculatedMetrics, fn ($val) => $val !== null));

        $log->update($mergedData);

        if ($attachments) {
            $this->storeAttachments($log, $attachments);
        }

        return $log->fresh(['attachments', 'session.exerciseType']);
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

    /**
     * Sync physical metrics if height or weight were provided.
     */
    private function syncPhysicalMetrics(int $athleteId, array &$data): void
    {
        if (! empty($data['weight']) || ! empty($data['height'])) {
            $user = User::with('latestPhysicalMetric')->find($athleteId);
            $latest = $user?->latestPhysicalMetric;

            $weight = $data['weight'] ?? $latest?->weight;
            $height = $data['height'] ?? $latest?->height;
            $age = $latest?->age ?? $user?->age ?? 30; // fallback

            // Only create new entry if it's a different day, or update if it's today
            if ($latest && $latest->recorded_at && $latest->recorded_at->isToday()) {
                $latest->update([
                    'weight' => $weight,
                    'height' => $height,
                ]);
            } else {
                PhysicalMetric::create([
                    'user_id' => $athleteId,
                    'weight' => $weight,
                    'height' => $height,
                    'age' => $age,
                    'recorded_at' => now(),
                ]);
            }

            // Clean up the data array so it doesn't cause SQL errors when creating TrainingLog
            unset($data['weight']);
            unset($data['height']);
        }
    }
}
