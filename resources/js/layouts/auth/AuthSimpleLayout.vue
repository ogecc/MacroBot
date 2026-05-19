<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-background px-6 py-12">
        <div class="w-full max-w-sm space-y-8">
            <!-- Brand header -->
            <div class="flex flex-col items-center gap-3">
                <Link :href="home()" class="flex items-center gap-2.5">
                    <img src="/icon-192.png" alt="MacroBot" class="size-9 object-contain" />
                    <span class="text-xl font-bold tracking-tight">MacroBot</span>
                </Link>
                <div class="space-y-1 text-center">
                    <h1 class="text-lg font-semibold">{{ title }}</h1>
                    <p class="text-sm text-muted-foreground">{{ description }}</p>
                </div>
            </div>

            <!-- Form slot -->
            <slot />
        </div>
    </div>
</template>
