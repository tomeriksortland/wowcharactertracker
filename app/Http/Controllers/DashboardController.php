<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index() : InertiaResponse
    {
        return Inertia::render('Dashboard', [
            'userCharacters' => Character::query()
                ->where('characters.user_id', Auth::id())
                ->join('scores', 'characters.id', '=', 'scores.character_id')
                ->take(8)
                ->orderByDesc('overall')
                ->get(),
            'allCharactersFetched' => CharacterUpdate::where('user_id', Auth()->id())->first()->status
        ]);
    }
}
