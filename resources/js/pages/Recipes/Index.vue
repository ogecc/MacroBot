<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { index as recipesIndex, store as recipeStore, show as recipeShow, destroy as recipeDestroy } from '@/actions/App/Http/Controllers/RecipeController';
import { store as generateRoute } from '@/actions/App/Http/Controllers/RecipeGenerationController';
import { Button } from '@/components/ui/button';
import { ChefHat, Loader2, ArrowUp, Trash2, Clock, ChevronRight, UtensilsCrossed, Flame, RefreshCw } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Recipes', href: recipesIndex() }],
    },
});

interface Ingredient {
    amount: string;
    name: string;
}

interface GeneratedRecipe {
    title: string;
    description: string;
    meal_type: string;
    servings: number;
    prep_time_min: number;
    cook_time_min: number;
    cuisine: string;
    difficulty: string;
    calories_per_serving: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
    why_recommended: string;
    ingredients: Ingredient[];
    steps: string[];
}

interface SavedRecipe {
    id: number;
    title: string;
    meal_type: string | null;
    cuisine: string | null;
    difficulty: string;
    calories_per_serving: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
    cook_time_min: number;
    prep_time_min: number;
    created_at: string;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    recipes: Paginator<SavedRecipe>;
}>();

const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'] as const;
type MealType = typeof mealTypes[number];

const prompt = ref('');
const selectedMealType = ref<MealType | null>(null);
const generating = ref(false);
const generateError = ref('');
const coachNote = ref('');
const generatedRecipes = ref<GeneratedRecipe[]>([]);
const savingIndex = ref<number | null>(null);
const deletingId = ref<number | null>(null);

function toggleMealType(type: MealType) {
    selectedMealType.value = selectedMealType.value === type ? null : type;
}

function getCsrf(): string {
    return document.cookie
        .split('; ')
        .find((r) => r.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ?.replace(/%3D/g, '=') ?? '';
}

async function generate() {
    if (!prompt.value.trim()) return;
    generateError.value = '';
    generating.value = true;
    coachNote.value = '';
    generatedRecipes.value = [];

    try {
        const res = await fetch(generateRoute.url(), {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                prompt: prompt.value,
                meal_type: selectedMealType.value,
            }),
        });

        const json = await res.json();

        if (!res.ok) {
            generateError.value = json.message ?? 'Failed to generate recipes. Please try again.';
            return;
        }

        coachNote.value = json.ai_coach_note;
        generatedRecipes.value = json.recipes;
    } catch {
        generateError.value = 'Network error. Please try again.';
    } finally {
        generating.value = false;
    }
}

async function saveRecipe(recipe: GeneratedRecipe, index: number) {
    savingIndex.value = index;
    try {
        const res = await fetch(recipeStore.url(), {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ ...recipe, prompt: prompt.value }),
        });

        if (res.ok || res.status === 302) {
            router.reload({ only: ['recipes'] });
            generatedRecipes.value.splice(index, 1);
        }
    } finally {
        savingIndex.value = null;
    }
}

async function deleteRecipe(id: number) {
    deletingId.value = id;
    try {
        await fetch(recipeDestroy.url({ recipe: id }), {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE' }),
        });
        router.reload({ only: ['recipes'] });
    } finally {
        deletingId.value = null;
    }
}

const difficultyColor: Record<string, string> = {
    easy: 'text-emerald-500 bg-emerald-500/10',
    medium: 'text-amber-500 bg-amber-500/10',
    hard: 'text-rose-500 bg-rose-500/10',
};
</script>

