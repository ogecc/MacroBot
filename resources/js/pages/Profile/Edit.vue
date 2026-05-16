<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { edit, update } from '@/actions/App/Http/Controllers/FitnessProfileController';
import { Card, CardContent } from '@/components/ui/card';
import { CalendarDays, Flame, Minus, Ruler, Scale, TrendingDown, TrendingUp, UserRound } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Fitness Profile', href: edit.url() }],
    },
});

interface Profile {
    age: number | null;
    gender: string | null;
    height_cm: number | null;
    weight_kg: number | null;
    goal: string | null;
    target_weight_kg: number | null;
    daily_calorie_goal: number | null;
}

const props = defineProps<{ profile: Profile }>();

const form = useForm({
    age: props.profile.age ?? ('' as number | string),
    gender: props.profile.gender ?? '',
    height_cm: props.profile.height_cm ?? ('' as number | string),
    weight_kg: props.profile.weight_kg ?? ('' as number | string),
    goal: props.profile.goal ?? '',
    target_weight_kg: props.profile.target_weight_kg ?? ('' as number | string),
});

const targetWeightError = computed<string | null>(() => {
    const current = Number(form.weight_kg);
    const target = Number(form.target_weight_kg);
    if (!form.target_weight_kg || !current || !target) return null;
    if (form.goal === 'lose' && target >= current) return t('fitness.target_weight_lose_error');
    if (form.goal === 'gain' && target <= current) return t('fitness.target_weight_gain_error');
    return null;
});

const goalNoteKey = computed<string | null>(() => {
    if (form.goal === 'lose') return 'fitness.goal_note_lose';
    if (form.goal === 'gain') return 'fitness.goal_note_gain';
    if (form.goal === 'maintain') return 'fitness.goal_note_maintain';
    return null;
});

const estimatedCalories = computed(() => {
    const { age, gender, height_cm, weight_kg, goal } = form;
    if (!age || !gender || !height_cm || !weight_kg || !goal) {
        return null;
    }
    const base = 10 * Number(weight_kg) + 6.25 * Number(height_cm) - 5 * Number(age);
    const bmr = gender === 'male' ? base + 5 : gender === 'female' ? base - 161 : base - 78;
    return Math.max(1200, Math.round(bmr * 1.2));
});

function submit() {
    form.put(update.url());
}
</script>

