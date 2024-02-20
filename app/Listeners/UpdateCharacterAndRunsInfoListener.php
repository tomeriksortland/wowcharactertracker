<?php

namespace App\Listeners;

use App\Events\UpdateCharacterAndRunsInfo;
use App\Services\BattleNetService;
use App\Services\RaiderIOService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCharacterAndRunsInfoListener
{
    private RaiderIOService $raiderIOService;
    public function __construct($raiderIOService)
    {
        $this->raiderIOService = $raiderIOService;
    }


    public function handle(UpdateCharacterAndRunsInfo $event): void
    {
        sleep(10);
        $event->broadcastWith();
    }
}
