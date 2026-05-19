<script setup lang="ts">
import { Form, Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import { update as localeUpdate } from '@/actions/App/Http/Controllers/Settings/LocaleController';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { LOCALES } from '@/i18n';
import { send } from '@/routes/verification';

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);
const currentLocale = computed(() => (page.props.locale as string) ?? 'en');

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
    passwordRules: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Settings', href: '/settings' }],
    },
});

function changeLocale(locale: string) {
    router.patch(localeUpdate.url(), { locale }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="$t('settings.profile_tab')" />

    <h1 class="sr-only">{{ $t('settings.profile_tab') }}</h1>

    <div class="flex flex-col gap-10 p-4 pb-10 max-w-lg">

        <!-- Page title -->
        <div>
            <h2 class="text-xl font-semibold">{{ $t('settings.title') }}</h2>
            <p class="text-sm text-muted-foreground mt-1">{{ $t('settings.manage_desc') }}</p>
        </div>

        <!-- Profile -->
        <section class="space-y-6">
            <Heading
                variant="small"
                :title="$t('settings.profile_info_title')"
                :description="$t('settings.profile_info_desc')"
            />
            <Form
                v-bind="ProfileController.update.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="name">{{ $t('settings.name') }}</Label>
                    <Input
                        id="name"
                        class="mt-1 block w-full"
                        name="name"
                        :default-value="user.name"
                        required
                        autocomplete="name"
                        :placeholder="$t('settings.full_name_placeholder')"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label>{{ $t('settings.email_address') }}</Label>
                    <p class="mt-1 rounded-md border border-input bg-muted/40 px-3 py-2 text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        {{ $t('settings.email_unverified') }}
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            {{ $t('settings.resend_verification') }}
                        </Link>
                    </p>
                    <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                        {{ $t('settings.verification_sent') }}
                    </div>
                </div>

                <Button class="bg-amber-500 hover:bg-amber-600 text-white" :disabled="processing" data-test="update-profile-button">{{ $t('settings.save') }}</Button>
            </Form>
        </section>

        <hr class="border-border" />

        <!-- Password -->
        <section class="space-y-6">
            <Heading
                variant="small"
                :title="$t('settings.update_password_title')"
                :description="$t('settings.update_password_desc')"
            />
            <Form
                v-bind="SecurityController.update.form()"
                :options="{ preserveScroll: true }"
                reset-on-success
                :reset-on-error="['password', 'password_confirmation', 'current_password']"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="current_password">{{ $t('settings.current_password') }}</Label>
                    <PasswordInput
                        id="current_password"
                        name="current_password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        :placeholder="$t('settings.current_password')"
                    />
                    <InputError :message="errors.current_password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">{{ $t('settings.new_password') }}</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        :placeholder="$t('settings.new_password')"
                        :passwordrules="passwordRules"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ $t('settings.confirm_password') }}</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        :placeholder="$t('settings.confirm_password')"
                        :passwordrules="passwordRules"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
                <Button class="bg-amber-500 hover:bg-amber-600 text-white" :disabled="processing" data-test="update-password-button">
                    {{ $t('settings.save_password') }}
                </Button>
            </Form>
        </section>

        <hr class="border-border" />

        <!-- Appearance -->
        <section class="space-y-6">
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
        </section>

        <hr class="border-border" />

        <!-- Danger zone -->
        <section>
            <DeleteUser />
        </section>

    </div>
</template>
