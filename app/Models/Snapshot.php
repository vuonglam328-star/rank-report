<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Snapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'snapshot_name',
        'report_date',
        'snapshot_type',
        'source_file_path',
        'notes',
        'status',
        'total_keywords',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function keywordRankings(): HasMany
    {
        return $this->hasMany(KeywordRanking::class);
    }

    public function generatedReports(): HasMany
    {
        return $this->hasMany(GeneratedReport::class);
    }

    // ─── Business Logic ───────────────────────────────────────────────────────

    /**
     * Get the previous completed snapshot for this project (by date).
     */
    public function previousSnapshot(): ?Snapshot
    {
        return Snapshot::where('project_id', $this->project_id)
            ->where('report_date', '<', $this->report_date)
            ->where('status', 'completed')
            ->orderBy('report_date', 'desc')
            ->first();
    }

    /**
     * Get the next snapshot for this project (by date).
     */
    public function nextSnapshot(): ?Snapshot
    {
        return Snapshot::where('project_id', $this->project_id)
            ->where('report_date', '>', $this->report_date)
            ->where('status', 'completed')
            ->orderBy('report_date', 'asc')
            ->first();
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'completed'  => 'success',
            'processing' => 'warning',
            'pending'    => 'info',
            'failed'     => 'danger',
            default      => 'secondary',
        };
    }
}
