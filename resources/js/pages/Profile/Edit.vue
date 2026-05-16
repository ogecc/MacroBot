<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { edit, update } from '@/actions/App/Http/Controllers/FitnessProfileController';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    activity_level: string | null;
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
    activity_level: props.profile.activity_level ?? '',
    goal: props.profile.goal ?? '',
    target_weight_kg: props.profile.target_weight_kg ?? ('' as number | string),
});

const activityMultipliers: Record<string, number> = {
    sedentary: 1.2,
    lightly_active: 1.375,
    moderately_active: 1.55,
    very_active: 1.725,
    extremely_active: 1.9,
};

const goalAdjustments: Record<string, number> = {
    lose: -500,
    maintain: 0,
    gain: 300,
};

const estimatedCalories = computed(() => {
    const { age, gender, height_cm, weight_kg, activity_level, goal } = form;
    if (!age || !gender || !height_cm || !weight_kg || !activity_level || !goal) {
        return null;
    }
    const base = 10 * Number(weight_kg) + 6.25 * Number(height_cm) - 5 * Number(age);
    const bmr = gender === 'male' ? base + 5 : gender === 'female' ? base - 161 : base - 78;
    const multiplier = activityMultipliers[activity_level] ?? 1.2;
    const adjustment = goalAdjustments[goal] ?? 0;
    return Math.max(1200, Math.round(bmr * multiplier + adjustment));
});

function submit() {
    form.put(update.url());
}
</script>

<template>
    <Head :title="t('fitness.title')" />

    <div class="flex flex-col gap-4 p-4">
        <div>
            <h1 class="text-xl font-bold">{{ $t('fitness.title') }}</h1>
            <p class="text-sm text-muted-foreground">{{ $t('fitness.desc') }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Personal Info -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ $t('fitness.personal_info') }}</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="age">{{ $t('fitness.age') }}</Label>
                        <Input id="age" v-model="form.age" type="number" min="13" max="120" placeholder="28" />
                        <p v-if="form.errors.age" class="text-xs text-destructive">{{ form.errors.age }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label>{{ $t('fitness.gender') }}</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger><SelectValue :placeholder="$t('fitness.select')" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">{{ $t('fitness.male') }}</SelectItem>
                                <SelectItem value="female">{{ $t('fitness.female') }}</SelectItem>
                                <SelectItem value="other">{{ $t('fitness.other') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.gender" class="text-xs text-destructive">{{ form.errors.gender }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="height_cm">{{ $t('fitness.height_cm') }}</Label>
                        <Input id="height_cm" v-model="form.height_cm" type="number" min="100" max="250" placeholder="175" />
                        <p v-if="form.errors.height_cm" class="text-xs text-destructive">{{ form.errors.height_cm }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="weight_kg">{{ $t('fitness.weight_kg') }}</Label>
                        <Input id="weight_kg" v-model="form.weight_kg" type="number" step="0.1" min="30" max="300" placeholder="75" />
                        <p v-if="form.errors.weight_kg" class="text-xs text-destructive">{{ form.errors.weight_kg }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Activity & Goal -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ $t('fitness.activity_goal') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-1.5">
                        <Label>{{ $t('fitness.activity_level') }}</Label>
                        <Select v-model="form.activity_level">
                            <SelectTrigger><SelectValue :placeholder="$t('fitness.select')" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="sedentary">{{ $t('fitness.sedentary') }}</SelectItem>
                                <SelectItem value="lightly_active">{{ $t('fitness.lightly_active') }}</SelectItem>
                                <SelectItem value="moderately_active">{{ $t('fitness.moderately_active') }}</SelectItem>
                                <SelectItem value="very_active">{{ $t('fitness.very_active') }}</SelectItem>
                                <SelectItem value="extremely_active">{{ $t('fitness.extremely_active') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.activity_level" class="text-xs text-destructive">{{ form.errors.activity_level }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <Label>{{ $t('fitness.goal') }}</Label>
                        <Select v-model="form.goal">
                            <SelectTrigger><SelectValue :placeholder="$t('fitness.select')" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="lose">{{ $t('fitness.lose_weight') }}</SelectItem>
                                <SelectItem value="maintain">{{ $t('fitness.maintain_weight') }}</SelectItem>
                                <SelectItem value="gain">{{ $t('fitness.gain_weight') }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.goal" class="text-xs text-destructive">{{ form.errors.goal }}</p>
                    </div>

                    <div v-if="form.goal && form.goal !== 'maintain'" class="space-y-1.5">
                        <Label for="target_weight_kg">
                            {{ $t('fitness.target_weight') }}
                            <span class="text-muted-foreground">{{ $t('fitness.optional') }}</span>
                        </Label>
                        <Input
                            id="target_weight_kg"
                            v-model="form.target_weight_kg"
                            type="number"
                            step="0.1"
                            min="30"
                            max="300"
                            placeholder="70"
                        />
                        <p v-if="form.errors.target_weight_kg" class="text-xs text-destructive">{{ form.errors.target_weight_kg }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Live calorie estimate -->
            <div
                v-if="estimatedCalories"
                class="rounded-lg border border-border bg-muted/40 px-4 py-3 text-center"
            >
                <p class="text-sm text-muted-foreground">{{ $t('fitness.new_estimated_goal') }}</p>
                <p class="text-2xl font-bold">
                    {{ estimatedCalories.toLocaleString() }}
                    <span class="text-sm font-normal text-muted-foreground">{{ $t('fitness.kcal_day') }}</span>
                </p>
                <p v-if="profile.daily_calorie_goal && estimatedCalories !== profile.daily_calorie_goal" class="mt-1 text-xs text-muted-foreground">
                    {{ $t('fitness.currently_kcal', { n: profile.daily_calorie_goal.toLocaleString() }) }}
                </p>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? $t('fitness.saving') : $t('fitness.save_changes') }}
            </Button>
        </form>
    </div>
</template>
