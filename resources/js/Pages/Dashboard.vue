<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {onMounted, ref} from "vue";
import CharacterCard from "@/Components/CharacterCard.vue";

const {auth, userCharacters, allCharactersFetched} = defineProps({
    auth: Object,
    userCharacters: Object,
    allCharactersFetched: String
})

let allCharactersFetchedStatus = ref(allCharactersFetched)
let characters = ref(userCharacters)

onMounted(() => {
    const fetchAndSetCharacters = async () => {
        const response = await axios.get(`api/v1/users/${auth.user.id}/characters`)
        if(response.data.jobStatus === "completed") {
            allCharactersFetchedStatus = response.data.jobStatus
            characters = response.data.userCharacters
            return Promise.resolve()
        }

        setTimeout(fetchAndSetCharacters, 2000)
    }

    if(allCharactersFetchedStatus !== "completed") fetchAndSetCharacters()
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">My characters</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap w-full mt-10 mx-auto justify-center">
                    <div v-if="allCharactersFetchedStatus !== 'completed'" class="flex items-center">
                        <h1 class="text-white text-2xl">Loading characters</h1>
                        <span class="loading loading-spinner loading-lg text-info ml-4"></span>
                    </div>
                    <template v-else v-for="character in characters" :key="character.id">
                        <div class="w-1/4 px-2 mb-2">
                            <CharacterCard :character="character" />
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
