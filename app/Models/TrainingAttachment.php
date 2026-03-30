<?php

namespace App\Models;

use Database\Factories\TrainingAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAttachment extends Model
{
    /** @use HasFactory<TrainingAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'training_log_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    /**
     * Get the training log this attachment belongs to.
     *
     * @return BelongsTo<TrainingLog, $this>
     */
    public function trainingLog(): BelongsTo
    {
        return $this->belongsTo(TrainingLog::class);
    }
}
