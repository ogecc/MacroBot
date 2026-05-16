<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { LOCALES } from '@/i18n';
import { update } from '@/actions/App/Http/Controllers/Settings/LocaleController';
import { edit } from '@/routes/appearance';

const { t } = useI18n();
const page = usePage();

const currentLocale = computed(() => (page.props.locale as string) ?? 'en');

function changeLocale(locale: string) {
    router.patch(update.url(), { locale }, { preserveScroll: true });
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="$t('settings.appearance_title')" />

    <h1 class="sr-only">{{ $t('settings.appearance_title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="$t('settings.appearance_title')"
            :description="$t('settings.appearance_desc')"
        />
        <AppearanceTabs />

        <div class="space-y-4">
            <Heading
                variant="small"
                :title="$t('settings.language_title')"
                :description="$t('settings.language_desc')"
            />
            <Select :model-value="currentLocale" @update:model-value="changeLocale">
                <SelectTrigger class="w-48">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="locale in LOCALES" :key="locale.value" :value="locale.value">
                        {{ locale.flag }} {{ locale.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>
