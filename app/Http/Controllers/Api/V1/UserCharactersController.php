<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CharacterResource;
use App\Models\Character;
use App\Models\CharacterUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCharactersController extends Controller
{
    public function index(User $user)
    {
        $characterUpdate = CharacterUpdate::where('user_id', $user->id)->latest()->first();

        if ($characterUpdate->status !== 'completed') {
            return response()->json([
                'jobStatus' => $characterUpdate->status
            ]);
        }

        return response()->json([
            'characters' => CharacterResource::collection(Character::leftJoin('scores', 'characters.id', '=', 'scores.character_id')
                ->with('score')
                ->where('user_id', $user->id)
                ->orderByDesc('overall')
                ->take(8)
                ->get(['characters.*'])),
            'jobStatus' => $characterUpdate->status
        ]);
    }
}
