<script setup>
import {Link} from "@inertiajs/vue3";
import {colorVariants} from './CharacterColors.js';


const { character } = defineProps({
    character: Object
})

</script>

<template>
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-4 h-80 cursor-pointer">
        <Link :href="route('character-search.show', character.id )">
            <div class="text-center mb-4">
                <h2 class="text-xl font-semibold mt-2"
                    :class="colorVariants[character.class]">
                        {{ character.name }}
                </h2>
                <p class="text-gray-800 dark:text-gray-100">{{ character.realm }} - {{ character.region.toUpperCase() }}</p>
                <div
                    class="flex items-center justify-center h-20 w-20 bg-gray-200 dark:bg-gray-600 rounded-full mx-auto mt-3">
                    <img :src="character.thumbnail" :alt="character.name" class="w-16 h-16 rounded-full"/>
                </div>
                <h2 class="text-xl font-semibold mt-2"
                    :class="colorVariants[character.class]">
                    {{ character.spec }} - {{ character.class }}</h2>
            </div>
            <div class="flex justify-center">
                <div class="pr-2">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2">
                            <h2 class='text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2 mr-1 inline-block'>Overall:</h2>
                            <h2 :style="{'color': character.overall_color}"
                                class="text-xl font-semibold rounded-full inline-block">{{ character.overall }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    </div>
</template>
