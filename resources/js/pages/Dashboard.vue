<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { dashboard } from '@/routes';
import { store as mealStore, destroy as mealDestroy } from '@/actions/App/Http/Controllers/MealController';
import { store as analyzeRoute } from '@/actions/App/Http/Controllers/MealAnalysisController';
import { updateWeight } from '@/actions/App/Http/Controllers/FitnessProfileController';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import DialogScrollContent from '@/components/ui/dialog/DialogScrollContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import { ArrowUp, Camera, Plus, Minus, UtensilsCrossed, ChevronDown, ChevronRight, Loader2, Trash2 } from 'lucide-vue-next';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

interface MealItem {
    id: number;
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

interface DayGroup {
    date: string;
    total_calories: number;
    meals: Meal[];
}

interface Totals {
    calories: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
}

interface AnalyzedItem {
    name: string;
    quantity: number;
    unit: string;
    calories: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
}

const props = defineProps<{
    dailyCalorieGoal: number;
    todayTotals: Totals;
    recentMealsByDay: DayGroup[];
    currentWeight: number;
    targetWeight: number | null;
}>();

// ── Today date label ─────────────────────────────────────────────────────────
const todayLabel = ref('');
onMounted(() => {
    todayLabel.value = new Date().toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' });
});

// ── Calorie ring ──────────────────────────────────────────────────────────────
const caloriePercent = computed(() =>
    props.dailyCalorieGoal > 0
        ? Math.min(100, Math.round((props.todayTotals.calories / props.dailyCalorieGoal) * 100))
        : 0,
);
const caloriesOver = computed(() => props.todayTotals.calories > props.dailyCalorieGoal);
const ringColor = computed(() => (caloriesOver.value ? '#ef4444' : '#22c55e'));
const ringStyle = computed(() => ({
    background: `conic-gradient(${ringColor.value} ${caloriePercent.value}%, #e5e7eb ${caloriePercent.value}% 100%)`,
}));
// ── Weight ring ───────────────────────────────────────────────────────────────
const weightAtGoal = computed(() => props.targetWeight !== null && Math.abs(props.currentWeight - props.targetWeight) < 0.1);
const weightPercent = computed(() => {
    if (!props.targetWeight) return 0;
    if (weightAtGoal.value) return 100;
    const cur = props.currentWeight;
    const tgt = props.targetWeight;
    // Gaining: fill as cur approaches tgt from below
    // Losing:  fill as cur approaches tgt from above (tgt/cur gives increasing %)
    const ratio = cur < tgt ? cur / tgt : tgt / cur;
    return Math.min(100, Math.round(ratio * 100));
});
const weightRingColor = computed(() => weightAtGoal.value ? '#22c55e' : 'hsl(var(--primary))');
const weightRingStyle = computed(() => ({
    background: props.targetWeight
        ? `conic-gradient(${weightRingColor.value} ${weightPercent.value}%, #e5e7eb ${weightPercent.value}% 100%)`
        : 'conic-gradient(#e5e7eb 100%, transparent 0%)',
}));

const macros = computed(() => [
    { label: t('dashboard.protein'), value: props.todayTotals.protein_g, color: 'bg-blue-500', max: 200 },
    { label: t('dashboard.carbs'), value: props.todayTotals.carbs_g, color: 'bg-amber-400', max: 300 },
    { label: t('dashboard.fat'), value: props.todayTotals.fat_g, color: 'bg-rose-400', max: 100 },
]);

// ── Weight stat with +/- 0.5 ─────────────────────────────────────────────────
const weightSaving = ref(false);

async function adjustWeight(delta: number) {
    const next = Math.round((props.currentWeight + delta) * 10) / 10;
    if (next < 1 || next > 999) return;
    weightSaving.value = true;
    try {
        await fetch(updateWeight.url(), {
            method: 'PATCH',
            headers: { 'X-XSRF-TOKEN': getCsrf(), 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ weight_kg: next }),
        });
        router.reload({ only: ['currentWeight', 'dailyCalorieGoal'] });
    } finally {
        weightSaving.value = false;
    }
}

