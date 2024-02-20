<script setup lang="ts">
import {Character} from "@/types/Character";
import {colorVariants} from "@/types/ColorVaiants";


const props = defineProps<{
    character: Character
}>()

</script>

<template>
    <h2 class="text-xl font-semibold mt-2"
        :class="colorVariants[props.character.class]">{{ props.character.name }}</h2>
    <p class="text-gray-800 dark:text-gray-100">{{ props.character.realm }} -
        {{ props.character.region.toUpperCase() }}</p>
    <div
        class="flex h-20 w-20 bg-gray-200 dark:bg-gray-600 rounded-full mt-3 mx-auto">
        <img :src="props.character.thumbnail" :alt="props.character.name"
             class="w-20 h-20 rounded-full"/>
    </div>
    <h2 class="text-xl font-semibold mt-2 mb-4"
        :class="colorVariants[props.character.class]">{{ props.character.spec }} - {{
            props.character.class
        }}</h2>
    <div class="flex flex-row space-x-5 justify-around">
        <div class="mb-2">
            <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                Overall:</h2>
            <h2 :style="`color: ${props.character.score.overall_color}`"
                class="text-xl font-semibold rounded-full inline-block">
                {{ props.character.score.overall }}</h2>
        </div>
        <div class="mb-2">
            <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                Tank:</h2>
            <h2 :style="`color: ${props.character.score.tank_color}`"
                class="text-xl font-semibold rounded-full inline-block">
                {{ props.character.score.tank }}</h2>
        </div>
    </div>
    <div class="flex flex-row justify-around">
        <div class="mb-2">
            <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                DPS:</h2>
            <h2 :style="`color: ${props.character.score.dps_color}`"
                class="text-xl font-semibold rounded-full inline-block">
                {{ props.character.score.dps }}</h2>
        </div>
        <div class="mb-2">
            <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>
                Healer:</h2>
            <h2 :style="`color: ${props.character.score.healer_color}`"
                class="text-xl font-semibold rounded-full inline-block">
                {{ props.character.score.healer }}</h2>
        </div>
    </div>
</template>
