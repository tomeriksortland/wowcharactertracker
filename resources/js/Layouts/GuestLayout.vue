<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div
            class="w-full rounded-full sm:max-w-md mt-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden"
        >
            <slot />
        </div>
    </div>
</template>
