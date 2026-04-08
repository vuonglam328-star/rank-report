<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordGroupItem extends Model
{
    protected $fillable = ['keyword_group_id', 'keyword_id'];

    public function keywordGroup(): BelongsTo
    {
        return $this->belongsTo(KeywordGroup::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
