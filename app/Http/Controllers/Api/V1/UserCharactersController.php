<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterUpdate;
use App\Models\User;
use Illuminate\Http\Request;

class UserCharactersController extends Controller
{
    public function index(User $user)
    {
        $characters = [];
        $characterUpdate = CharacterUpdate::where('user_id', $user->id)->latest()->first();

        if($characterUpdate->status === 'completed')
        {
            $characters = Character::query()
                ->join('mythic_plus_scores', 'characters.id', '=', 'mythic_plus_scores.character_id')
                ->orderByDesc('Overall')
                ->take(8)
                ->get();
        }



        return response()->json([
            'userCharacters' => $characters,
            'jobStatus' => $characterUpdate->status
        ]);
    }
}
