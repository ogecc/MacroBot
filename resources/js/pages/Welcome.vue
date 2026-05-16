<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center justify-center bg-background px-6 text-foreground">
        <div class="w-full max-w-sm space-y-8 text-center">
            <div class="space-y-2">
                <div class="flex items-center justify-center gap-2">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground font-bold text-lg">M</div>
                    <span class="text-2xl font-bold tracking-tight">MacroBot</span>
                </div>
                <p class="text-muted-foreground">
                    {{ $t('welcome.tagline') }}
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    {{ $t('welcome.go_to_dashboard') }}
                </Link>
                <template v-else>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        {{ $t('welcome.get_started') }}
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-input px-5 py-2.5 text-sm font-medium hover:bg-accent"
                    >
                        {{ $t('welcome.log_in') }}
                    </Link>
                </template>
            </div>

            <div class="grid grid-cols-3 gap-4 text-left">
                <div class="rounded-lg border border-border p-4 space-y-1">
                    <div class="text-lg">📸</div>
                    <div class="text-sm font-medium">{{ $t('welcome.feature_1_title') }}</div>
                    <div class="text-xs text-muted-foreground">{{ $t('welcome.feature_1_desc') }}</div>
                </div>
                <div class="rounded-lg border border-border p-4 space-y-1">
                    <div class="text-lg">🎯</div>
                    <div class="text-sm font-medium">{{ $t('welcome.feature_2_title') }}</div>
                    <div class="text-xs text-muted-foreground">{{ $t('welcome.feature_2_desc') }}</div>
                </div>
                <div class="rounded-lg border border-border p-4 space-y-1">
                    <div class="text-lg">📈</div>
                    <div class="text-sm font-medium">{{ $t('welcome.feature_3_title') }}</div>
                    <div class="text-xs text-muted-foreground">{{ $t('welcome.feature_3_desc') }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
