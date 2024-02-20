<script setup lang="ts">

import {useForm, Head} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CharacterCard from "@/Components/CharacterCard.vue";
import {Auth} from "@/types/Auth";
import {Character} from "@/types/Character";


const props = defineProps<{
    auth: Auth,
    apiErrors: Object,
    lastSixCharacterSearches: Character[]
}>()

const form = useForm({
    region: 'Region',
    realm: '',
    characterName: ''
})

const submit = () => {
    form.post(route('character-search.store'))
}
</script>

<template>
    <Head title="Character search"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Character search</h2>
        </template>
        <div class="flex justify-center mt-10 bg-gray-100 dark:bg-gray-900">
            <div class="w-full sm:max-w-xl px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <form @submit.prevent="submit">
                    <div class="flex">
                        <button id="states-button" data-dropdown-toggle="dropdown-states"
                                class="flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600"
                                type="button">
                            {{ form.region }}
                            <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="currentColor"
                                 viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div id="dropdown-states"
                             class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="states-button">
                                <li>
                                    <button type="button"
                                            @click="() => form.region = 'EU'"
                                    class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                    <div class="inline-flex items-center">
                                        EU
                                    </div>
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                            @click="() => form.region = 'US'"
                                    class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                    <div class="inline-flex items-center">
                                        US
                                    </div>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <input id="realm"
                               name="realm"
                               v-model="form.realm"
                               placeholder="Realm the character is on..."
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg border-l-gray-100 dark:border-l-gray-700 border-l-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
                    </div>

                    <div class="mt-5">
                        <input id="characterName"
                               name="characterName"
                               v-model="form.characterName"
                               placeholder="Search for character name..."
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg border-l-gray-100 dark:border-l-gray-700 border-l-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
                        <template v-if="apiErrors">
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500"><span
                                class="font-medium">{{ apiErrors }}</span></p>
                        </template>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full text-white bg-gradient-to-br mt-4 from-green-400 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-green-200 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2">
                            Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="flex flex-wrap w-3/4 mt-10 mx-auto">
            <template v-for="character in props.lastSixCharacterSearches" :key="character.id">
                <div class="w-1/3 px-2 mb-4">
                    <CharacterCard :character="character" />
                </div>
            </template>
        </div>

    </AuthenticatedLayout>
</template>
