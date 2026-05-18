<script setup lang="ts">
import type { AppVariant } from '@/types';

withDefaults(defineProps<{ variant?: AppVariant }>(), {
    variant: 'header',
});
</script>

<template>
    <div class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
</template>
