<?php

namespace App\Http\Controllers;

use App\Events\UpdateCharacterAndRunsInfo;
use App\Http\Requests\CharacterSearchRequest;
use App\Http\Resources\CharacterResource;
use App\Models\Character;
use App\Models\CharacterSearch;
use App\Services\RaiderIOService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CharacterSearchController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('characterSearch/Index', [
            'lastSixCharacterSearches' => CharacterResource::collection(Character::leftJoin('character_searches', 'characters.id', '=', 'character_searches.character_id')
                ->with('score')
                ->whereHas('characterSearches', function ($query) {
                    $query->select('character_id')
                        ->where('user_id', auth()->id());
                })
                ->take(6)
                ->orderByDesc('searched_at')
                ->get(['characters.*']))
        ]);
    }

    public function show(Character $character): InertiaResponse
    {
        $lastCharacterSearch = CharacterSearch::where('character_id', $character->id)->latest()->first();
        if(empty($lastCharacterSearch) || $lastCharacterSearch->searched_at < Carbon::now()->subHours(2)) {
            UpdateCharacterAndRunsInfo::dispatch($character, Auth::user());
        }

        /*'character' => new CharacterResource(Character::with([
        'score',
        'recentRuns' => function ($query) {
            return $query->with(['characters.score'])->orderBy('completed_at', 'desc')->limit(5);
        },
        'highestLevelRuns' => function ($query) {
            return $query->with(['characters.score'])->orderBy('key_level', 'desc')->limit(5);
        }
    ])->find($character->id))*/

        return Inertia::render('characterSearch/Show', [
            'characterId' => $character->id,
        ]);
    }

    public function store(CharacterSearchRequest $request, RaiderIOService $raiderIOService): RedirectResponse
    {
        $user = Auth::user();
        $characterApiData = $raiderIOService->fetchCharacterData(region: $request->get('region'), realm: $request->get('realm'), characterName: $request->get('characterName'), user: $user);
        $character = $raiderIOService->storeOrUpdateCharacterDataWhenSearching(user: $user, data: $characterApiData);

        // Get characters that where present in the runs connected to the character searched for
        $raiderIOService->fetchHighestLevelKeysRoster(character: $character, user: $user);
        $raiderIOService->fetchRecentRunsRoster(character: $character, user: $user);

        return Redirect::route('character-search.show', $character);
    }
}
