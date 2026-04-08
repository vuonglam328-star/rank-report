<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'keyword',
        'normalized_keyword',
        'keyword_type',
        'brand_flag',
        'tag',
    ];

    protected $casts = [
        'brand_flag' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(KeywordRanking::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            KeywordGroup::class,
            'keyword_group_items'
        );
    }

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /**
     * Normalize a keyword string for consistent comparison.
     * Lowercase, trim, collapse whitespace.
     */
    public static function normalize(string $keyword): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $keyword)));
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeBranded($query)
    {
        return $query->where('brand_flag', true);
    }

    public function scopeNonBranded($query)
    {
        return $query->where('brand_flag', false);
    }
}
