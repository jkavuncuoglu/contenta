<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { computed, ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

interface Props {
    modelValue: string;
    timezone?: string;
    error?: string;
    label?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Language',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

interface LanguageOption {
    label: string;
    value: string;
}

// All available languages with nice names
const allLanguages: LanguageOption[] = [
    { label: 'English (US)', value: 'en-US' },
    { label: 'English (UK)', value: 'en-GB' },
    { label: 'English (Australia)', value: 'en-AU' },
    { label: 'Turkish', value: 'tr-TR' },
    { label: 'Spanish (US)', value: 'es-US' },
    { label: 'Spanish (Spain)', value: 'es-ES' },
    { label: 'Japanese', value: 'ja-JP' },
    { label: 'Chinese (Simplified)', value: 'zh-CN' },
    { label: 'Chinese (Traditional)', value: 'zh-TW' },
    { label: 'French', value: 'fr-FR' },
    { label: 'German', value: 'de-DE' },
    { label: 'Italian', value: 'it-IT' },
    { label: 'Portuguese (Brazil)', value: 'pt-BR' },
    { label: 'Portuguese (Portugal)', value: 'pt-PT' },
    { label: 'Russian', value: 'ru-RU' },
    { label: 'Korean', value: 'ko-KR' },
    { label: 'Arabic', value: 'ar-SA' },
    { label: 'Hindi', value: 'hi-IN' },
    { label: 'Dutch', value: 'nl-NL' },
    { label: 'Swedish', value: 'sv-SE' },
    { label: 'Polish', value: 'pl-PL' },
    { label: 'Danish', value: 'da-DK' },
    { label: 'Norwegian', value: 'no-NO' },
    { label: 'Finnish', value: 'fi-FI' },
];

// Simple mapping from timezone to likely language codes
const timezoneLanguageMap: Record<string, string[]> = {
    // Turkey
    'Europe/Istanbul': ['tr-TR', 'en-US'],

    // United Kingdom & Ireland (English)
    'Europe/London': ['en-GB', 'en-US'],
    'Europe/Dublin': ['en-GB', 'en-US'],
    'Europe/Belfast': ['en-GB', 'en-US'],

    // United States (English)
    'America/New_York': ['en-US', 'es-US'],
    'America/Los_Angeles': ['en-US', 'es-US'],
    'America/Chicago': ['en-US', 'es-US'],
    'America/Denver': ['en-US', 'es-US'],
    'America/Phoenix': ['en-US', 'es-US'],
    'America/Detroit': ['en-US', 'es-US'],
    'America/Anchorage': ['en-US'],
    'America/Honolulu': ['en-US'],
    'Pacific/Honolulu': ['en-US'],

    // Canada (English & French)
    'America/Toronto': ['en-US', 'fr-FR'],
    'America/Vancouver': ['en-US', 'fr-FR'],
    'America/Montreal': ['fr-FR', 'en-US'],
    'America/Halifax': ['en-US', 'fr-FR'],
    'America/Winnipeg': ['en-US', 'fr-FR'],
    'America/Edmonton': ['en-US'],

    // Australia (English)
    'Australia/Sydney': ['en-AU', 'en-US'],
    'Australia/Melbourne': ['en-AU', 'en-US'],
    'Australia/Brisbane': ['en-AU', 'en-US'],
    'Australia/Perth': ['en-AU', 'en-US'],
    'Australia/Adelaide': ['en-AU', 'en-US'],
    'Australia/Canberra': ['en-AU', 'en-US'],

    // New Zealand (English)
    'Pacific/Auckland': ['en-AU', 'en-US'],
    'Pacific/Wellington': ['en-AU', 'en-US'],

    // Spain & Spanish-speaking Latin America
    'Europe/Madrid': ['es-ES', 'en-US'],
    'Europe/Barcelona': ['es-ES', 'en-US'],
    'Atlantic/Canary': ['es-ES', 'en-US'],
    'America/Mexico_City': ['es-US', 'en-US'],
    'America/Cancun': ['es-US', 'en-US'],
    'America/Tijuana': ['es-US', 'en-US'],
    'America/Bogota': ['es-US', 'en-US'],
    'America/Lima': ['es-US', 'en-US'],
    'America/Santiago': ['es-US', 'en-US'],
    'America/Buenos_Aires': ['es-US', 'en-US'],
    'America/Caracas': ['es-US', 'en-US'],
    'America/Montevideo': ['es-US', 'en-US'],
    'America/Asuncion': ['es-US', 'en-US'],
    'America/La_Paz': ['es-US', 'en-US'],
    'America/Guatemala': ['es-US', 'en-US'],
    'America/Managua': ['es-US', 'en-US'],
    'America/San_Jose': ['es-US', 'en-US'],
    'America/Panama': ['es-US', 'en-US'],
    'America/Havana': ['es-US', 'en-US'],
    'America/Santo_Domingo': ['es-US', 'en-US'],
    'America/Guayaquil': ['es-US', 'en-US'],

    // France & French-speaking countries
    'Europe/Paris': ['fr-FR', 'en-US'],
    'Europe/Monaco': ['fr-FR', 'en-US'],
    'Europe/Brussels': ['fr-FR', 'en-US'],
    'Europe/Luxembourg': ['fr-FR', 'de-DE', 'en-US'],
    'Europe/Geneva': ['fr-FR', 'de-DE', 'en-US'],
    'Africa/Algiers': ['fr-FR', 'ar-SA', 'en-US'],
    'Africa/Tunis': ['fr-FR', 'ar-SA', 'en-US'],
    'Africa/Casablanca': ['fr-FR', 'ar-SA', 'en-US'],
    'Africa/Dakar': ['fr-FR', 'en-US'],
    'Africa/Abidjan': ['fr-FR', 'en-US'],
    'Indian/Reunion': ['fr-FR', 'en-US'],
    'Indian/Mauritius': ['fr-FR', 'en-US'],
    'America/Cayenne': ['fr-FR', 'en-US'],
    'America/Guadeloupe': ['fr-FR', 'en-US'],
    'America/Martinique': ['fr-FR', 'en-US'],
    'Pacific/Tahiti': ['fr-FR', 'en-US'],
    'Pacific/Noumea': ['fr-FR', 'en-US'],

    // Germany, Austria & German-speaking countries
    'Europe/Berlin': ['de-DE', 'en-US'],
    'Europe/Munich': ['de-DE', 'en-US'],
    'Europe/Hamburg': ['de-DE', 'en-US'],
    'Europe/Vienna': ['de-DE', 'en-US'],
    'Europe/Zurich': ['de-DE', 'fr-FR', 'it-IT', 'en-US'],
    'Europe/Vaduz': ['de-DE', 'en-US'],

    // Other European countries
    'Europe/Rome': ['it-IT', 'en-US'],
    'Europe/Milan': ['it-IT', 'en-US'],
    'Europe/Vatican': ['it-IT', 'en-US'],
    'Europe/Lisbon': ['pt-PT', 'en-US'],
    'Europe/Amsterdam': ['en-US'],
    'Europe/Stockholm': ['sv-SE', 'en-US'],
    'Europe/Warsaw': ['pl-PL', 'en-US'],
    'Europe/Copenhagen': ['da-DK', 'en-US'],
    'Europe/Oslo': ['no-NO', 'en-US'],
    'Europe/Helsinki': ['fi-FI', 'en-US'],
    'Europe/Moscow': ['ru-RU', 'en-US'],
    'Europe/Athens': ['en-US'],
    'Europe/Bucharest': ['en-US'],
    'Europe/Sofia': ['en-US'],
    'Europe/Prague': ['en-US'],
    'Europe/Budapest': ['en-US'],

    // Brazil (Portuguese)
    'America/Sao_Paulo': ['pt-BR', 'en-US'],
    'America/Rio_de_Janeiro': ['pt-BR', 'en-US'],
    'America/Brasilia': ['pt-BR', 'en-US'],
    'America/Fortaleza': ['pt-BR', 'en-US'],
    'America/Manaus': ['pt-BR', 'en-US'],
    'America/Belem': ['pt-BR', 'en-US'],

    // Asia
    'Asia/Tokyo': ['ja-JP', 'en-US'],
    'Asia/Shanghai': ['zh-CN', 'en-US'],
    'Asia/Hong_Kong': ['zh-CN', 'en-US'],
    'Asia/Taipei': ['zh-TW', 'en-US'],
    'Asia/Singapore': ['en-US', 'zh-CN'],
    'Asia/Dubai': ['ar-SA', 'en-US'],
    'Asia/Seoul': ['ko-KR', 'en-US'],
    'Asia/Mumbai': ['hi-IN', 'en-US'],
    'Asia/Kolkata': ['hi-IN', 'en-US'],
    'Asia/Bangkok': ['en-US'],
    'Asia/Jakarta': ['en-US'],
    'Asia/Manila': ['en-US', 'es-US'],

    // Africa (English & French speaking)
    'Africa/Johannesburg': ['en-US', 'en-GB'],
    'Africa/Cairo': ['ar-SA', 'en-US'],
    'Africa/Lagos': ['en-US'],
    'Africa/Nairobi': ['en-US'],
    'Africa/Accra': ['en-US'],
    'Africa/Kampala': ['en-US'],

    // Middle East
    'Asia/Jerusalem': ['en-US'],
    'Asia/Beirut': ['ar-SA', 'fr-FR', 'en-US'],
    'Asia/Riyadh': ['ar-SA', 'en-US'],
    'Asia/Kuwait': ['ar-SA', 'en-US'],
    'Asia/Qatar': ['ar-SA', 'en-US'],
    'Asia/Bahrain': ['ar-SA', 'en-US'],

    // UTC & Other
    UTC: ['en-US'],
    GMT: ['en-GB', 'en-US'],
    'Etc/UTC': ['en-US'],
    'Etc/GMT': ['en-GB', 'en-US'],
};

const languageOptions = ref<LanguageOption[]>([
    { label: 'English (US)', value: 'en-US' },
]);

// Update language options based on timezone
watch(
    () => props.timezone,
    (tz) => {
        if (tz) {
            const langCodes = timezoneLanguageMap[tz] || ['en-US'];
            languageOptions.value = allLanguages.filter((lang) =>
                langCodes.includes(lang.value),
            );
            if (!langCodes.includes(props.modelValue)) {
                emit('update:modelValue', langCodes[0]);
            }
        }
    },
    { immediate: true },
);

const localValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="grid gap-2">
        <Label for="language">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </Label>
        <Multiselect
            v-model="localValue"
            :options="languageOptions"
            placeholder="Select language..."
            :searchable="true"
            :clearable="false"
            label="label"
            track-by="value"
        />
        <InputError :message="error" />
    </div>
</template>

<style lang="postcss">
.multiselect {
    color: var(--foreground);
    background-color: transparent;
    border-radius: 0.375rem;
    border-width: 1px;
    border-color: var(--input);
    min-width: 0;
    width: 100%;
    font-size: 1rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03);
    outline: none;
    transition:
        color 0.2s,
        box-shadow 0.2s;
    display: flex;
    min-height: 36px;
    max-height: 36px;
}

.multiselect__tags {
    background: color-mix(in oklab, var(--input) 30%, transparent);
    color: var(--primary-foreground);
    font-size: var(--text-xs);
    width: 100%;
    line-height: var(--tw-leading, var(--text-sm--line-height));
    border: 0 none;
    padding: 0;
}

.multiselect__single {
    background: transparent;
    color: var(--foreground);
    font-size: var(--text-sm);
    padding: 6px 0 0px 8px;
}

.multiselect__content-wrapper {
    max-height: 15rem;
    overflow-y: auto;
    border-radius: 0.375rem;
    box-shadow:
        0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -2px rgb(0 0 0 / 0.05);
    background-color: var(--background);
    border: 1px solid var(--border);
    z-index: 100;
}

.multiselect__option--highlight {
    background-color: var(--primary);
    color: var(--primary-foreground);
}

.multiselect__option--selected {
    font-weight: 600;
}

.multiselect__option {
    padding: 0.5rem 1rem;
    cursor: pointer;
}
</style>
