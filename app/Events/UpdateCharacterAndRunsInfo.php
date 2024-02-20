<?php

namespace App\Events;

use App\Models\Character;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateCharacterAndRunsInfo implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Character $character, public User|Authenticatable $user)
    {
        //
    }

    public function broadcastWith(): array
    {
        return [
            'characterId' => $this->character->id,
            'userId' => $this->user->id,
        ];
    }


    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(`characterUpdate.${$this->user->id}`),
        ];
    }
}
