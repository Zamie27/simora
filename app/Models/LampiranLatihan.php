<?php

namespace App\Models;

use Database\Factories\TrainingAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranLatihan extends Model
{
    protected $table = 'training_attachments';

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
        return $this->belongsTo(LogLatihan::class, 'training_log_id');
    }
}
