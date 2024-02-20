<script setup>
import {colorVariants} from "@/Components/CharacterColors";
import * as dayjs from "dayjs";
import {ref} from "vue";

const props = defineProps({
    runs: Array,
    characterClass: String
})

const convertToMinutesAndSeconds = (time) => {
    let minutes = Math.floor(time / 60000)
    let seconds = ((time % 60000) / 1000).toFixed(0)

    return minutes + ':' + (seconds < 10 ? '0' : '') + seconds
}

const expandedRows = ref([])

const rowExpanded = (runId) => {
    return expandedRows.value.includes(runId)
}

const toggleExpandedRow = (runId) => {
    if (expandedRows.value.indexOf(runId) === -1) {
        expandedRows.value.push(runId)
    } else {
        expandedRows.value.splice(expandedRows.value.indexOf(runId), 1)
    }
}

</script>

<template>
    <table class="w-full text-md text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <tbody>
        <template v-for="(run, index) in props.runs">
            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 hover:dark:bg-gray-700 hover:cursor-pointer"
                @click="toggleExpandedRow(index)">
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
                    <a :class="colorVariants[props.characterClass]"
                       class="border-b border-orange-500 hover:cursor-pointer"
                       :href="run.url">
                        RaiderIO run
                    </a>
                </td>
                <td class="px-6 py-4">
                    {{ dayjs(run.completed_at).format('DD MMMM YYYY - HH:mm:ss') }}
                </td>
            </tr>
            <tr class="transition ease-in-out duration-700" :class="{'hidden': ! rowExpanded(index)}">
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
                                    DPS:
                                </h2>
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
                            <div class="space-x-5">
                                <font-awesome-icon :icon="['fas', 'thumbs-up']" size="2xl" class="hover:text-green-600 hover:cursor-pointer" />
                                <font-awesome-icon :icon="['fas', 'thumbs-down']" size="2xl" class="hover:text-red-600 hover:cursor-pointer" />
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </template>
        </tbody>
    </table>
</template>
