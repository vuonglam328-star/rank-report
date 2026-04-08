<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class KeywordRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_id',
        'keyword_id',
        'current_position',
        'previous_position',
        'position_change',
        'search_volume',
        'target_url',
        'location',
        'device',
        'visibility_points',
        'raw_data_json',
    ];

    protected $casts = [
        'raw_data_json' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeTop3(Builder $query): Builder
    {
        return $query->whereNotNull('current_position')
                     ->where('current_position', '<=', 3);
    }

    public function scopeTop10(Builder $query): Builder
    {
        return $query->whereNotNull('current_position')
                     ->where('current_position', '<=', 10);
    }

    public function scopeTop20(Builder $query): Builder
    {
        return $query->whereNotNull('current_position')
                     ->where('current_position', '<=', 20);
    }

    public function scopeTop50(Builder $query): Builder
    {
        return $query->whereNotNull('current_position')
                     ->where('current_position', '<=', 50);
    }

    public function scopeTop100(Builder $query): Builder
    {
        return $query->whereNotNull('current_position')
                     ->where('current_position', '<=', 100);
    }

    public function scopeImproved(Builder $query): Builder
    {
        return $query->where('position_change', '>', 0);
    }

    public function scopeDeclined(Builder $query): Builder
    {
        return $query->where('position_change', '<', 0);
    }

    public function scopeRanked(Builder $query): Builder
    {
        return $query->whereNotNull('current_position');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPositionChangeColorAttribute(): string
    {
        if ($this->position_change > 0) return 'text-success';
        if ($this->position_change < 0) return 'text-danger';
        return 'text-muted';
    }

    public function getPositionChangeIconAttribute(): string
    {
        if ($this->position_change > 0) return '▲';
        if ($this->position_change < 0) return '▼';
        return '—';
    }

    public function getPositionGroupAttribute(): string
    {
        $pos = $this->current_position;
        if (!$pos) return 'outside';
        if ($pos <= 3)   return 'top_3';
        if ($pos <= 10)  return 'top_10';
        if ($pos <= 20)  return 'top_20';
        if ($pos <= 50)  return 'top_50';
        if ($pos <= 100) return 'top_100';
        return 'outside';
    }
}
