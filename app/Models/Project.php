<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'domain',
        'project_type',
        'country_code',
        'device_type',
        'status',
        'is_main_project',
        'notes',
    ];

    protected $casts = [
        'is_main_project' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(Snapshot::class)->orderBy('report_date', 'desc');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function keywordGroups(): HasMany
    {
        return $this->hasMany(KeywordGroup::class);
    }

    public function generatedReports(): HasMany
    {
        return $this->hasMany(GeneratedReport::class);
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(Snapshot::class)->latestOfMany('report_date');
    }

    /** Main project → its assigned competitors */
    public function competitors(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_competitors',
            'main_project_id',
            'competitor_project_id'
        );
    }

    /** Competitor → main projects it is assigned to */
    public function mainProjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_competitors',
            'competitor_project_id',
            'main_project_id'
        );
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getDomainCleanAttribute(): string
    {
        $host = parse_url($this->domain, PHP_URL_HOST);
        return strtolower(rtrim($host ?? $this->domain, '/'));
    }

    public function getProjectTypeBadgeAttribute(): string
    {
        return match ($this->project_type) {
            'main'       => 'primary',
            'competitor' => 'danger',
            'partner'    => 'success',
            'benchmark'  => 'warning',
            default      => 'secondary',
        };
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMain($query)
    {
        return $query->where('project_type', 'main');
    }

    public function scopeCompetitor($query)
    {
        return $query->where('project_type', 'competitor');
    }
}
