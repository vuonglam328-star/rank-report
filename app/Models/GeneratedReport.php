<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'snapshot_id',
        'report_template_id',
        'report_title',
        'summary_text',
        'selected_competitors_json',
        'selected_sections_json',
        'pdf_path',
        'status',
        'error_message',
    ];

    protected $casts = [
        'selected_competitors_json' => 'array',
        'selected_sections_json'    => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'report_template_id');
    }

    public function getPdfUrlAttribute(): ?string
    {
        if ($this->pdf_path && Storage::exists($this->pdf_path)) {
            return route('reports.download', $this->id);
        }
        return null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'ready'      => 'success',
            'generating' => 'warning',
            'pending'    => 'info',
            'failed'     => 'danger',
            default      => 'secondary',
        };
    }
}
