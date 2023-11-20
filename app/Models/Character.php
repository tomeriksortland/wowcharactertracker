<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Character extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function characterSearches() : HasMany
    {
        return $this->hasMany(CharacterSearch::class);
    }

    public function score() : HasOne
    {
        return $this->hasOne(Score::class);
    }

    public function previousScore() : HasOne
    {
        return $this->hasOne(PreviousScore::class);
    }

    public function highestLevelRuns() : BelongsToMany
    {
        return $this->belongsToMany(HighestLevelRun::class, 'character_highest_level_run', 'character_id', 'highest_level_run_id');
    }
}