<template>
    <Head :title="t('fitness.title')" />

    <div class="flex flex-col gap-3 p-4">
        <div>
            <h1 class="text-lg font-bold">{{ $t('fitness.title') }}</h1>
            <p class="text-xs text-muted-foreground">{{ $t('fitness.desc') }}</p>
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-3">
            <Card>
                <CardContent class="pt-4 pb-4 space-y-3">
                    <!-- Personal info -->
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('fitness.personal_info') }}</p>

                    <div class="space-y-2">
                        <!-- Age -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-500/10">
                                <CalendarDays class="h-4 w-4 text-orange-500" />
                            </div>
                            <Label for="age" class="w-20 shrink-0 text-sm">{{ $t('fitness.age') }}</Label>
                            <div class="flex-1">
                                <Input id="age" v-model="form.age" type="number" min="13" max="120" placeholder="28" class="h-9 text-sm" />
                                <p v-if="form.errors.age" class="mt-0.5 text-xs text-destructive">{{ form.errors.age }}</p>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-purple-500/10">
                                <UserRound class="h-4 w-4 text-purple-500" />
                            </div>
                            <Label class="w-20 shrink-0 text-sm">{{ $t('fitness.gender') }}</Label>
                            <div class="flex-1">
                                <Select v-model="form.gender">
                                    <SelectTrigger class="h-9 text-sm"><SelectValue :placeholder="$t('fitness.select')" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="male">{{ $t('fitness.male') }}</SelectItem>
                                        <SelectItem value="female">{{ $t('fitness.female') }}</SelectItem>
                                        <SelectItem value="other">{{ $t('fitness.other') }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.gender" class="mt-0.5 text-xs text-destructive">{{ form.errors.gender }}</p>
                            </div>
                        </div>

                        <!-- Height -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/10">
                                <Ruler class="h-4 w-4 text-blue-500" />
                            </div>
                            <Label for="height_cm" class="w-20 shrink-0 text-sm">{{ $t('fitness.height_cm') }}</Label>
                            <div class="flex-1">
                                <Input id="height_cm" v-model="form.height_cm" type="number" min="100" max="250" placeholder="175" class="h-9 text-sm" />
                                <p v-if="form.errors.height_cm" class="mt-0.5 text-xs text-destructive">{{ form.errors.height_cm }}</p>
                            </div>
                        </div>

                        <!-- Weight -->
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-500/10">
                                <Scale class="h-4 w-4 text-green-500" />
                            </div>
                            <Label for="weight_kg" class="w-20 shrink-0 text-sm">{{ $t('fitness.weight_kg') }}</Label>
                            <div class="flex-1">
                                <Input id="weight_kg" v-model="form.weight_kg" type="number" step="0.1" min="30" max="300" placeholder="75" class="h-9 text-sm" />
                                <p v-if="form.errors.weight_kg" class="mt-0.5 text-xs text-destructive">{{ form.errors.weight_kg }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-border" />

                    <!-- Goal -->
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('fitness.your_goal') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="option in [
                                { value: 'lose', label: $t('fitness.lose_weight'), icon: TrendingDown },
                                { value: 'maintain', label: $t('fitness.maintain_weight'), icon: Minus },
                                { value: 'gain', label: $t('fitness.gain_weight'), icon: TrendingUp },
                            ]"
                            :key="option.value"
                            type="button"
                            @click="form.goal = option.value"
                            class="flex flex-col items-center gap-1 rounded-lg border px-2 py-2.5 text-center transition"
                            :class="form.goal === option.value
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-border bg-muted/30 text-muted-foreground hover:bg-muted/60'"
                        >
                            <component :is="option.icon" class="h-4 w-4" />
                            <span class="text-[11px] font-medium leading-tight">{{ option.label }}</span>
                        </button>
                    </div>
                    <p v-if="form.errors.goal" class="text-xs text-destructive">{{ form.errors.goal }}</p>

                    <!-- Target weight -->
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="form.goal === 'maintain' ? 'bg-muted' : 'bg-green-500/10'">
                            <Scale class="h-4 w-4" :class="form.goal === 'maintain' ? 'text-muted-foreground' : 'text-green-500'" />
                        </div>
                        <Label for="target_weight_kg" class="w-20 shrink-0 text-sm" :class="form.goal === 'maintain' ? 'text-muted-foreground' : ''">
                            {{ $t('fitness.target_weight') }}
                        </Label>
                        <div class="flex-1">
                            <Input
                                id="target_weight_kg"
                                v-model="form.target_weight_kg"
                                type="number"
                                step="0.1"
                                min="30"
                                max="300"
                                placeholder="70"
                                class="h-9 text-sm"
                                :disabled="form.goal === 'maintain'"
                            />
                            <p v-if="targetWeightError" class="mt-0.5 text-xs text-destructive">{{ targetWeightError }}</p>
                            <p v-else-if="form.errors.target_weight_kg" class="mt-0.5 text-xs text-destructive">{{ form.errors.target_weight_kg }}</p>
                        </div>
                    </div>

                    <!-- Live calorie estimate -->
                    <template v-if="estimatedCalories">
                        <div class="border-t border-border" />
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-500/10">
                                <Flame class="h-4 w-4 text-rose-500" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-muted-foreground">{{ $t('fitness.new_estimated_goal') }}</p>
                                <p v-if="goalNoteKey" class="text-xs font-medium text-foreground/70">{{ $t(goalNoteKey) }}</p>
                                <p v-if="profile.daily_calorie_goal && estimatedCalories !== profile.daily_calorie_goal" class="text-xs text-muted-foreground">
                                    {{ $t('fitness.currently_kcal', { n: profile.daily_calorie_goal.toLocaleString() }) }}
                                </p>
                            </div>
                            <p class="text-2xl font-bold tabular-nums">
                                {{ estimatedCalories.toLocaleString() }}
                                <span class="text-sm font-normal text-muted-foreground">kcal</span>
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground pl-11">{{ $t('fitness.tdee_explanation') }}</p>
                    </template>
                </CardContent>
            </Card>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? $t('fitness.saving') : $t('fitness.save_changes') }}
            </Button>
        </form>
    </div>
</template>