// ── AI Meal chat input ────────────────────────────────────────────────────────
const reviewOpen = ref(false);
const mealDescription = ref('');
const mealImageFile = ref<File | null>(null);
const analyzing = ref(false);
const analyzeError = ref('');
const fileInputRef = ref<HTMLInputElement | null>(null);

const mealForm = useForm({
    name: '',
    eaten_at: '',
    items: [] as AnalyzedItem[],
});

function onImageChange(e: Event) {
    const input = e.target as HTMLInputElement;
    mealImageFile.value = input.files?.[0] ?? null;
}

async function analyzeMeal() {
    if (!mealDescription.value && !mealImageFile.value) {
        analyzeError.value = t('dashboard.type_or_attach_error');
        return;
    }
    analyzeError.value = '';
    analyzing.value = true;

    const data = new FormData();
    if (mealDescription.value) data.append('description', mealDescription.value);
    if (mealImageFile.value) data.append('image', mealImageFile.value);

    try {
        const res = await fetch(analyzeRoute.url(), {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': getCsrf(), Accept: 'application/json' },
            body: data,
        });
        if (!res.ok) {
            const err = await res.json();
            analyzeError.value = err.message ?? t('dashboard.analysis_failed');
            return;
        }
        const json = await res.json();
        mealForm.items = json.items;
        mealForm.name = json.meal_name_suggestion ?? mealForm.name;
        reviewOpen.value = true;
    } catch {
        analyzeError.value = t('dashboard.network_error');
    } finally {
        analyzing.value = false;
    }
}

