<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';

defineOptions({
    layout: {
        title: 'Verify email',
        description:
            'Please verify your email address by clicking on the link we just emailed to you.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head :title="$t('auth.verify_email_title')" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ $t('auth.verification_link_sent') }}
    </div>

    <Form
        action="/email/verification-notification"
        method="post"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" class="bg-amber-500 hover:bg-amber-600 text-white">
            <Spinner v-if="processing" />
            {{ $t('auth.resend_verification') }}
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            {{ $t('nav.log_out') }}
        </TextLink>
    </Form>
</template>
