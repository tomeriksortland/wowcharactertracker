<?php

namespace App\Services;

use App\Events\ApiErrorLog;
use App\Events\ApiLog;
use App\Models\Character;
use App\Models\CharacterSearch;
use App\Models\HighestLevelRun;
use App\Models\PreviousScore;
use App\Models\RecentRun;
use App\Models\Score;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RaiderIOService
{

    private string $currentSeason = 'season-df-3';
    private string $previousSeason = 'season-df-2';
    private string $runIdRegex = '/\/season-df-3\/([^\/-]+)/';
    private string $imageUrl = 'https://wow.zamimg.com/images/wow/icons/large/';


    public function fetchCharacterData(string $region, string $realm, string $characterName, User|Authenticatable $user): \stdClass
    {

        $response = Http::get('https://raider.io/api/v1/characters/profile', [
            'region' => $region,
            'realm' => $realm,
            'name' => $characterName,
            'fields' => $this->getCharacterQueryParameters()
        ]);

        $this->logRaiderIOResponse(response: $response, user: $user);

        return json_decode($response->body());
    }

    public function fetchRunData(int $runId, User $user): mixed
    {
        $response = Http::get('https://raider.io/api/v1/mythic-plus/run-details', [
            'season' => $this->currentSeason,
            'id' => $runId
        ]);

        $this->logRaiderIOResponse(response: $response, user: $user);

        return json_decode($response->body());
    }

    public function fetchHighestLevelKeysRoster(Character $character, User|Authenticatable $user): void
    {
        foreach ($character->highestLevelRuns as $run) {
            $runData = $this->fetchRunData(runId: $run->run_id, user: $user);

            foreach ($runData->roster as $roster) {
                if (Str::contains($roster->character->name, '-')) {
                    /** @var string $characterName */
                    $characterName = substr($roster->character->name, 0, strpos($roster->character->name, '-'));
                } else {
                    /** @var string $characterName */
                    $characterName = $roster->character->name;
                }

                /** @var Character $alreadyExistingCharacter */
                $alreadyExistingCharacter = Character::where('region', $roster->character->region->slug)->where('realm', $roster->character->realm->name)->where('name', $characterName)->first();

                $raiderIOCharacterData = $this->fetchCharacterData(region: $roster->character->region->slug, realm: $roster->character->realm->name, characterName: $characterName, user: $user);

                /** @var Character $character */
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

                PreviousScore::firstOrCreate(
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

    public function fetchRecentRunsRoster(Character $character, User|Authenticatable $user) : void
    {
        foreach ($character->recentRuns as $run) {

            $runData = $this->fetchRunData(runId: $run->run_id, user: $user);

            foreach ($runData->roster as $roster) {
                if (Str::contains($roster->character->name, '-')) {
                    /** @var string $characterName */
                    $characterName = substr($roster->character->name, 0, strpos($roster->character->name, '-'));
                } else {
                    /** @var string $characterName */
                    $characterName = $roster->character->name;
                }

                /** @var Character $alreadyExistingCharacter */
                $alreadyExistingCharacter = Character::where('region', $roster->character->region->slug)->where('realm', $roster->character->realm->name)->where('name', $characterName)->first();

                $raiderIOCharacterData = $this->fetchCharacterData(region: $roster->character->region->slug, realm: $roster->character->realm->name, characterName: $characterName, user: $user);

                /** @var Character $character */
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

                PreviousScore::firstOrCreate(
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


                $character->recentRuns()->syncWithoutDetaching($run->id);
            }
        }
    }


    public
    function storeOrUpdateCharacterDataWhenLoggingIn(User $user, mixed $data): Character
    {

        /** @var Character $character */
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

        $character->previousScore()->firstOrCreate(
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

        foreach (array_slice(array: $data->mythic_plus_recent_runs, offset: 0, length: 5, preserve_keys: true) as $run) {

            $runId = $this->getRunId(url: $run->url);


            /** @var RecentRun $recentRun */
            $recentRun = RecentRun::firstOrCreate(
                [
                    'run_id' => $runId
                ],
                array(
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => Arr::get($run->affixes, 0)->name,
                    'affix_one_icon' => $this->getAffixIconUrl(icon: $run->affixes[0]->icon),
                    'affix_two' => isset($run->affixes[1]) ? Arr::get($run->affixes, 1)->name : '',
                    'affix_two_icon' => $this->getAffixIconUrl($run->affixes[1]->icon),
                    'affix_three' => isset($run->affixes[2]) ? Arr::get($run->affixes, 2)->name : '',
                    'affix_three_icon' => $this->getAffixIconUrl(icon: $run->affixes[2]->icon ?? ''),
                    'seasonal_affix' => isset($run->affixes[3]) ? $run->affixes[3]->name : '',
                    'seasonal_affix_icon' => $this->getAffixIconUrl(icon: $run->affixes[3]->icon ?? ''),
                    'run_id' => $runId,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ));

            $character->recentRuns()->syncWithoutDetaching($recentRun->id);

        }

        foreach (array_slice(array: $data->mythic_plus_highest_level_runs, offset: 0, length: 5, preserve_keys: true) as $run) {

            $runId = $this->getRunId(url: $run->url);

            /** @var HighestLevelRun $highestLevelRun */
            $highestLevelRun = HighestLevelRun::firstOrCreate(
                [
                    'run_id' => $runId
                ],
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => Arr::get($run->affixes, 0)->name,
                    'affix_one_icon' => $this->getAffixIconUrl(icon: $run->affixes[0]->icon),
                    'affix_two' => isset($run->affixes[1]) ? Arr::get($run->affixes, 1)->name : '',
                    'affix_two_icon' => $this->getAffixIconUrl(icon: $run->affixes[1]->icon),
                    'affix_three' => isset($run->affixes[2]) ? Arr::get($run->affixes, 2)->name : '',
                    'affix_three_icon' => $this->getAffixIconUrl(icon: $run->affixes[2]->icon ?? ''),
                    'seasonal_affix' => isset($run->affixes[3]) ? $run->affixes[3]->name : '',
                    'seasonal_affix_icon' => $this->getAffixIconUrl(icon: $run->affixes[3]->icon ?? ''),
                    'run_id' => $runId,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ]);

            $character->highestLevelRuns()->syncWithoutDetaching($highestLevelRun->id);

        }

        return $character;
    }

    public
    function storeOrUpdateCharacterDataWhenSearching(User|Authenticatable $user, mixed $data): Character
    {
        /** @var Character $alreadyExistingCharacter */
        $alreadyExistingCharacter = Character::where('region', $data->region)->where('realm', $data->realm)->where('name', $data->name)->first();

        /** @var Character $character */
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

        $character->previousScore()->firstOrCreate(
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

        foreach (array_slice(array: $data->mythic_plus_recent_runs, offset: 0, length: 5, preserve_keys: true) as $run) {

            $runId = $this->getRunId(url: $run->url);

            /** @var RecentRun $recentRun */
            $recentRun = RecentRun::firstOrCreate(
                [
                    'run_id' => $runId
                ],
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => Arr::get($run->affixes, 0)->name,
                    'affix_one_icon' => $this->getAffixIconUrl(icon: $run->affixes[0]->icon),
                    'affix_two' => isset($run->affixes[1]) ? Arr::get($run->affixes, 1)->name : '',
                    'affix_two_icon' => $this->getAffixIconUrl(icon: $run->affixes[1]->icon),
                    'affix_three' => isset($run->affixes[2]) ? Arr::get($run->affixes, 2)->name : '',
                    'affix_three_icon' => $this->getAffixIconUrl(icon: $run->affixes[2]->icon ?? ''),
                    'seasonal_affix' => isset($run->affixes[3]) ? $run->affixes[3]->name : '',
                    'seasonal_affix_icon' => $this->getAffixIconUrl(icon: $run->affixes[3]->icon ?? ''),
                    'run_id' => $runId,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ]);

            $character->recentRuns()->syncWithoutDetaching($recentRun->id);

        }

        foreach (array_slice(array: $data->mythic_plus_highest_level_runs, offset: 0, length: 5, preserve_keys: true) as $run) {

            $runId = $this->getRunId(url: $run->url);

            /** @var HighestLevelRun $highestLevelRun */
            $highestLevelRun = HighestLevelRun::firstOrCreate(
                [
                    'run_id' => $runId
                ],
                [
                    'dungeon' => $run->dungeon,
                    'key_level' => $run->mythic_level,
                    'completion_time' => $run->clear_time_ms,
                    'dungeon_total_time' => $run->par_time_ms,
                    'keystone_upgrades' => $run->num_keystone_upgrades,
                    'affix_one' => $run->affixes[0]->name,
                    'affix_one_icon' => $this->getAffixIconUrl(icon: $run->affixes[0]->icon),
                    'affix_two' => $run->affixes[1]->name ?? '',
                    'affix_two_icon' => $this->getAffixIconUrl(icon: $run->affixes[1]->icon),
                    'affix_three' => $run->affixes[2]->name ?? '',
                    'affix_three_icon' => $this->getAffixIconUrl(icon: $run->affixes[2]->icon),
                    'seasonal_affix' => $run->affixes[3]->name ?? '',
                    'seasonal_affix_icon' => $this->getAffixIconUrl(icon: $run->affixs[3] ?? ''),
                    'run_id' => $runId,
                    'run_url' => $run->url,
                    'completed_at' => Carbon::parse($run->completed_at)
                ]);

            $character->highestLevelRuns()->syncWithoutDetaching($highestLevelRun->id);

        }

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


        return $character;

    }

    private
    function getRunId(string $url): string|null
    {
        return preg_match(pattern: $this->runIdRegex, subject: $url, matches: $matches) ? $matches[1] : null;
    }

    private
    function logRaiderIOResponse(Response $response, User $user): void
    {
        if ($response->status() === 200) {
            ApiLog::dispatch($response, $user);
        } else {
            ApiErrorLog::dispatch($response, $user);
        }
    }

    private
    function getCharacterQueryParameters(): string
    {
        return "mythic_plus_scores_by_season:{$this->currentSeason}:{$this->previousSeason},mythic_plus_recent_runs,mythic_plus_highest_level_runs";
    }

    private
    function getAffixIconUrl(string $icon = ''): string
    {
        if (empty($icon)) return '';

        return $this->imageUrl . $icon . '.jpg';
    }


}

