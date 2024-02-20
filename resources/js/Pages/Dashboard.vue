<script setup lang="ts">
import {Auth} from "@/types/Auth"
import {onMounted, ref} from "vue"
import { Head } from '@inertiajs/vue3'
import { FwbSpinner } from 'flowbite-vue'
import {Character} from "@/types/Character"
import axios, { AxiosResponse } from "axios"
import {ApiResponse} from "@/types/ApiResponse"
import CharacterCard from '@/Components/CharacterCard.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps<{
    charactersUpdateJob: string,
    auth: Auth
}>();

enum getCharactersJobStatus {
    Completed = 'completed',
    Pending = 'pending',
}

let getCharactersJob = ref<getCharactersJobStatus>(props.charactersUpdateJob as getCharactersJobStatus)
let characters = ref<Character[]>([])

onMounted(() => {
    const fetchAndSetCharacters = async () : Promise<void> => {
            const response: AxiosResponse<ApiResponse> = await axios.get(`api/v1/users/${props.auth.user.id}/characters`)
            if(response.data.jobStatus === getCharactersJobStatus.Completed) {
                getCharactersJob.value = response.data.jobStatus
                characters.value = response.data.characters
                return Promise.resolve()
            }
            await new Promise(resolve => setTimeout(resolve, 2000))
            await fetchAndSetCharacters()
    }
    fetchAndSetCharacters()
    if(getCharactersJob.value !== getCharactersJobStatus.Completed) {
        fetchAndSetCharacters()
    }
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
                    <div v-if="getCharactersJob !== getCharactersJobStatus.Completed || !characters.length" class="flex items-center space-x-5">
                        <h1 class="text-white text-2xl">Loading characters</h1>
                        <fwb-spinner size="8" color="red"/>
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
