<script setup>

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {colorVariants} from '@/Components/CharacterColors.js'
import {Head} from '@inertiajs/vue3'
import * as dayjs from 'dayjs'

const {auth, character} = defineProps({
    auth: Object,
    character: Object
})

const convertToMinutesAndSeconds = (time) => {
    let minutes = Math.floor(time / 60000)
    let seconds = ((time % 60000) / 1000).toFixed(0)

    return minutes + ':' + (seconds < 10 ? '0' : '') + seconds
}

const expand = (runId) => {
    let row = document.getElementById(`expandableRow${runId}`)
    if(row.classList.contains('hidden')) row.classList.remove('hidden')
    else row.classList.add('hidden')
}

</script>

<template>
    <Head title="Character search"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Character</h2>
        </template>

        <div class="flex mt-10 bg-gray-100 dark:bg-gray-900">
            <div class="flex w-4/6 mx-auto">
                <div class="w-full">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6">
                        <div class="flex flex-row justify-around">
                            <div class="flex flex-col text-center">
                                <h2 class="text-xl font-semibold mt-2"
                                    :class="colorVariants[character.class]">{{ character.name }}</h2>
                                <p class="text-gray-800 dark:text-gray-100">{{ character.realm }} -
                                    {{ character.region.toUpperCase() }}</p>
                                <div
                                    class="flex h-20 w-20 bg-gray-200 dark:bg-gray-600 rounded-full mt-3 mx-auto">
                                    <img :src="character.thumbnail" :alt="character.name"
                                         class="w-20 h-20 rounded-full"/>
                                </div>
                                <h2 class="text-xl font-semibold mt-2 mb-4"
                                    :class="colorVariants[character.class]">{{ character.spec }} - {{
                                        character.class
                                    }}</h2>
                                <div class="flex flex-row space-x-5 justify-around">
                                    <div class="mb-2">
                                        <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                            Overall:</h2>
                                        <h2 :style="`color: ${character.score.overall_color}`"
                                            class="text-xl font-semibold rounded-full inline-block">
                                            {{ character.score.overall }}</h2>
                                    </div>
                                    <div class="mb-2">
                                        <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                            Tank:</h2>
                                        <h2 :style="`color: ${character.score.tank_color}`"
                                            class="text-xl font-semibold rounded-full inline-block">
                                            {{ character.score.tank }}</h2>
                                    </div>
                                </div>
                                <div class="flex flex-row justify-around">
                                    <div class="mb-2">
                                        <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                            DPS:</h2>
                                        <h2 :style="`color: ${character.score.dps_color}`"
                                            class="text-xl font-semibold rounded-full inline-block">
                                            {{ character.score.dps }}</h2>
                                    </div>
                                    <div class="mb-2">
                                        <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                            Healer:</h2>
                                        <h2 :style="`color: ${character.score.healer_color}`"
                                            class="text-xl font-semibold rounded-full inline-block">
                                            {{ character.score.healer }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col text-center" v-if="character.highest_level_runs[0]">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-battlenet-light mr-1">
                                        Highest key completed</h2>
                                </div>
                                <div class="mt-4">
                                    <h2 v-if="character.highest_level_runs[0]"
                                        class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
                                        {{
                                            `+${character.highest_level_runs[0].key_level} ${character.highest_level_runs[0].dungeon}`
                                        }}</h2>
                                </div>
                                <div class="mt-2">
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">{{
                                            `${convertToMinutesAndSeconds(character.highest_level_runs[0].completion_time)} / ${convertToMinutesAndSeconds(character.highest_level_runs[0].dungeon_total_time)} ( +${character.highest_level_runs[0].keystone_upgrades} chest)`
                                        }}</h3>
                                </div>
                                <div class="flex flex-row space-x-4 justify-around">
                                    <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
                                        <img :src="character.highest_level_runs[0].affix_one_icon"
                                             :alt="character.highest_level_runs[0].affix_one_icon"
                                             class="w-12 h-12 rounded-full"/>
                                    </div>
                                    <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
                                        <img :src="character.highest_level_runs[0].affix_two_icon"
                                             :alt="character.highest_level_runs[0].affix_two_icon"
                                             class="w-12 h-12 rounded-full"/>
                                    </div>
                                    <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
                                        <img :src="character.highest_level_runs[0].affix_three_icon"
                                             :alt="character.highest_level_runs[0].affix_three_icon"
                                             class="w-12 h-12 rounded-full"/>
                                    </div>
                                </div>
                                <div class="flex flex-row space-x-4 mt-2">
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
                                        {{ character.highest_level_runs[0].affix_one }}</h3>
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
                                        {{ character.highest_level_runs[0].affix_two }}</h3>
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
                                        {{ character.highest_level_runs[0].affix_three }}</h3>
                                </div>
                                <div class="mt-2">
                                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">{{
                                            dayjs(character.highest_level_runs[0].completed_at).format('DD MMMM YYYY - HH:mm:ss')
                                        }}</h3>
                                </div>
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
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-battlenet-light mr-1 mb-5">Top 5
                                highest keys completed</h2>
                        </div>
                        <table class="w-full text-md text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <tbody>
                            <template v-for="run in character.highest_level_runs">
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 hover:dark:bg-gray-700 hover:cursor-pointer"
                                    @click="expand(run.id)">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ `+${run.key_level} ${run.dungeon}` }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{
                                            `${convertToMinutesAndSeconds(run.completion_time)} / ${convertToMinutesAndSeconds(run.dungeon_total_time)} ( +${run.keystone_upgrades} chest)`
                                        }}
                                    </td>
                                    <td class="px-6 py-4 inline-flex space-x-3">
                                        <img :src="run.affix_one_icon"
                                             :alt="run.affix_one_icon"
                                             class="w-6 h-6 rounded-full"/>
                                        <img v-if="run.affix_two_icon"
                                             :src="run.affix_two_icon"
                                             :alt="run.affix_two_icon"
                                             class="w-6 h-6 rounded-full"/>
                                        <img v-if="run.affix_three_icon"
                                             :src="run.affix_three_icon"
                                             :alt="run.affix_three_icon"
                                             class="w-6 h-6 rounded-full"/>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a :class="colorVariants[character.class]"
                                           class="border-b border-orange-500 hover:cursor-pointer"
                                           :href="run.url">
                                            RaiderIO run
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ dayjs(run.completed_at).format('DD MMMM YYYY - HH:mm:ss') }}
                                    </td>
                                </tr>
                                <tr class="hidden transition ease-in-out duration-700" :id="`expandableRow${run.id}`">
                                    <td colspan="1" v-for="player in run.characters" :key="player.id">
                                        <div class="flex flex-col text-center">
                                            <h2 class="text-md font-semibold mt-2"
                                                :class="colorVariants[player.class]">{{ player.name }}</h2>
                                            <p class="text-sm text-gray-800 dark:text-gray-100">{{ player.realm }} -
                                                {{ player.region.toUpperCase() }}</p>
                                            <div
                                                class="flex h-14 w-14 bg-gray-200 dark:bg-gray-600 rounded-full mt-3 mx-auto">
                                                <img :src="player.thumbnail" :alt="player.name"
                                                     class="w-14 h-14 rounded-full"/>
                                            </div>
                                            <h2 class="text-md font-semibold mt-2 mb-4"
                                                :class="colorVariants[player.class]">{{ player.spec }} - {{
                                                    player.class
                                                }}</h2>
                                            <div class="flex flex-row space-x-5 justify-center">
                                                <div class="mb-2">
                                                    <h2 class='text-md font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                                        Overall:</h2>
                                                    <h2 :style="`color: ${player.score.overall_color}`"
                                                        class="text-md font-semibold rounded-full inline-block">
                                                        {{ player.score.overall }}</h2>
                                                </div>
                                                <div class="mb-2">
                                                    <h2 class='text-md font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                                        Tank:</h2>
                                                    <h2 :style="`color: ${player.score.tank_color}`"
                                                        class="text-md font-semibold rounded-full inline-block">
                                                        {{ player.score.tank }}</h2>
                                                </div>
                                            </div>
                                            <div class="flex flex-row space-x-5 justify-center">
                                                <div class="mb-2">
                                                    <h2 class='text-md font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                                        DPS:</h2>
                                                    <h2 :style="`color: ${player.score.dps_color}`"
                                                        class="text-md font-semibold rounded-full inline-block">
                                                        {{ player.score.dps }}</h2>
                                                </div>
                                                <div class="mb-2">
                                                    <h2 class='text-md font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                                                        Healer:</h2>
                                                    <h2 :style="`color: ${player.score.healer_color}`"
                                                        class="text-md font-semibold rounded-full inline-block">
                                                        {{ player.score.healer }}</h2>
                                                </div>
                                            </div>
                                            <div class="flex flex-row space-x-5 justify-center">
                                                <div>
                                                    Thumbs Up / Thumbs Down
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
