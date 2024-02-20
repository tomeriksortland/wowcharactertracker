<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HighestLevelRunResource extends JsonResource
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
            'dungeon' => $this->dungeon,
            'keyLevel' => $this->key_level,
            'completionTime' => $this->completion_time,
            'dungeonTotalTime' => $this->dungeon_total_time,
            'keystoneUpgrades' => $this->keystone_upgrades,
            'affixOne' => $this->affix_one,
            'affixOneIcon' => $this->affix_one_icon,
            'affixTwo' => $this->affix_two,
            'affixTwoIcon' => $this->affix_two_icon,
            'affixThree' => $this->affix_three,
            'affixThreeIcon' => $this->affix_three_icon,
            'seasonalAffix' => $this->seasonal_affix,
            'seasonalAffixIcon' => $this->seasonal_affix_icon,
            'runId' => $this->run_id,
            'runUrl' => $this->run_url,
            'completedAt' => $this->completed_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'characters' => CharacterResource::collection($this->whenLoaded('characters'))
        ];
    }
}
