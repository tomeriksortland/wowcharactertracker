<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
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
            'characterId' => $this->character_id,
            'overall' => $this->overall,
            'overallColor' => $this->overall_color,
            'tank' => $this->tank,
            'tankColor' => $this->tank_color,
            'healer' => $this->healer,
            'healerColor' => $this->healer_color,
            'dps' => $this->dps,
            'dpsColor' => $this->dps_color,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
