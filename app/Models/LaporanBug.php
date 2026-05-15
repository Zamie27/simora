<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanBug extends Model
{
    protected $table = 'bug_reports';

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'reporter_name',
        'reporter_contact',
        'url',
        'user_id',
        'status',
        'in_progress_at',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'image_path' => 'array',
            'in_progress_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get the user who reported the bug.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
