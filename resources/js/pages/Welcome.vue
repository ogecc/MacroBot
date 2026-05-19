<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Camera, ChartNoAxesColumn, CircleDot } from 'lucide-vue-next';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    {
        icon: Camera,
        color: 'text-amber-500 bg-amber-500/10',
        titleKey: 'welcome.feature_1_title',
        descKey: 'welcome.feature_1_desc',
    },
    {
        icon: CircleDot,
        color: 'text-blue-500 bg-blue-500/10',
        titleKey: 'welcome.feature_2_title',
        descKey: 'welcome.feature_2_desc',
    },
    {
        icon: ChartNoAxesColumn,
        color: 'text-emerald-500 bg-emerald-500/10',
        titleKey: 'welcome.feature_3_title',
        descKey: 'welcome.feature_3_desc',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-background px-6 py-12 text-foreground">
        <div class="w-full max-w-sm space-y-10 text-center">

            <!-- Brand -->
            <div class="space-y-4">
                <div class="flex items-center justify-center gap-3">
                    <img src="/icon-192.png" alt="MacroBot" class="size-10 object-contain" />
                    <span class="text-3xl font-bold tracking-tight">MacroBot</span>
                </div>
                <p class="text-base text-muted-foreground leading-relaxed">
                    {{ $t('welcome.tagline') }}
                </p>
            </div>

            <!-- CTAs -->
            <div class="flex flex-col gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition-colors"
                >
                    {{ $t('welcome.go_to_dashboard') }}
                </Link>
                <template v-else>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition-colors"
                    >
                        {{ $t('welcome.get_started') }}
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-border px-5 py-3 text-sm font-medium hover:bg-accent transition-colors"
                    >
                        {{ $t('welcome.log_in') }}
                    </Link>
                </template>
            </div>

            <!-- Feature cards -->
            <div class="grid grid-cols-3 gap-3 text-left">
                <div
                    v-for="feature in features"
                    :key="feature.titleKey"
                    class="rounded-2xl border border-border bg-card p-3 space-y-2"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg"
                        :class="feature.color"
                    >
                        <component :is="feature.icon" class="h-4 w-4" />
                    </div>
                    <div class="text-xs font-semibold leading-tight">{{ $t(feature.titleKey) }}</div>
                    <div class="text-xs text-muted-foreground leading-snug">{{ $t(feature.descKey) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
