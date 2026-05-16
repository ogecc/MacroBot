<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/MealController';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Meal History', href: '#' },
            { title: 'Edit Meal', href: '#' },
        ],
    },
});

interface MealItem {
    id?: number;
    name: string;
    quantity: number;
    unit: string;
    calories: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
}

interface Meal {
    id: number;
    name: string;
    eaten_at: string;
    items: MealItem[];
}

const props = defineProps<{ meal: Meal }>();

const form = useForm({
    name: props.meal.name,
    eaten_at: props.meal.eaten_at.slice(0, 16),
    items: props.meal.items.map((i) => ({ ...i })),
});

function addItem() {
    form.items.push({ name: '', quantity: 100, unit: 'g', calories: 0, protein_g: 0, carbs_g: 0, fat_g: 0 });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function submit() {
    form.put(update.url({ meal: props.meal.id }));
}
</script>

<template>
    <Head :title="t('meal_edit.title')" />

    <div class="flex flex-col gap-4 p-4">
        <form @submit.prevent="submit" class="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">{{ $t('meal_edit.meal_details') }}</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="meal-name">{{ $t('meal_edit.meal_name') }}</Label>
                        <Input id="meal-name" v-model="form.name" placeholder="Lunch" />
                        <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="eaten-at">{{ $t('meal_edit.time') }}</Label>
                        <Input id="eaten-at" v-model="form.eaten_at" type="datetime-local" />
                        <p v-if="form.errors.eaten_at" class="text-xs text-destructive">{{ form.errors.eaten_at }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-base">{{ $t('meal_edit.items') }}</CardTitle>
                        <Button type="button" variant="outline" size="sm" @click="addItem">{{ $t('meal_edit.add_item') }}</Button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p v-if="form.errors.items" class="text-sm text-destructive">{{ form.errors.items }}</p>

                    <div v-for="(item, i) in form.items" :key="i" class="rounded-lg border p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">{{ $t('meal_edit.item_n', { n: i + 1 }) }}</span>
                            <button type="button" class="text-xs text-destructive" @click="removeItem(i)">{{ $t('meal_edit.remove') }}</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="col-span-2 space-y-1">
                                <Label>{{ $t('meal_edit.name') }}</Label>
                                <Input v-model="item.name" placeholder="Chicken breast" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.quantity') }}</Label>
                                <Input v-model.number="item.quantity" type="number" step="0.1" min="0" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.unit') }}</Label>
                                <Input v-model="item.unit" placeholder="g" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.calories') }}</Label>
                                <Input v-model.number="item.calories" type="number" min="0" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.protein_g') }}</Label>
                                <Input v-model.number="item.protein_g" type="number" step="0.1" min="0" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.carbs_g') }}</Label>
                                <Input v-model.number="item.carbs_g" type="number" step="0.1" min="0" />
                            </div>
                            <div class="space-y-1">
                                <Label>{{ $t('meal_edit.fat_g') }}</Label>
                                <Input v-model.number="item.fat_g" type="number" step="0.1" min="0" />
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? $t('meal_edit.saving') : $t('meal_edit.update_meal') }}
            </Button>
        </form>
    </div>
</template>
