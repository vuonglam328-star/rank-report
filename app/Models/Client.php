<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'domain',
        'logo_path',
        'contact_name',
        'contact_email',
        'report_frequency',
        'notes',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function mainProjects(): HasMany
    {
        return $this->hasMany(Project::class)->where('project_type', 'main');
    }

    public function activeProjects(): HasMany
    {
        return $this->hasMany(Project::class)->where('status', 'active');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }
        return asset('assets/img/client-placeholder.png');
    }

    public function getDomainCleanAttribute(): string
    {
        $domain = $this->domain ?? '';
        $host = parse_url($domain, PHP_URL_HOST);
        return strtolower(rtrim($host ?? $domain, '/'));
    }
}
