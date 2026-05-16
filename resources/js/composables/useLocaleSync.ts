import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

export function useLocaleSync() {
    const page = usePage();
    const { locale } = useI18n({ useScope: 'global' });

    watch(
        () => page.props.locale as string | undefined,
        (newLocale) => {
            if (newLocale) {
                locale.value = newLocale;
            }
        },
        { immediate: true },
    );
}
