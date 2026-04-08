<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCompetitor extends Model
{
    protected $fillable = ['main_project_id', 'competitor_project_id'];

    public function mainProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'main_project_id');
    }

    public function competitorProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'competitor_project_id');
    }
}
