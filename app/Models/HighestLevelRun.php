<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HighestLevelRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function characters() : BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_highest_level_run', 'highest_level_run_id', 'character_id');
    }
}
