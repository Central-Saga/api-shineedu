<?php

namespace App\Modules\Learning\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory, \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $table = 'assignment_submission';

    protected $fillable = [
        'assignment_id',
        'submitted_by',
        'submitted_at',
        'content_text',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'feedback',
        'score',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for status enum
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_REVISION_REQUESTED = 'REVISION_REQUESTED';
    public const STATUS_ACCEPTED = 'ACCEPTED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_REVISION_REQUESTED,
            self::STATUS_ACCEPTED,
        ];
    }

    // Relationships

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    // Helper methods

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function markAsAccepted(User $reviewer, ?string $feedback = null, ?float $score = null): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'feedback' => $feedback,
            'score' => $score,
        ]);
    }

    public function requestRevision(User $reviewer, string $feedback): void
    {
        $this->update([
            'status' => self::STATUS_REVISION_REQUESTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);
    }
}
