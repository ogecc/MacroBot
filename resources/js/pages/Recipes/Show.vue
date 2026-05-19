<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { index as recipesIndex, destroy as recipeDestroy, eat as recipeEat } from '@/actions/App/Http/Controllers/RecipeController';
import { Button } from '@/components/ui/button';
import { ChefHat, Clock, ChevronLeft, Trash2, Loader2, Flame, Utensils } from 'lucide-vue-next';

interface Ingredient {
    amount: string;
    name: string;
}

interface Recipe {
    id: number;
    title: string;
    description: string | null;
    meal_type: string | null;
    servings: number;
    prep_time_min: number;
    cook_time_min: number;
    cuisine: string | null;
    difficulty: string;
    calories_per_serving: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
    why_recommended: string | null;
    ingredients: Ingredient[];
    steps: string[];
}

const props = defineProps<{
    recipe: Recipe;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Recipes', href: recipesIndex() },
            { title: 'Recipe', href: '#' },
        ],
    },
});

const deleting = ref(false);
const eating = ref(false);

function getCsrf(): string {
    return document.cookie
        .split('; ')
        .find((r) => r.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ?.replace(/%3D/g, '=') ?? '';
}

async function eatRecipe() {
    eating.value = true;
    try {
        await fetch(recipeEat.url({ recipe: props.recipe.id }), {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });
        router.visit('/dashboard');
    } finally {
        eating.value = false;
    }
}

async function deleteRecipe() {
    if (!confirm('Delete this recipe?')) return;
    deleting.value = true;
    try {
        await fetch(recipeDestroy.url({ recipe: props.recipe.id }), {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': getCsrf(),
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ _method: 'DELETE' }),
        });
        router.visit(recipesIndex());
    } finally {
        deleting.value = false;
    }
}

const difficultyColor: Record<string, string> = {
    easy: 'text-emerald-500 bg-emerald-500/10',
    medium: 'text-amber-500 bg-amber-500/10',
    hard: 'text-rose-500 bg-rose-500/10',
};
</script>

<template>
    <Head :title="recipe.title" />

    <div class="mx-auto w-full max-w-2xl space-y-6 px-4 py-6">

        <!-- Back link -->
        <Link :href="recipesIndex()" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
            <ChevronLeft class="h-4 w-4" />
            Recipes
        </Link>

        <!-- Header card -->
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-5 shadow-sm">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span
                    class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                    :class="difficultyColor[recipe.difficulty] ?? 'text-muted-foreground bg-muted'"
                >{{ recipe.difficulty }}</span>
                <span v-if="recipe.cuisine" class="text-xs text-muted-foreground">{{ recipe.cuisine }}</span>
                <span v-if="recipe.meal_type" class="text-xs capitalize text-muted-foreground">· {{ recipe.meal_type }}</span>
                <span class="text-xs text-muted-foreground flex items-center gap-1">
                    · <Clock class="h-3 w-3" /> {{ recipe.prep_time_min + recipe.cook_time_min }} min total
                </span>
            </div>

            <h1 class="text-xl font-bold leading-tight">{{ recipe.title }}</h1>
            <p v-if="recipe.description" class="mt-1 text-sm text-muted-foreground">{{ recipe.description }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                <span>Prep: {{ recipe.prep_time_min }} min</span>
                <span>Cook: {{ recipe.cook_time_min }} min</span>
                <span>Serves: {{ recipe.servings }}</span>
            </div>
        </div>

        <!-- Macros -->
        <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <div class="grid grid-cols-2 divide-y divide-border sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
                <div class="flex items-center justify-between px-4 py-3 sm:block sm:px-3 sm:py-3 sm:text-center">
                    <div>
                        <p class="text-xs text-muted-foreground">Calories</p>
                        <p class="text-xs text-muted-foreground">per serving</p>
                    </div>
                    <p class="font-bold text-lg">{{ recipe.calories_per_serving }}</p>
                </div>
                <div class="flex items-center justify-between px-4 py-3 sm:block sm:px-3 sm:py-3 sm:text-center border-l border-border sm:border-0">
                    <p class="text-xs text-muted-foreground">Protein</p>
                    <p class="font-bold text-lg text-blue-500">{{ recipe.protein_g }}g</p>
                </div>
                <div class="flex items-center justify-between px-4 py-3 sm:block sm:px-3 sm:py-3 sm:text-center">
                    <p class="text-xs text-muted-foreground">Carbs</p>
                    <p class="font-bold text-lg text-amber-500">{{ recipe.carbs_g }}g</p>
                </div>
                <div class="flex items-center justify-between px-4 py-3 sm:block sm:px-3 sm:py-3 sm:text-center border-l border-border sm:border-0">
                    <p class="text-xs text-muted-foreground">Fat</p>
                    <p class="font-bold text-lg text-rose-400">{{ recipe.fat_g }}g</p>
                </div>
            </div>
        </div>

        <!-- Why recommended -->
        <div v-if="recipe.why_recommended" class="flex gap-2 rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-3">
            <Flame class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            <p class="text-sm text-foreground">{{ recipe.why_recommended }}</p>
        </div>

        <!-- Ingredients -->
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h2 class="mb-3 font-semibold flex items-center gap-2">
                <ChefHat class="h-4 w-4 text-amber-500" />
                Ingredients
            </h2>
            <ul class="space-y-2">
                <li
                    v-for="(ingredient, i) in recipe.ingredients"
                    :key="i"
                    class="flex items-baseline gap-2 text-sm"
                >
                    <span class="shrink-0 font-medium text-amber-500 min-w-[4rem]">{{ ingredient.amount }}</span>
                    <span>{{ ingredient.name }}</span>
                </li>
            </ul>
        </div>

        <!-- Steps -->
        <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
            <h2 class="mb-3 font-semibold">Instructions</h2>
            <ol class="space-y-3">
                <li
                    v-for="(step, i) in recipe.steps"
                    :key="i"
                    class="flex gap-3"
                >
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-600">
                        {{ i + 1 }}
                    </span>
                    <p class="text-sm leading-relaxed">{{ step }}</p>
                </li>
            </ol>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pb-4">
            <Button
                size="sm"
                class="bg-amber-500 hover:bg-amber-600 text-white"
                :disabled="eating"
                @click="eatRecipe"
            >
                <Loader2 v-if="eating" class="mr-1.5 h-3 w-3 animate-spin" />
                <Utensils v-else class="mr-1.5 h-3 w-3" />
                I ate this
            </Button>

            <Button
                variant="destructive"
                size="sm"
                :disabled="deleting"
                @click="deleteRecipe"
            >
                <Loader2 v-if="deleting" class="mr-1.5 h-3 w-3 animate-spin" />
                <Trash2 v-else class="mr-1.5 h-3 w-3" />
                Delete Recipe
            </Button>
        </div>
    </div>
</template>
