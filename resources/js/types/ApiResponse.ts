import {Character} from "@/types/Character";

export interface ApiResponse {
    jobStatus: string;
    characters: Character[];
}
