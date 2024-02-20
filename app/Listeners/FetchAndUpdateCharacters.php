<?php

namespace App\Listeners;

use App\Events\UpdateCharacters;
use App\Services\BattleNetService;
use App\Services\RaiderIOService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FetchAndUpdateCharacters implements ShouldQueue
{
    private BattleNetService $battleNetService;
    private RaiderIOService $raiderIOService;

    public function __construct(BattleNetService $battleNetService, RaiderIOService $raiderIOService)
    {
        $this->battleNetService = $battleNetService;
        $this->raiderIOService = $raiderIOService;
    }

    /**
     * Handle the event.
     */
    public function handle(UpdateCharacters $event): void
    {
        $event->characterUpdate->update([
            'status' => 'started'
        ]);

        $characters = $this->battleNetService->getCharacters(user: $event->user);

        foreach ($characters->wow_accounts[0]->characters as $character) {
            if ($character->level !== 70) {
                continue;
            }

            $characterData = $this->raiderIOService->fetchCharacterData(region: 'EU', realm: $character->realm->slug, characterName: $character->name, user: $event->user);
            $this->raiderIOService->storeOrUpdateCharacterDataWhenLoggingIn(user: $event->user, data: $characterData);
        }

        $event->characterUpdate->update([
            'status' => 'completed'
        ]);
    }
}
