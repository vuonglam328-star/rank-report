<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cover_title',
        'agency_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'layout_config_json',
        'is_default',
    ];

    protected $casts = [
        'layout_config_json' => 'array',
        'is_default'         => 'boolean',
    ];

    public function generatedReports(): HasMany
    {
        return $this->hasMany(GeneratedReport::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }
        return asset('assets/img/agency-logo-placeholder.png');
    }

    public function getDefaultSections(): array
    {
        return $this->layout_config_json['sections'] ?? [
            'cover',
            'executive_summary',
            'kpi_summary',
            'position_chart',
            'distribution_chart',
            'top_keywords',
            'landing_pages',
            'competitor_monitoring',
            'action_items',
        ];
    }
}
