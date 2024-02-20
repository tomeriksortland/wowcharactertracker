<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'name' => $this->name,
            'race' => $this->race,
            'class' => $this->class,
            'spec' => $this->spec,
            'faction' => $this->faction,
            'thumbnail' => $this->thumbnail,
            'realm' => $this->realm,
            'region' => $this->region,
            'profileUrl' => $this->profile_url,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'score' => new ScoreResource($this->whenLoaded('score')),
            'highestLevelRuns' => HighestLevelRunResource::collection($this->whenLoaded('highestLevelRuns')
            ),
            'recentRuns' => RecentRunResource::collection($this->whenLoaded('recentRuns')
            ),
        ];
    }
}
