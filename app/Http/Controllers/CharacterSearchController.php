<?php

namespace App\Http\Controllers;

use App\Http\Requests\CharacterSearchRequest;
use App\Models\Character;
use App\Models\CharacterSearch;
use App\Models\HighestLevelRun;
use App\Services\RaiderIOService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CharacterSearchController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('characterSearch/Index', [
            'lastSixCharacterSearches' => CharacterSearch::query()
                ->where('character_searches.user_id', Auth::id())
                ->join('characters', 'character_searches.character_id', '=', 'characters.id')
                ->join('scores', 'characters.id', '=', 'scores.character_id')
                ->select('characters.id as id', 'character_searches.user_id as user_id',
                    'name', 'class', 'spec', 'thumbnail', 'realm', 'searched_at', 'region', 'profile_url', 'overall', 'overall_color',
                    'tank', 'tank_color', 'healer', 'healer_color', 'dps', 'dps_color')
                ->orderByDesc('searched_at')
                ->take(6)
                ->get()
        ]);
    }

    public function show(Character $character): InertiaResponse
    {
        return Inertia::render('characterSearch/Show', [
            'character' => Character::with(['score', 'highestLevelRuns' => function ($query) {
                return $query->with(['characters.score'])->orderBy('key_level', 'desc')->limit(5);
            }])->find($character->id)
        ]);
    }

    public function store(CharacterSearchRequest $request, RaiderIOService $raiderIOService): RedirectResponse
    {
        $apiData = $raiderIOService->fetchCharacterData($request->region, $request->realm, $request->characterName);
        $character = $raiderIOService->storeOrUpdateCharacterDataWhenSearching(Auth::user(), $apiData, true);
        $raiderIOService->fetchRunInformation($character);

        return Redirect::route('character-search.show', $character);

    }
}
