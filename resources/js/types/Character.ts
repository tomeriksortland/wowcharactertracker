import {RecentRun} from "@/types/RecentRun";
import {Score} from "@/types/Score";
import {HighestLevelRun} from "@/types/HighestLevelRun";

export interface Character {
    id: number;
    user_id: number;
    name: string;
    race: string;
    class: string;
    spec: string;
    faction: string;
    thumbnail: string;
    realm: string;
    region: string;
    profile_url: string;
    created_at: string;
    updated_at: string;
    score: Score;
    recent_runs: RecentRun[];
    highest_level_runs: HighestLevelRun[];
}
