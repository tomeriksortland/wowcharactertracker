<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RecentRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function characters() : BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_recent_run', 'recent_run_id', 'character_id');
    }
}
