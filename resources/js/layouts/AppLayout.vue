<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import { useLocaleSync } from '@/composables/useLocaleSync';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

useLocaleSync();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