<template>
    <Head title="Recipes" />

    <div class="mx-auto w-full max-w-2xl space-y-6 px-4 py-6">

        <!-- Chef card -->
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <ChefHat class="h-5 w-5 text-amber-500" />
                <span class="font-semibold">AI Chef</span>
            </div>

            <!-- Meal type pills -->
            <div class="mb-3 flex flex-wrap gap-2">
                <button
                    v-for="type in mealTypes"
                    :key="type"
                    type="button"
                    class="rounded-full px-3 py-1 text-xs font-medium capitalize transition-colors"
                    :class="selectedMealType === type
                        ? 'bg-amber-500 text-white'
                        : 'bg-background/60 text-muted-foreground hover:text-foreground border border-border'"
                    @click="toggleMealType(type)"
                >
                    {{ type }}
                </button>
            </div>

            <!-- Prompt input -->
            <div class="rounded-xl border border-border/60 bg-background/80 shadow-inner backdrop-blur-sm transition focus-within:border-amber-500/50 focus-within:ring-2 focus-within:ring-amber-500/20">
                <textarea
                    v-model="prompt"
                    rows="2"
                    placeholder="Describe what you'd like to cook… e.g. &quot;I have chicken and spinach, make me something light&quot;"
                    class="w-full resize-none bg-transparent px-4 pt-3 pb-2 text-sm leading-relaxed placeholder:text-muted-foreground/60 focus:outline-none"
                    @keydown.enter.exact.prevent="generate"
                />
                <div class="flex items-center justify-end border-t border-border/40 px-3 py-2">
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white shadow transition hover:bg-amber-600 active:scale-95 disabled:opacity-50"
                        :disabled="generating || !prompt.trim()"
                        @click="generate"
                    >
                        <Loader2 v-if="generating" class="h-4 w-4 animate-spin" />
                        <ArrowUp v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <p v-if="generateError" class="mt-2 text-xs text-destructive">{{ generateError }}</p>
            <p v-if="generating" class="mt-2 text-xs text-muted-foreground animate-pulse">
                AI Chef is cooking up recipes for you…
            </p>
        </div>

        <!-- AI Coach note -->
        <div v-if="coachNote" class="rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-3">
            <div class="flex gap-2">
                <Flame class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                <p class="text-sm text-foreground">{{ coachNote }}</p>
            </div>
        </div>

        <!-- Generated recipe cards -->
        <div v-if="generatedRecipes.length" class="space-y-4">
            <h2 class="text-sm font-medium text-muted-foreground">{{ generatedRecipes.length }} recipes generated</h2>

            <div
                v-for="(recipe, i) in generatedRecipes"
                :key="i"
                class="rounded-2xl border border-border bg-card p-5 shadow-sm space-y-4 overflow-hidden min-w-0"
            >
                <!-- Header row -->
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="difficultyColor[recipe.difficulty] ?? 'text-muted-foreground bg-muted'"
                            >{{ recipe.difficulty }}</span>
                            <span v-if="recipe.cuisine" class="text-xs text-muted-foreground">{{ recipe.cuisine }}</span>
                            <span v-if="recipe.meal_type" class="text-xs capitalize text-muted-foreground">· {{ recipe.meal_type }}</span>
                        </div>
                        <h3 class="font-semibold leading-tight break-words">{{ recipe.title }}</h3>
                        <p class="mt-0.5 text-xs text-muted-foreground break-words">{{ recipe.description }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1 text-xs text-muted-foreground whitespace-nowrap">
                        <Clock class="h-3 w-3" />
                        {{ recipe.prep_time_min + recipe.cook_time_min }} min
                    </div>
                </div>

                <!-- Macros -->
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-0 sm:divide-x sm:divide-border sm:rounded-xl sm:border sm:border-border sm:bg-background/50">
                    <div class="flex items-center justify-between rounded-xl border border-border bg-background/50 px-3 py-2 sm:block sm:rounded-none sm:border-0 sm:bg-transparent sm:px-1 sm:py-2 sm:text-center">
                        <p class="text-xs text-muted-foreground">Calories</p>
                        <p class="font-semibold text-sm">{{ recipe.calories_per_serving }}</p>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-border bg-background/50 px-3 py-2 sm:block sm:rounded-none sm:border-0 sm:bg-transparent sm:px-1 sm:py-2 sm:text-center">
                        <p class="text-xs text-muted-foreground">Protein</p>
                        <p class="font-semibold text-sm text-blue-500">{{ recipe.protein_g }}g</p>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-border bg-background/50 px-3 py-2 sm:block sm:rounded-none sm:border-0 sm:bg-transparent sm:px-1 sm:py-2 sm:text-center">
                        <p class="text-xs text-muted-foreground">Carbs</p>
                        <p class="font-semibold text-sm text-amber-500">{{ recipe.carbs_g }}g</p>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-border bg-background/50 px-3 py-2 sm:block sm:rounded-none sm:border-0 sm:bg-transparent sm:px-1 sm:py-2 sm:text-center">
                        <p class="text-xs text-muted-foreground">Fat</p>
                        <p class="font-semibold text-sm text-rose-400">{{ recipe.fat_g }}g</p>
                    </div>
                </div>

                <!-- Why recommended -->
                <p v-if="recipe.why_recommended" class="text-xs text-muted-foreground italic border-l-2 border-amber-500/40 pl-3">
                    {{ recipe.why_recommended }}
                </p>

                <!-- Save button -->
                <div class="flex justify-end">
                    <Button
                        size="sm"
                        class="bg-amber-500 hover:bg-amber-600 text-white"
                        :disabled="savingIndex === i"
                        @click="saveRecipe(recipe, i)"
                    >
                        <Loader2 v-if="savingIndex === i" class="mr-1.5 h-3 w-3 animate-spin" />
                        Save Recipe
                    </Button>
                </div>
            </div>
        </div>

        <!-- Regenerate button -->
        <div v-if="generatedRecipes.length" class="flex justify-center">
            <Button
                variant="outline"
                :disabled="generating"
                @click="generate"
            >
                <Loader2 v-if="generating" class="mr-2 h-4 w-4 animate-spin" />
                <RefreshCw v-else class="mr-2 h-4 w-4" />
                Regenerate recipes
            </Button>
        </div>

        <!-- Saved recipes list -->
        <div>
            <h2 class="mb-3 text-sm font-medium text-muted-foreground">Saved Recipes</h2>

            <div v-if="recipes.data.length === 0" class="rounded-2xl border border-dashed border-border p-8 text-center">
                <ChefHat class="mx-auto mb-2 h-8 w-8 text-muted-foreground/40" />
                <p class="text-sm text-muted-foreground">No saved recipes yet.</p>
                <p class="text-xs text-muted-foreground/60">Generate one above and save it!</p>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="recipe in recipes.data"
                    :key="recipe.id"
                    class="flex items-center gap-3 rounded-xl border border-border bg-card p-3 hover:bg-accent/30 transition-colors"
                >
                    <Link :href="recipeShow(recipe.id)" class="flex min-w-0 flex-1 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10">
                            <UtensilsCrossed class="h-4 w-4 text-amber-500" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-sm">{{ recipe.title }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span
                                    class="text-xs capitalize font-medium"
                                    :class="difficultyColor[recipe.difficulty] ?? 'text-muted-foreground'"
                                >{{ recipe.difficulty }}</span>
                                <span class="text-xs text-muted-foreground">{{ recipe.calories_per_serving }} kcal</span>
                                <span class="text-xs text-muted-foreground flex items-center gap-0.5">
                                    <Clock class="h-3 w-3" />{{ recipe.prep_time_min + recipe.cook_time_min }} min
                                </span>
                            </div>
                        </div>
                    </Link>

                    <div class="flex shrink-0 items-center gap-1">
                        <Link :href="recipeShow(recipe.id)" class="flex items-center text-muted-foreground hover:text-foreground">
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                        <button
                            type="button"
                            class="p-1.5 text-muted-foreground hover:text-destructive transition-colors"
                            :disabled="deletingId === recipe.id"
                            @click="deleteRecipe(recipe.id)"
                        >
                            <Loader2 v-if="deletingId === recipe.id" class="h-4 w-4 animate-spin" />
                            <Trash2 v-else class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="recipes.last_page > 1" class="mt-4 flex justify-center gap-2">
                <Link
                    v-if="recipes.prev_page_url"
                    :href="recipes.prev_page_url"
                    class="rounded-lg border border-border px-3 py-1.5 text-sm hover:bg-accent/30"
                >
                    Previous
                </Link>
                <span class="rounded-lg border border-border px-3 py-1.5 text-sm text-muted-foreground">
                    {{ recipes.current_page }} / {{ recipes.last_page }}
                </span>
                <Link
                    v-if="recipes.next_page_url"
                    :href="recipes.next_page_url"
                    class="rounded-lg border border-border px-3 py-1.5 text-sm hover:bg-accent/30"
                >
                    Next
                </Link>
            </div>
        </div>
    </div>
</template>
