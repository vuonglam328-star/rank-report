<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeywordGroup extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'description'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'keyword_group_items');
    }

    public function items(): HasMany
    {
        return $this->hasMany(KeywordGroupItem::class);
    }
}
