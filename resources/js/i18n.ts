import { createI18n } from 'vue-i18n';
import en from '@/locales/en.json';
import es from '@/locales/es.json';
import de from '@/locales/de.json';
import fr from '@/locales/fr.json';
import sr from '@/locales/sr.json';

const supported = ['en', 'es', 'de', 'fr', 'sr'] as const;
export type SupportedLocale = (typeof supported)[number];

export const LOCALES: { value: SupportedLocale; label: string; flag: string }[] = [
    { value: 'en', label: 'English', flag: '🇬🇧' },
    { value: 'es', label: 'Español', flag: '🇪🇸' },
    { value: 'de', label: 'Deutsch', flag: '🇩🇪' },
    { value: 'fr', label: 'Français', flag: '🇫🇷' },
    { value: 'sr', label: 'Srpski', flag: '🇷🇸' },
];

const initialLocale: SupportedLocale = 'en';

export const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
    fallbackLocale: 'en',
    messages: { en, es, de, fr, sr },
    globalInjection: true,
});
