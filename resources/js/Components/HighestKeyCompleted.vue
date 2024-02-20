<template>
    <div>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-battlenet-light mr-1">
            Highest key completed</h2>
    </div>
    <div class="mt-4">
        <h2 v-if="props.highestLevelRun"
            class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
            {{
                `+${props.highestLevelRun.key_level} ${props.highestLevelRun.dungeon}`
            }}</h2>
    </div>
    <div class="mt-2">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">{{
                `${convertToMinutesAndSeconds(props.highestLevelRun.completion_time)} / ${convertToMinutesAndSeconds(props.highestLevelRun.dungeon_total_time)} ( +${props.highestLevelRun.keystone_upgrades} chest)`
            }}</h3>
    </div>
    <div class="flex flex-row space-x-4 justify-around">
        <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
            <img :src="props.highestLevelRun.affix_one_icon"
                 :alt="props.highestLevelRun.affix_one_icon"
                 class="w-12 h-12 rounded-full"/>
        </div>
        <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
            <img :src="props.highestLevelRun.affix_two_icon"
                 :alt="props.highestLevelRun.affix_two_icon"
                 class="w-12 h-12 rounded-full"/>
        </div>
        <div class="flex h-12 w-12 bg-gray-200 dark:bg-gray-600 rounded-full mt-3">
            <img :src="props.highestLevelRun.affix_three_icon"
                 :alt="props.highestLevelRun.affix_three_icon"
                 class="w-12 h-12 rounded-full"/>
        </div>
    </div>
    <div class="flex flex-row space-x-4 mt-2">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
            {{ props.highestLevelRun.affix_one }}</h3>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
            {{ props.highestLevelRun.affix_two }}</h3>
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">
            {{ props.highestLevelRun.affix_three }}</h3>
    </div>
    <div class="mt-2">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1">{{
                dayjs(props.highestLevelRun.completed_at).format('DD MMMM YYYY - HH:mm:ss')
            }}</h3>
    </div>
</template>


<script setup>
import * as dayjs from "dayjs";

const props = defineProps({
    highestLevelRun: Object,
});

const convertToMinutesAndSeconds = (time) => {
    let minutes = Math.floor(time / 60000)
    let seconds = ((time % 60000) / 1000).toFixed(0)

    return minutes + ':' + (seconds < 10 ? '0' : '') + seconds
}

</script>

