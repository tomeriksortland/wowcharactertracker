<?php

namespace App\Http\Controllers\Auth;

use App\Events\UpdateCharacters;
use App\Http\Controllers\Controller;
use App\Models\CharacterUpdate;
use App\Services\BattleNetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;

class BattleNetLoginController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('battlenet')->scopes(['wow.profile'])->redirect();
    }

    public function callback(BattleNetService $battleNetService) : RedirectResponse
    {
        $user = $battleNetService->getUser();

        Auth::login($user);

        $characterUpdate = CharacterUpdate::create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        UpdateCharacters::dispatch($user, $characterUpdate);

        return Redirect::route('dashboard.index');
    }
}