function getCsrf(): string {
    return document.cookie
        .split('; ')
        .find((r) => r.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ?.replace(/%3D/g, '=') ?? '';
}

function addMealItem() {
    mealForm.items.push({ name: '', quantity: 100, unit: 'g', calories: 0, protein_g: 0, carbs_g: 0, fat_g: 0 });
}

function removeMealItem(i: number) {
    mealForm.items.splice(i, 1);
}

function submitMeal() {
    mealForm.eaten_at = new Date().toISOString().slice(0, 16);
    mealForm.post(mealStore.url(), {
        onSuccess: () => {
            reviewOpen.value = false;
            mealDescription.value = '';
            mealImageFile.value = null;
            mealForm.reset();
        },
    });
}

// ── Meal review expanded items ────────────────────────────────────────────────
const expandedItems = ref<Set<number>>(new Set());

function toggleItem(i: number) {
    const s = new Set(expandedItems.value);
    s.has(i) ? s.delete(i) : s.add(i);
    expandedItems.value = s;
}

function removeMealItemAndCollapse(i: number) {
    removeMealItem(i);
    const s = new Set(expandedItems.value);
    s.delete(i);
    expandedItems.value = s;
}

// ── Meal history modal ────────────────────────────────────────────────────────
const historyOpen = ref(false);
const expandedDay = ref<string | null>(null);
const expandedMealId = ref<number | null>(null);
const deletingMealId = ref<number | null>(null);

function toggleDay(date: string) {
    expandedDay.value = expandedDay.value === date ? null : date;
    expandedMealId.value = null;
}

function toggleMeal(mealId: number) {
    expandedMealId.value = expandedMealId.value === mealId ? null : mealId;
}

function formatDate(dateStr: string) {
    const d = new Date(dateStr + 'T00:00:00');
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    if (d.toDateString() === today.toDateString()) return t('dashboard.today_label');
    if (d.toDateString() === yesterday.toDateString()) return t('dashboard.yesterday');
    return d.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
}

function isToday(dateStr: string): boolean {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toDateString() === new Date().toDateString();
}

async function deleteMeal(mealId: number) {
    deletingMealId.value = mealId;
    try {
        await fetch(mealDestroy.url(mealId), {
            method: 'DELETE',
            headers: { 'X-XSRF-TOKEN': getCsrf(), Accept: 'application/json' },
        });
        router.reload({ only: ['todayTotals', 'recentMealsByDay'] });
    } finally {
        deletingMealId.value = null;
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-4 p-4">

        <!-- ── AI Meal Logger hero ── -->
        <div class="relative overflow-hidden rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent p-5 shadow-sm">
            <!-- Decorative glow blob -->
            <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-primary/10 blur-2xl" />

            <div class="relative space-y-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/20">
                        <UtensilsCrossed class="h-3.5 w-3.5 text-primary" />
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-primary/80">{{ $t('dashboard.ai_tracker_label') }}</span>
                </div>

                <div>
                    <h2 class="text-xl font-bold leading-tight">{{ $t('dashboard.what_did_you_eat') }}</h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">{{ $t('dashboard.ai_description') }}</p>
                </div>

                <!-- Input box -->
                <div class="rounded-xl border border-border/60 bg-background/80 shadow-inner backdrop-blur-sm transition focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-primary/20">
                    <!-- Textarea -->
                    <textarea
                        v-model="mealDescription"
                        rows="2"
                        :placeholder="$t('dashboard.describe_placeholder')"
                        class="w-full resize-none bg-transparent px-4 pt-3 pb-2 text-sm leading-relaxed placeholder:text-muted-foreground/60 focus:outline-none"
                        style="field-sizing: content; max-height: 140px;"
                        @keydown.enter.exact.prevent="analyzeMeal"
                    />

                    <!-- Bottom bar: photo chip + send -->
                    <div class="flex items-center justify-between gap-2 border-t border-border/40 px-3 py-2">
                        <!-- Camera / attachment -->
                        <label class="flex cursor-pointer items-center gap-1.5 rounded-full border border-border/60 bg-muted/40 px-3 py-1 text-xs text-muted-foreground transition hover:border-primary/40 hover:text-primary">
                            <Camera class="h-3.5 w-3.5" />
                            <span>{{ mealImageFile ? mealImageFile.name : $t('dashboard.add_photo') }}</span>
                            <button v-if="mealImageFile" type="button" class="ml-0.5 text-destructive" @click.prevent="mealImageFile = null">✕</button>
                            <input ref="fileInputRef" type="file" accept="image/*" class="sr-only" @change="onImageChange" />
                        </label>

                        <button
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground shadow transition hover:opacity-90 active:scale-95 disabled:opacity-50"
                            :disabled="analyzing"
                            @click="analyzeMeal"
                        >
                            <Loader2 v-if="analyzing" class="h-4 w-4 animate-spin" />
                            <ArrowUp v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <p v-if="analyzeError" class="mt-1.5 px-1 text-xs text-destructive">{{ analyzeError }}</p>
            </div>
        </div>

        <!-- ── Today header ── -->
        <div class="flex items-center justify-between px-1">
            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">{{ $t('dashboard.today') }}</p>
            <p v-if="todayLabel" class="text-xs text-muted-foreground">{{ todayLabel }}</p>
        </div>

        <!-- ── Two rings + macros ── -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Calorie ring -->
            <Card>
                <CardContent class="flex flex-col items-center pt-4 pb-4 px-3">
                    <div class="relative">
                        <div class="h-28 w-28 rounded-full" :style="ringStyle" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="h-20 w-20 rounded-full bg-background" />
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-base font-bold leading-none">{{ todayTotals.calories }}</span>
                            <span class="text-[10px] text-muted-foreground">kcal</span>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-muted-foreground">{{ $t('dashboard.goal_kcal', { n: dailyCalorieGoal }) }}</p>
                    <p class="text-[10px] font-medium mt-0.5" :class="caloriesOver ? 'text-red-500' : 'text-muted-foreground'">
                        {{ caloriesOver
                            ? $t('dashboard.kcal_over', { n: Math.abs(dailyCalorieGoal - todayTotals.calories) })
                            : $t('dashboard.kcal_left', { n: Math.abs(dailyCalorieGoal - todayTotals.calories) }) }}
                    </p>
                    <!-- Macro mini bars -->
                    <div class="mt-3 w-full space-y-1.5">
                        <div v-for="macro in macros" :key="macro.label" class="flex items-center gap-1.5">
                            <span class="w-8 text-[10px] text-muted-foreground">{{ macro.label.slice(0, 4) }}</span>
                            <div class="h-1 flex-1 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full" :class="macro.color" :style="{ width: `${Math.min(100, (macro.value / macro.max) * 100)}%` }" />
                            </div>
                            <span class="w-7 text-right text-[10px] font-medium">{{ macro.value }}g</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Weight ring -->
            <Card>
                <CardContent class="flex flex-col items-center pt-4 pb-4 px-3">
                    <div class="relative">
                        <div class="h-28 w-28 rounded-full" :style="weightRingStyle" />
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="h-20 w-20 rounded-full bg-background" />
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-base font-bold leading-none">{{ currentWeight.toFixed(1) }}</span>
                            <span class="text-[10px] text-muted-foreground">kg</span>
                        </div>
                    </div>
                    <p class="mt-2 text-[10px] text-muted-foreground">
                        {{ targetWeight ? $t('dashboard.goal_kg', { n: targetWeight }) : $t('dashboard.no_target_set') }}
                    </p>
                    <p v-if="targetWeight" class="text-[10px] font-medium mt-0.5" :class="weightAtGoal ? 'text-green-500' : 'text-muted-foreground'">
                        {{ weightAtGoal ? $t('dashboard.goal_reached') : $t('dashboard.kg_to_go', { n: Math.abs(currentWeight - targetWeight).toFixed(1) }) }}
                    </p>
                    <p v-else class="text-[10px] text-muted-foreground mt-0.5">{{ $t('dashboard.set_in_fitness_profile') }}</p>
                    <!-- +/- buttons -->
                    <div class="mt-3 flex items-center gap-2">
                        <button
                            class="flex h-7 w-7 items-center justify-center rounded-full border border-border bg-background text-foreground transition hover:bg-muted disabled:opacity-40"
                            :disabled="weightSaving"
                            @click="adjustWeight(-0.5)"
                        >
                            <Minus class="h-3 w-3" />
                        </button>
                        <span class="text-[10px] text-muted-foreground select-none">0.5 kg</span>
                        <button
                            class="flex h-7 w-7 items-center justify-center rounded-full border border-border bg-background text-foreground transition hover:bg-muted disabled:opacity-40"
                            :disabled="weightSaving"
                            @click="adjustWeight(0.5)"
                        >
                            <Plus class="h-3 w-3" />
                        </button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- ── Empty state: no meals today ── -->
        <div
            v-if="todayTotals.calories === 0"
            class="rounded-xl border border-dashed border-border/50 px-4 py-3 text-center text-sm text-muted-foreground"
        >
            {{ $t('dashboard.no_meals_today') }}
        </div>

        <!-- ── Meal history trigger ── -->
        <div
            v-if="recentMealsByDay.length > 0"
            class="flex cursor-pointer items-center justify-between rounded-xl border border-border/40 bg-muted/20 px-4 py-3 transition active:bg-muted/40"
            @click="historyOpen = true"
        >
            <div>
                <p class="text-sm font-medium">{{ $t('dashboard.meal_history') }}</p>
                <p class="text-xs text-muted-foreground">
                    {{ $t('dashboard.days_logged', recentMealsByDay.length) }}
                </p>
            </div>
            <ChevronRight class="h-4 w-4 text-muted-foreground" />
        </div>
        <p v-else class="px-1 text-xs text-muted-foreground">{{ $t('dashboard.no_meals_this_week') }}</p>

        <!-- Meal history modal -->
        <Dialog :open="historyOpen" @update:open="historyOpen = $event">
            <DialogScrollContent class="max-w-md rounded-xl">
                <DialogHeader>
                    <DialogTitle>{{ $t('dashboard.meal_history') }}</DialogTitle>
                    <DialogDescription>{{ $t('dashboard.history_modal_desc') }}</DialogDescription>
                </DialogHeader>

                <div class="divide-y">
                    <div v-for="day in recentMealsByDay" :key="day.date">
                        <!-- Day row -->
                        <button
                            type="button"
                            class="flex w-full items-center justify-between py-3 text-left"
                            @click="toggleDay(day.date)"
                        >
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-sm">{{ formatDate(day.date) }}</span>
                                <span v-if="isToday(day.date)" class="rounded-full bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary">{{ $t('dashboard.editable') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <span>{{ day.total_calories }} kcal</span>
                                <ChevronDown v-if="expandedDay === day.date" class="h-4 w-4" />
                                <ChevronRight v-else class="h-4 w-4" />
                            </div>
                        </button>

                        <!-- Meals list for expanded day -->
                        <div v-if="expandedDay === day.date" class="pb-3 space-y-1.5">
                            <div v-for="meal in day.meals" :key="meal.id" class="overflow-hidden rounded-md border border-border/50">
                                <!-- Meal header row (always visible) -->
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between bg-muted/30 px-3 py-2.5 text-left transition hover:bg-muted/50"
                                    @click="toggleMeal(meal.id)"
                                >
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <ChevronDown v-if="expandedMealId === meal.id" class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                        <ChevronRight v-else class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                        <span class="text-sm font-medium truncate">{{ meal.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 ml-2">
                                        <span class="text-sm font-semibold">{{ meal.items.reduce((s, i) => s + i.calories, 0) }} kcal</span>
                                        <button
                                            v-if="isToday(day.date)"
                                            type="button"
                                            class="rounded p-1 text-muted-foreground transition hover:bg-destructive/10 hover:text-destructive disabled:opacity-40"
                                            :disabled="deletingMealId === meal.id"
                                            :title="$t('dashboard.remove_meal', { name: meal.name })"
                                            @click.stop="deleteMeal(meal.id)"
                                        >
                                            <Loader2 v-if="deletingMealId === meal.id" class="h-3.5 w-3.5 animate-spin" />
                                            <Trash2 v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </button>

                                <!-- Ingredients (collapsed by default) -->
                                <ul v-if="expandedMealId === meal.id" class="divide-y divide-border/30 bg-background px-3">
                                    <li
                                        v-for="item in meal.items"
                                        :key="item.id"
                                        class="flex justify-between py-1.5 text-xs text-muted-foreground"
                                    >
                                        <span>{{ item.name }} ({{ item.quantity }}{{ item.unit }})</span>
                                        <span class="shrink-0 ml-3">{{ item.calories }} kcal</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </DialogScrollContent>
        </Dialog>

        <!-- Meal review modal -->
        <Dialog :open="reviewOpen" @update:open="reviewOpen = $event">
            <DialogScrollContent class="max-w-sm">
                <DialogHeader>
                    <DialogTitle>{{ $t('dashboard.confirm_meal') }}</DialogTitle>
                    <DialogDescription>{{ $t('dashboard.tap_to_edit') }}</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitMeal" class="space-y-3">
                    <!-- Meal name -->
                    <Input v-model="mealForm.name" :placeholder="$t('dashboard.meal_name_placeholder')" class="h-9 text-sm" />
                    <p v-if="mealForm.errors.name" class="text-xs text-destructive -mt-2">{{ mealForm.errors.name }}</p>

                    <!-- Total summary -->
                    <div class="flex justify-between rounded-lg bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                        <span class="font-medium text-foreground">{{ mealForm.items.reduce((s, i) => s + i.calories, 0) }} kcal</span>
                        <span>P {{ mealForm.items.reduce((s, i) => s + (i.protein_g || 0), 0).toFixed(0) }}g</span>
                        <span>C {{ mealForm.items.reduce((s, i) => s + (i.carbs_g || 0), 0).toFixed(0) }}g</span>
                        <span>F {{ mealForm.items.reduce((s, i) => s + (i.fat_g || 0), 0).toFixed(0) }}g</span>
                    </div>

                    <p v-if="mealForm.errors.items" class="text-xs text-destructive">{{ mealForm.errors.items }}</p>

                    <!-- Items list -->
                    <div class="space-y-1.5">
                        <div v-for="(item, i) in mealForm.items" :key="i" class="rounded-lg border overflow-hidden">
                            <!-- Collapsed row — always visible -->
                            <button
                                type="button"
                                class="flex w-full items-center justify-between px-3 py-2.5 text-left hover:bg-muted/30 transition"
                                @click="toggleItem(i)"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <ChevronDown v-if="expandedItems.has(i)" class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                    <ChevronRight v-else class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                    <span class="text-sm font-medium truncate">{{ item.name || $t('meal_edit.item_n', { n: i + 1 }) }}</span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 ml-2">
                                    <span class="text-xs text-muted-foreground">{{ item.quantity }}{{ item.unit }}</span>
                                    <span class="text-xs font-semibold">{{ item.calories }} kcal</span>
                                </div>
                            </button>

                            <!-- Expanded detail fields -->
                            <div v-if="expandedItems.has(i)" class="border-t px-3 py-3 space-y-2 bg-muted/20">
                                <!-- Name + qty/unit row -->
                                <div class="flex gap-2">
                                    <Input v-model="item.name" :placeholder="$t('meal_edit.name')" class="h-7 text-xs flex-1 min-w-0" />
                                    <Input v-model.number="item.quantity" type="number" step="0.1" min="0" class="h-7 text-xs w-16 shrink-0" />
                                    <Input v-model="item.unit" placeholder="g" class="h-7 text-xs w-12 shrink-0" />
                                </div>
                                <!-- Macros row -->
                                <div class="grid grid-cols-4 gap-1.5">
                                    <div>
                                        <p class="text-[10px] text-muted-foreground mb-0.5">kcal</p>
                                        <Input v-model.number="item.calories" type="number" min="0" class="h-7 text-xs px-2" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-muted-foreground mb-0.5">{{ $t('dashboard.protein') }}</p>
                                        <Input v-model.number="item.protein_g" type="number" step="0.1" min="0" class="h-7 text-xs px-2" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-muted-foreground mb-0.5">{{ $t('dashboard.carbs') }}</p>
                                        <Input v-model.number="item.carbs_g" type="number" step="0.1" min="0" class="h-7 text-xs px-2" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-muted-foreground mb-0.5">{{ $t('dashboard.fat') }}</p>
                                        <Input v-model.number="item.fat_g" type="number" step="0.1" min="0" class="h-7 text-xs px-2" />
                                    </div>
                                </div>
                                <button type="button" class="text-[10px] text-destructive hover:underline" @click="removeMealItemAndCollapse(i)">
                                    {{ $t('dashboard.remove_item') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="w-full text-xs text-muted-foreground hover:text-foreground border border-dashed border-border rounded-lg py-2 transition" @click="addMealItem">
                        {{ $t('dashboard.add_item') }}
                    </button>

                    <div class="flex gap-2 pt-1">
                        <Button type="button" variant="outline" class="flex-1 h-9" @click="reviewOpen = false">{{ $t('dashboard.cancel') }}</Button>
                        <Button type="submit" class="flex-1 h-9" :disabled="mealForm.processing">
                            {{ mealForm.processing ? $t('dashboard.saving') : $t('dashboard.save_meal') }}
                        </Button>
                    </div>
                </form>
            </DialogScrollContent>
        </Dialog>
    </div>
</template>
