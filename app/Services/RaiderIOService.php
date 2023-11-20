<?php

namespace App\Services;

use App\Events\ApiErrorLog;
use App\Events\ApiLog;
use App\Models\Character;
use App\Models\CharacterSearch;
use App\Models\HighestLevelRun;
use App\Models\PreviousScore;
use App\Models\Score;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RaiderIOService
{

    private string $currentSeason = 'season-df-3';
    private string $previousSeason = 'season-df-2';
    private string $imageUrl = 'https://wow.zamimg.com/images/wow/icons/large/';

    private function getCharacterQueryParameters(): string
    {
        return "mythic_plus_scores_by_season:{$this->currentSeason}:{$this->previousSeason},mythic_plus_recent_runs,mythic_plus_highest_level_runs";
    }

    private function getAffixIconUrl(string $icon = '') : string
    {
        if(empty($icon)) return '';

        return $this->imageUrl . $icon . '.jpg';
    }

    public function fetchCharacterData(string $region, string $realm, string $characterName): \stdClass
    {

        $response = Http::get('https://raider.io/api/v1/characters/profile', [
            'region' => $region,
            'realm' => $realm,
            'name' => $characterName,
            'fields' => $this->getCharacterQueryParameters()
        ]);

        if ($response->status() === 200) {
            ApiLog::dispatch($response, Auth::user());
        } else {
            ApiErrorLog::dispatch($response, Auth::user());
        }

        return json_decode($response->body());
    }

    public function fetchRunInformation(Character $character) : void
    {
        foreach ($character->highestLevelRuns as $run) {
            $response = Http::get('https://raider.io/api/v1/mythic-plus/run-details', [
                'season' => $this->currentSeason,
                'id' => $run->run_id
            ]);

            if($response->status() === 200) {
                ApiLog::dispatch($response, Auth::user());
            } else {
                ApiErrorLog::dispatch($response, Auth::user());
            }

            $response = json_decode($response->body());

            foreach($response->roster as $roster)
            {
                $alreadyExistingCharacter = Character::where('region', $roster->character->region->slug)->where('realm', $roster->character->realm->name)->where('name', $roster->character->name)->first();

                $raiderIOCharacterData = $this->fetchCharacterData($roster->character->region->slug, $roster->character->realm->name, $roster->character->name);

                $character = Character::updateOrCreate(
                    [
                        'name' => $raiderIOCharacterData->name,
                        'realm' => $raiderIOCharacterData->realm,
                        'region' => $raiderIOCharacterData->region
                    ],
                    [
                        'user_id' => $alreadyExistingCharacter ? $alreadyExistingCharacter->user_id : 1,
                        'race' => $raiderIOCharacterData->race,
                        'class' => $raiderIOCharacterData->class,
                        'spec' => $raiderIOCharacterData->active_spec_name,
                        'faction' => $raiderIOCharacterData->faction,
                        'thumbnail' => $raiderIOCharacterData->thumbnail_url,
                        'realm' => $raiderIOCharacterData->realm,
                        'region' => $raiderIOCharacterData->region,
                        'profile_url' => $raiderIOCharacterData->profile_url
                    ]);

                Score::updateOrCreate(
                    [
                        'character_id' => $character->id,
                    ],
                    [
                        'character_id' => $character->id,
                        'overall' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->all->score,
                        'overall_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->all->color,
                        'tank' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->tank->score,
                        'tank_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->tank->color,
                        'healer' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->healer->score,
                        'healer_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->healer->color,
                        'dps' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->dps->score,
                        'dps_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[0]->segments->dps->color,
                    ]);

                PreviousScore::updateOrCreate(
                    [
                        'character_id' => $character->id,
                    ],
                    [
                        'character_id' => $character->id,
                        'season' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->season,
                        'overall' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->all->score,
                        'overall_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->all->color,
                        'tank' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->tank->score,
                        'tank_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->tank->color,
                        'healer' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->healer->score,
                        'healer_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->healer->color,
                        'dps' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->dps->score,
                        'dps_color' => $raiderIOCharacterData->mythic_plus_scores_by_season[1]->segments->dps->color,
                    ]);

                $character->highestLevelRuns()->syncWithoutDetaching($run->id);
            }
        }
    }

    public function storeOrUpdateCharacterDataWhenLoggingIn(User $user, mixed $data): Character
    {

        $character = Character::updateOrCreate(
            [
                'name' => $data->name,
                'realm' => $data->realm,
                'region' => $data->region
            ],
            [
                'user_id' => $user->id,
                'race' => $data->race,
                'class' => $data->class,
                'spec' => $data->active_spec_name,
                'faction' => $data->faction,
                'thumbnail' => $data->thumbnail_url,
                'realm' => $data->realm,
                'region' => $data->region,
                'profile_url' => $data->profile_url
            ]);

        $character->touch();

        $character->score()->updateOrCreate(
            [
                'character_id' => $character->id,
            ],
            [
                'character_id' => $character->id,
                'overall' => $data->mythic_plus_scores_by_season[0]->segments->all->score,
                'overall_color' => $data->mythic_plus_scores_by_season[0]->segments->all->color,
                'tank' => $data->mythic_plus_scores_by_season[0]->segments->tank->score,
                'tank_color' => $data->mythic_plus_scores_by_season[0]->segments->tank->color,
                'healer' => $data->mythic_plus_scores_by_season[0]->segments->healer->score,
                'healer_color' => $data->mythic_plus_scores_by_season[0]->segments->healer->color,
                'dps' => $data->mythic_plus_scores_by_season[0]->segments->dps->score,
                'dps_color' => $data->mythic_plus_scores_by_season[0]->segments->dps->color,
            ]);

        $character->previousScore()->updateOrCreate(
            [
                'character_id' => $character->id,
            ],
            [
                'character_id' => $character->id,
                'season' => $data->mythic_plus_scores_by_season[1]->season,
                'overall' => $data->mythic_plus_scores_by_season[1]->segments->all->score,
                'overall_color' => $data->mythic_plus_scores_by_season[1]->segments->all->color,
                'tank' => $data->mythic_plus_scores_by_season[1]->segments->tank->score,
                'tank_color' => $data->mythic_plus_scores_by_season[1]->segments->tank->color,
                'healer' => $data->mythic_plus_scores_by_season[1]->segments->healer->score,
                'healer_color' => $data->mythic_plus_scores_by_season[1]->segments->healer->color,
                'dps' => $data->mythic_plus_scores_by_season[1]->segments->dps->score,
                'dps_color' => $data->mythic_plus_scores_by_season[1]->segments->dps->color,
            ]);

        /*$i = 0;

        foreach($data->mythic_plus_recent_runs as $recentRun)
        {
            $character->mythicPlusRuns()->create([
                [
                    'dungeon' => $recentRun->dungeon,
                    'key_level' => $recentRun->mythic_level,
                    'completed_at' => $recentRun->completed_at,

                ],
                [

                ]
            ]);
        }*/

        $i = 0;
        foreach ($data->mythic_plus_highest_level_runs as $run) {
            if ($i === 5) break;

            $highestLevelRun = HighestLevelRun::updateOrCreate(
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completed_at' => $run->completed_at
                ],
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => Arr::get($run->affixes, 0)->name,
                    'affix_one_icon' => $this->getAffixIconUrl($run->affixes[0]->icon),
                    'affix_two' => isset($run->affixes[1]) ? Arr::get($run->affixes, 1)->name : '',
                    'affix_two_icon' => $this->getAffixIconUrl($run->affixes[1]->icon),
                    'affix_three' => isset($run->affixes[2]) ? Arr::get($run->affixes, 2)->name : '',
                    'affix_three_icon' => $this->getAffixIconUrl($run->affixes[2]->icon),
                    'seasonal_affix' => isset($run->affixes[3]) ? $run->affixes[3]->name : '',
                    'seasonal_affix_icon' => $this->getAffixIconUrl($run->affixes[3]->icon),
                    'run_id' => preg_match('/\/season-df-3\/([^\/-]+)/', $run->url, $matches) ? $matches[1] : null,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ]);

            $character->highestLevelRuns()->syncWithoutDetaching($highestLevelRun->id);

            $i++;
        }

        return $character;
    }

    public function storeOrUpdateCharacterDataWhenSearching(User $user, mixed $data, bool $search = false): Character
    {
        $alreadyExistingCharacter = Character::where('region', $data->region)->where('realm', $data->realm)->where('name', $data->name)->first();

        $character = Character::updateOrCreate(
            [
                'name' => $data->name,
                'realm' => $data->realm,
                'region' => $data->region
            ],
            [
                'user_id' => $alreadyExistingCharacter ? $alreadyExistingCharacter->user_id : 1,
                'race' => $data->race,
                'class' => $data->class,
                'spec' => $data->active_spec_name,
                'faction' => $data->faction,
                'thumbnail' => $data->thumbnail_url,
                'realm' => $data->realm,
                'region' => $data->region,
                'profile_url' => $data->profile_url
            ]);

        $character->score()->updateOrCreate(
            [
                'character_id' => $character->id,
            ],
            [
                'character_id' => $character->id,
                'overall' => $data->mythic_plus_scores_by_season[0]->segments->all->score,
                'overall_color' => $data->mythic_plus_scores_by_season[0]->segments->all->color,
                'tank' => $data->mythic_plus_scores_by_season[0]->segments->tank->score,
                'tank_color' => $data->mythic_plus_scores_by_season[0]->segments->tank->color,
                'healer' => $data->mythic_plus_scores_by_season[0]->segments->healer->score,
                'healer_color' => $data->mythic_plus_scores_by_season[0]->segments->healer->color,
                'dps' => $data->mythic_plus_scores_by_season[0]->segments->dps->score,
                'dps_color' => $data->mythic_plus_scores_by_season[0]->segments->dps->color,
            ]);

        $character->previousScore()->updateOrCreate(
            [
                'character_id' => $character->id,
            ],
            [
                'character_id' => $character->id,
                'season' => $data->mythic_plus_scores_by_season[1]->season,
                'overall' => $data->mythic_plus_scores_by_season[1]->segments->all->score,
                'overall_color' => $data->mythic_plus_scores_by_season[1]->segments->all->color,
                'tank' => $data->mythic_plus_scores_by_season[1]->segments->tank->score,
                'tank_color' => $data->mythic_plus_scores_by_season[1]->segments->tank->color,
                'healer' => $data->mythic_plus_scores_by_season[1]->segments->healer->score,
                'healer_color' => $data->mythic_plus_scores_by_season[1]->segments->healer->color,
                'dps' => $data->mythic_plus_scores_by_season[1]->segments->dps->score,
                'dps_color' => $data->mythic_plus_scores_by_season[1]->segments->dps->color,
            ]);

        /*$i = 0;

        foreach($data->mythic_plus_recent_runs as $recentRun)
        {
            $character->mythicPlusRuns()->create([
                [
                    'dungeon' => $recentRun->dungeon,
                    'key_level' => $recentRun->mythic_level,
                    'completed_at' => $recentRun->completed_at,

                ],
                [

                ]
            ]);
        }*/

        $i = 0;
        foreach ($data->mythic_plus_highest_level_runs as $run) {
            if ($i === 5) break;

            $affixImagePath = 'https://wow.zamimg.com/images/wow/icons/large/';

            $highestLevelRun = HighestLevelRun::updateOrCreate(
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'affix_one' => $run->affixes[0]->name ?? '',
                    'affix_two' => $run->affixes[1]->name ?? '',
                    'affix_three' => $run->affixes[2]->name ?? '',
                    'completed_at' => $run->completed_at
                ],
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => $run->affixes[0]->name,
                    'affix_one_icon' => $this->getAffixIconUrl($run->affixes[0]->icon),
                    'affix_two' => $run->affixes[1]->name ?? '',
                    'affix_two_icon' => $this->getAffixIconUrl($run->affixes[1]->icon),
                    'affix_three' => $run->affixes[2]->name ?? '',
                    'affix_three_icon' => $this->getAffixIconUrl($run->affixes[2]->icon),
                    'seasonal_affix' => $run->affixes[3]->name ?? '',
                    'seasonal_affix_icon' => $run->affixes[3]->icon ?? '',
                    'run_id' => preg_match('/\/season-df-3\/([^\/-]+)/', $run->url, $matches) ? $matches[1] : null,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ]);

            $character->highestLevelRuns()->syncWithoutDetaching($highestLevelRun->id);

            $i++;
        }

        if ($search) {
            CharacterSearch::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'character_id' => $character->id
                ],
                [
                    'user_id' => $user->id,
                    'character_id' => $character->id,
                    'searched_at' => Carbon::now()
                ]
            );
        }

        return $character;

    }


}

