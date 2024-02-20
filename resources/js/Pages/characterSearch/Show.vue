<script setup lang="ts">

import { ref } from "vue";
import Echo from "laravel-echo";
import { Auth } from "@/types/Auth";
import { Head } from '@inertiajs/vue3'
import { FwbSpinner } from "flowbite-vue";
import RunTable from "@/Components/RunTable.vue";
import CharacterShowCard from "@/Components/CharacterShowCard.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import RecentRunCompleted from "@/Components/RecentRunCompleted.vue";
import HighestKeyCompletedWithinTime from "@/Components/HighestKeyCompletedWithinTime.vue";
import Pusher from "pusher-js";

window.Pusher = Pusher;
const props = defineProps<{
    auth: Auth,
    characterId: Number
}>()

let laravelEcho = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY as string,
    wsHost: import.meta.env.VITE_PUSHER_HOST as string,
    wsPort: import.meta.env.VITE_PUSHER_PORT as string,
    wssPort: import.meta.env.VITE_PUSHER_PORT as string,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER as string,
    forceTLS: false as boolean,
    encrypted: true as boolean,
    disableStats: true as boolean,
    enabledTransports: ['ws', 'wss'] as string[],
    authEndpoint: '/broadcasting/auth',
});

Pusher.logToConsole = true;
laravelEcho.private(`characterUpdate.${props.auth.user.id}`).listen('.UpdateCharacterAndRunsInfoListener', (e: any) => {
    console.log(e)
})

const character = ref(false)

</script>

<template>
    <Head title="Character search"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Character</h2>
        </template>

        <div v-if="!character" class="flex justify-center mt-20">
            <div class="flex mt-10 bg-gray-100 dark:bg-gray-900 space-x-5">
                <h1 class="text-white text-3xl">Loading characters</h1>
                <fwb-spinner size="10" color="red"/>
            </div>
        </div>
        <div v-if="character" class="flex">
            <div class="flex mt-10 bg-gray-100 dark:bg-gray-900">
                <div class="flex w-4/6 mx-auto">
                    <div class="w-full">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                            <div class="flex flex-row justify-around">
                                <div class="flex flex-col text-center">
                                    <CharacterShowCard :character="props.character" />
                                </div>
                                <div class="flex flex-col text-center" v-if="highestKeyInTime">
                                    <HighestKeyCompletedWithinTime :highest-level-run-in-time="highestKeyInTime"/>
                                </div>
                                <div class="flex flex-col text-center" v-if="props.character.recent_runs[0]">
                                    <RecentRunCompleted :recent-run="props.character.recent_runs[0]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex mt-10 bg-gray-100 dark:bg-gray-900">
                <div class="flex w-4/6 mx-auto">
                    <div class="w-full">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                            <div class="text-center">
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-battlenet-light mr-1 mb-5">
                                    Top 5 highest keys completed
                                </h2>
                            </div>
                            <RunTable :runs="props.character.highest_level_runs" :character-class="props.character.class" :score="props.character.score" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex mt-10 bg-gray-100 dark:bg-gray-900">
                <div class="flex w-4/6 mx-auto">
                    <div class="w-full">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                            <div class="text-center">
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-battlenet-light mr-1 mb-5">
                                    5 recent runs completed
                                </h2>
                            </div>
                            <RunTable :runs="props.character.recent_runs" :character-class="props.character.class" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
